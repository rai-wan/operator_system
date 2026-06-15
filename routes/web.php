<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\DowntimeController;
use App\Http\Controllers\VisionController;

// ============================
// LOGIN & SELECTION
// ============================
Route::get('/', [OperatorController::class, 'loginPage']);
Route::get('/login', [OperatorController::class, 'loginPage'])->name('login');
Route::post('/login', [OperatorController::class, 'loginProses']);
Route::get('/logout', [OperatorController::class, 'logout']);
Route::get('/select-station', [OperatorController::class, 'selectStation']);

// ============================
// STATIONS
// ============================
Route::get('/insert', [OperatorController::class, 'insertPage']);
Route::post('/insert/proses', [OperatorController::class, 'insertProses']);

Route::get('/timbang', [OperatorController::class, 'timbangPage']);
Route::post('/timbang/proses', [OperatorController::class, 'timbangProses']);

Route::get('/packing', [OperatorController::class, 'packingPage']);
Route::post('/packing/proses', [OperatorController::class, 'packingProses']);

// ============================
// DOWNTIME
// ============================
Route::get('/downtime', [DowntimeController::class, 'index']);
Route::get('/downtime/data', [DowntimeController::class, 'getData']);
Route::get('/downtime/chart', [DowntimeController::class, 'chart']);
Route::post('/downtime/store', [DowntimeController::class, 'store']);

// ============================
// VISION (Raspberry Pi Camera)
// ============================
Route::prefix('vision')->group(function () {
    Route::get('/status',     [VisionController::class, 'status']);
    Route::get('/health',     [VisionController::class, 'health']);
    Route::get('/stream-url', [VisionController::class, 'streamUrl']);
    Route::post('/config',    [VisionController::class, 'sendConfig']);
    Route::post('/reset',     [VisionController::class, 'reset']);
});