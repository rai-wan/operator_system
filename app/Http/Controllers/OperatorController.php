<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OperatorController extends Controller
{
    // ============================
    // LOGIN & SELECTION
    // ============================
    public function loginPage() {
        return view('login');
    }

    public function loginProses(Request $request) {
        $request->validate([
            'id_karyawan' => 'required',
            'station' => 'required'
        ]);
        
        $api = config('app.api_url');
        
        try {
            $response = \Illuminate\Support\Facades\Http::post($api.'/api/operator/cek-id', [
                'id_karyawan' => $request->id_karyawan
            ]);

            if($response->successful()){
                session([
                    'id_karyawan' => $request->id_karyawan,
                    'station' => $request->station
                ]);
                return redirect('/' . $request->station);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['Tidak dapat terhubung ke server pusat.']);
        }
        
        return back()->withErrors(['ID Karyawan tidak terdaftar atau tidak valid.']);
    }

    public function logout() {
        session()->forget('id_karyawan');
        return redirect('/');
    }

    public function selectStation() {
        if (!session('id_karyawan')) {
            return redirect('/');
        }
        return view('selection');
    }

    // ============================
    // STATION INSERT
    // ============================
    public function insertPage()
    {
        if (!session('id_karyawan')) return redirect('/');
        return view('insert');
    }

    public function insertProses(Request $request)
    {
        $api = config('app.api_url');

        $response = \Illuminate\Support\Facades\Http::post($api.'/api/operator/insert', [
            'kode_produk' => $request->produk,
            'kode_part'   => $request->part,
            'status'      => $request->status,
            'defect_type' => $request->defect_type,
            'confidence'  => $request->confidence,
            'foto'        => $request->foto,
        ]);

        return response()->json([
            'success' => $response->successful(),
            'message' => $response->json('message') ?? 'Terjadi kesalahan'
        ]);
    }

    // ============================
    // STATION TIMBANG
    // ============================
    public function timbangPage()
    {
        if (!session('id_karyawan')) return redirect('/');
        return view('timbang');
    }

    public function timbangProses(Request $request)
    {
        $api = config('app.api_url');
        $response = \Illuminate\Support\Facades\Http::post($api.'/api/operator/timbang', [
            'kode_produk' => $request->produk,
            'berat' => $request->berat,
            'keterangan' => $request->keterangan
        ]);
        return response()->json([
            'success' => $response->successful(),
            'message' => $response->json('message') ?? 'Terjadi kesalahan'
        ]);
    }

    // ============================
    // STATION PACKING
    // ============================
    public function packingPage()
    {
        if (!session('id_karyawan')) return redirect('/');
        return view('packing');
    }

    public function packingProses(Request $request)
    {
        $api = config('app.api_url');
        $response = \Illuminate\Support\Facades\Http::post($api.'/api/operator/packing', [
            'kode_produk' => $request->produk
        ]);
        return response()->json([
            'success' => $response->successful(),
            'message' => $response->json('message') ?? 'Terjadi kesalahan'
        ]);
    }
}