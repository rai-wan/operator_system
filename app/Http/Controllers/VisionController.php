<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * VisionController
 * Proxy komunikasi antara browser Laravel ↔ Raspberry Pi Vision Server
 */
class VisionController extends Controller
{
    /**
     * Dapatkan IP dan base URL Raspberry Pi dari config/env
     */
    private function piUrl(string $path = ''): string
    {
        $ip   = env('RASPBERRY_PI_IP', '192.168.1.100');
        $port = env('RASPBERRY_PI_PORT', '5000');
        return "http://{$ip}:{$port}{$path}";
    }

    /**
     * GET /vision/status
     * Dipanggil browser (insert.blade.php) setiap ~1.5 detik via polling.
     * Mem-proxy request ke Raspberry Pi dan mengembalikan status deteksi.
     */
    public function status(Request $request)
    {
        try {
            $response = Http::timeout(3)->get($this->piUrl('/status'));

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'ok'           => false,
                'camera_ok'    => false,
                'is_complete'  => false,
                'scan_allowed' => false,
                'item_count'   => 0,
                'required_count' => env('VISION_REQUIRED_COUNT', 2),
                'error'        => 'Pi server merespons dengan error: ' . $response->status(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok'           => false,
                'camera_ok'    => false,
                'is_complete'  => false,
                'scan_allowed' => false,
                'item_count'   => 0,
                'required_count' => env('VISION_REQUIRED_COUNT', 2),
                'error'        => 'Tidak dapat terhubung ke Raspberry Pi: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /vision/config
     * Laravel mengirim konfigurasi produk ke Raspberry Pi saat operator scan produk pertama kali.
     */
    public function sendConfig(Request $request)
    {
        $data = $request->validate([
            'product_code'   => 'nullable|string',
            'product_name'   => 'nullable|string',
            'required_count' => 'nullable|integer|min:1',
            'items'          => 'nullable|array',
        ]);

        // Tambahkan default required_count dari env jika tidak dikirim
        if (!isset($data['required_count'])) {
            $data['required_count'] = (int) env('VISION_REQUIRED_COUNT', 2);
        }

        try {
            $response = Http::timeout(3)->post($this->piUrl('/config'), $data);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'Gagal mengirim config ke Pi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /vision/reset
     * Reset status deteksi di Pi setelah barcode scan berhasil.
     */
    public function reset(Request $request)
    {
        try {
            $response = Http::timeout(3)->post($this->piUrl('/reset'));
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'Gagal reset Pi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /vision/stream-url
     * Kembalikan URL stream video Pi ke browser (untuk <img> tag MJPEG).
     */
    public function streamUrl()
    {
        $ip   = env('RASPBERRY_PI_IP', '192.168.1.100');
        $port = env('RASPBERRY_PI_PORT', '5000');
        return response()->json([
            'url' => "http://{$ip}:{$port}/stream",
        ]);
    }

    /**
     * GET /vision/health
     * Cek apakah Pi Vision Server aktif.
     */
    public function health()
    {
        try {
            $response = Http::timeout(2)->get($this->piUrl('/health'));
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'ok'     => false,
                'camera' => false,
                'error'  => 'Pi tidak terjangkau.',
            ]);
        }
    }
}
