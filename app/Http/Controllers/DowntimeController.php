<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DowntimeController extends Controller
{
    const TARGET_PER_JAM = 100;
    const DB_CONN = 'web_based';

    /**
     * Halaman utama downtime
     */
    public function index()
    {
        return view('downtime');
    }

    /**
     * Data list downtime dari DB admin langsung
     */
    public function getData(Request $request)
    {
        try {
            $station = $request->station ?? 'insert';

            $data = DB::connection(self::DB_CONN)
                ->table('downtime')
                ->where('station', $station)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function ($item) {
                    if (!empty($item->created_at)) {
                        try {
                            $item->time = Carbon::parse($item->created_at)
                                ->setTimezone('Asia/Jakarta')
                                ->format('H:i:s');
                        } catch (\Throwable $e) {
                            $item->time = isset($item->jam) ? str_pad($item->jam, 2, '0', STR_PAD_LEFT) . ':00:00' : '—';
                        }
                    } else {
                        $item->time = isset($item->jam) ? str_pad($item->jam, 2, '0', STR_PAD_LEFT) . ':00:00' : '—';
                    }
                    return $item;
                });

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Gagal membaca data downtime: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Data chart produksi per jam — query LANGSUNG ke MySQL web_based
     * Dioptimasi: 1 query GROUP BY, bukan N query individual.
     */
    public function chart(Request $request)
    {
        $station  = $request->station ?? 'insert';
        $shift    = $request->shift   ?? 'pagi';
        $now      = Carbon::now('Asia/Jakarta');
        $tanggal  = $now->toDateString();
        $jamSkrg  = (int) $now->format('H');

        try {
            // ============================================================
            // BANGUN DAFTAR JAM SESUAI SHIFT
            // ============================================================
            $jamList = [];

            if ($shift === 'pagi') {
                for ($i = 6; $i <= 14; $i++) {
                    $jamList[] = ['hour' => $i, 'tanggal' => $tanggal];
                }
            } elseif ($shift === 'siang') {
                for ($i = 15; $i <= 22; $i++) {
                    $jamList[] = ['hour' => $i, 'tanggal' => $tanggal];
                }
            } else {
                $tanggalBesok = $now->copy()->addDay()->toDateString();
                $jamList[] = ['hour' => 23, 'tanggal' => $tanggal];
                for ($i = 0; $i <= 5; $i++) {
                    $jamList[] = ['hour' => $i, 'tanggal' => $tanggalBesok];
                }
            }

            // ============================================================
            // OPTIMASI: 1 QUERY GROUP BY untuk semua jam (bukan N queries)
            // ============================================================
            $tanggalList = array_unique(array_column($jamList, 'tanggal'));

            $rawCounts = DB::connection(self::DB_CONN)
                ->table('trackings')
                ->where('station', $station)
                ->whereIn(DB::raw('DATE(DATE_ADD(created_at, INTERVAL 7 HOUR))'), $tanggalList)
                ->selectRaw('DATE(DATE_ADD(created_at, INTERVAL 7 HOUR)) as tgl, HOUR(DATE_ADD(created_at, INTERVAL 7 HOUR)) as jam, COUNT(*) as total')
                ->groupByRaw('tgl, jam')
                ->get()
                ->keyBy(fn($r) => $r->tgl . '_' . $r->jam);

            // Bangun labels dan data dari hasil query
            $labels = [];
            $data   = [];
            foreach ($jamList as $jam) {
                $key = $jam['tanggal'] . '_' . $jam['hour'];
                $labels[] = str_pad($jam['hour'], 2, '0', STR_PAD_LEFT);
                $data[]   = isset($rawCounts[$key]) ? (int) $rawCounts[$key]->total : 0;
            }

            // ============================================================
            // HITUNG JAM YANG SUDAH BERLALU
            // ============================================================
            $elapsedCount = 0;
            foreach ($jamList as $idx => $jam) {
                if ($jam['tanggal'] < $tanggal) {
                    $elapsedCount = $idx + 1;
                } elseif ($jam['tanggal'] === $tanggal && $jam['hour'] <= $jamSkrg) {
                    $elapsedCount = $idx + 1;
                }
            }
            if ($elapsedCount === 0) $elapsedCount = 1;

            // ============================================================
            // TOTAL DOWNTIME hari ini (1 query)
            // ============================================================
            $downtime = DB::connection(self::DB_CONN)
                ->table('downtime')
                ->where('station', $station)
                ->whereDate('tanggal', $tanggal)
                ->sum('durasi');

            // ============================================================
            // KPI CALCULATION
            // ============================================================
            $actualOutput   = (int) array_sum(array_slice($data, 0, $elapsedCount));
            $expectedOutput = $elapsedCount * self::TARGET_PER_JAM;

            $outputPercent = $expectedOutput > 0
                ? min(100, (int) round(($actualOutput / $expectedOutput) * 100))
                : 0;

            $totalMenit = $elapsedCount * 60;
            $efficiency = $totalMenit > 0
                ? max(0, (int) round((($totalMenit - (int) $downtime) / $totalMenit) * 100))
                : 100;

            return response()->json([
                'labels'         => $labels,
                'data'           => $data,
                'downtime'       => (int) $downtime,
                'output_percent' => $outputPercent,
                'efficiency'     => $efficiency,
                'elapsed_hours'  => $elapsedCount,
                'actual_output'  => $actualOutput,
                'target_output'  => $expectedOutput,
                'current_hour'   => str_pad($jamSkrg, 2, '0', STR_PAD_LEFT),
            ])->withHeaders([
                'Cache-Control' => 'public, max-age=30', // Browser cache 30 detik
                'X-Optimized'   => 'group-by-query',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Gagal membaca data dari database: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan downtime LANGSUNG ke tabel downtimes di MySQL web_based
     * Tidak perlu admin server aktif.
     */
    public function store(Request $request)
    {
        try {
            $id = DB::connection(self::DB_CONN)
                ->table('downtime')
                ->insertGetId([
                    'station'    => $request->station,
                    'jam'        => $request->jam,
                    'tanggal'    => Carbon::now('Asia/Jakarta')->toDateString(),
                    'alasan'     => $request->alasan,
                    'durasi'     => (int) $request->durasi,
                    'komentar'   => $request->komentar ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Downtime berhasil disimpan',
                'id'      => $id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan downtime: ' . $e->getMessage(),
            ], 500);
        }
    }
}