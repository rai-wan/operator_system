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
        $ip   = config('app.raspberry_pi_ip');
        $port = config('app.raspberry_pi_port');
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
                'required_count' => config('app.vision_required_count'),
                'error'        => 'Pi server merespons dengan error: ' . $response->status(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok'           => false,
                'camera_ok'    => false,
                'is_complete'  => false,
                'scan_allowed' => false,
                'item_count'   => 0,
                'required_count' => config('app.vision_required_count'),
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
            $data['required_count'] = (int) config('app.vision_required_count');
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
        $ip   = config('app.raspberry_pi_ip');
        $port = config('app.raspberry_pi_port');
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

    /**
     * POST /vision/tare
     * Jalankan tare (zeroing) pada timbangan.
     */
    public function tare(Request $request)
    {
        try {
            $response = Http::timeout(3)->post($this->piUrl('/tare'));
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'Gagal tare timbangan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /vision/inspect
     * Minta Pi melakukan inspeksi visual terhadap produk tertentu.
     */
    public function inspect(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
        ]);

        try {
            $response = Http::timeout(15)->post($this->piUrl('/inspect'), [
                'product_code' => $request->product_code,
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'Gagal melakukan inspeksi di Raspberry Pi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /vision/reference
     * Minta Pi menyimpan gambar referensi untuk produk.
     */
    public function reference(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'image_base64' => 'required|string',
        ]);

        try {
            $response = Http::timeout(10)->post($this->piUrl('/reference'), [
                'product_code' => $request->product_code,
                'image_base64' => $request->image_base64,
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'Gagal mengirim gambar referensi ke Raspberry Pi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /vision/reference-image/{product_code}
     * Ambil foto referensi dari Pi dan kembalikan ke browser.
     */
    public function getReferenceImage($product_code)
    {
        $clean = preg_replace('/[^a-zA-Z0-9\-_]/', '', $product_code);
        try {
            $response = Http::timeout(3)->get($this->piUrl("/references/{$clean}.jpg"));
            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', 'image/jpeg');
            }
            // Fallback ke default.jpg di Pi
            $defaultResp = Http::timeout(3)->get($this->piUrl("/references/default.jpg"));
            if ($defaultResp->successful()) {
                return response($defaultResp->body(), 200)
                    ->header('Content-Type', 'image/jpeg');
            }
        } catch (\Exception $e) {}

        // Fallback lokal jika Pi offline
        $localDefault = public_path('images/default_reference.jpg');
        if (file_exists($localDefault)) {
            return response()->file($localDefault, ['Content-Type' => 'image/jpeg']);
        }

        return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='), 404)
            ->header('Content-Type', 'image/png');
    }
}
