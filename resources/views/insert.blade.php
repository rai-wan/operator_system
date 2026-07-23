<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station Insert — ProTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 28px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(14px);
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 12px; }
        .navbar h2 { font-size: 19px; font-weight: 700; color: #0f172a; }
        .badge-live {
            background: #dcfce7; color: #15803d;
            font-size: 11px; font-weight: 700;
            padding: 3px 9px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: 0.5px;
            display: flex; align-items: center; gap: 5px;
        }
        .pulse-dot {
            width: 7px; height: 7px;
            background: #22c55e; border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(0.7)} }

        .nav-right { display: flex; align-items: center; gap: 10px; }
        .operator-badge {
            background: #f8fafc; border: 1px solid #e2e8f0;
            padding: 6px 12px; border-radius: 8px;
            font-size: 13px; color: #475569; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }
        .operator-badge span {
            background: #0ea5e9; color: white;
            padding: 2px 6px; border-radius: 4px; font-size: 11px;
        }
        .nav-btn {
            text-decoration: none; padding: 8px 16px;
            border-radius: 8px; font-size: 13px; font-weight: 600;
            transition: all 0.2s;
        }
        .btn-back { background: #f1f5f9; color: #475569; }
        .btn-back:hover { background: #e2e8f0; }
        .btn-target { background: #0ea5e9; color: white; }
        .btn-target:hover { background: #0284c7; }

        /* ===== MAIN LAYOUT ===== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
            padding: 24px 28px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ===== VISION PANEL (kiri) ===== */
        .vision-panel {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
        }
        .vision-header {
            padding: 18px 22px 14px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .vision-title { font-size: 16px; font-weight: 700; color: #0f172a; }
        .vision-subtitle { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        /* Status badge kamera */
        .cam-status-badge {
            display: flex; align-items: center; gap: 7px;
            padding: 5px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            transition: all 0.3s;
        }
        .cam-badge-connecting { background: #fefce8; color: #854d0e; border: 1px solid #fde68a; }
        .cam-badge-ok         { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .cam-badge-error      { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Video stream container */
        .stream-wrap {
            position: relative;
            background: #0f172a;
            flex: 1;
            min-height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stream-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none;
        }
        .stream-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #475569;
            gap: 14px;
            padding: 40px;
            text-align: center;
        }
        .stream-placeholder .icon { font-size: 48px; }
        .stream-placeholder p { font-size: 14px; line-height: 1.6; }
        .stream-placeholder small { font-size: 12px; color: #64748b; }

        /* ===== DETECTION STATUS BAR ===== */
        .detection-bar {
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            transition: background 0.4s;
        }
        .detection-bar.complete   { background: #f0fdf4; }
        .detection-bar.incomplete { background: #fef9f0; }
        .detection-bar.error-state{ background: #fef2f2; }

        .det-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .det-icon.complete   { background: #dcfce7; color: #15803d; }
        .det-icon.incomplete { background: #fef3c7; color: #92400e; }
        .det-icon.error-state{ background: #fee2e2; color: #b91c1c; }
        .det-icon.waiting    { background: #eff6ff; color: #1d4ed8; }

        .det-info { flex: 1; }
        .det-main  { font-size: 14px; font-weight: 700; color: #0f172a; }
        .det-sub   { font-size: 12px; color: #64748b; margin-top: 2px; }

        /* ===== SCAN PANEL (kanan) ===== */
        .scan-panel {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* Vision requirement card */
        .requirement-card {
            background: white;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .req-title {
            font-size: 13px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        /* SCAN BOX */
        .scan-box {
            background: white;
            border-radius: 20px;
            padding: 26px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            flex: 1;
            transition: all 0.4s;
            border: 2px solid transparent;
        }
        .scan-box.locked {
            opacity: 0.65;
            pointer-events: none;
            border-color: #fee2e2;
            background: #fff9f9;
        }
        .scan-box.unlocked {
            border-color: #bbf7d0;
            box-shadow: 0 4px 24px rgba(34, 197, 94, 0.15);
        }

        .lock-overlay {
            text-align: center;
            padding: 16px;
            background: #fef2f2;
            border-radius: 12px;
            margin-bottom: 18px;
            display: none;
            border: 1px solid #fecaca;
        }
        .lock-overlay.visible { display: block; }
        .lock-overlay p { font-size: 13px; color: #b91c1c; font-weight: 600; }
        .lock-overlay small { font-size: 12px; color: #ef4444; }

        .unlock-notice {
            text-align: center;
            padding: 12px;
            background: #f0fdf4;
            border-radius: 12px;
            margin-bottom: 18px;
            display: none;
            border: 1px solid #bbf7d0;
        }
        .unlock-notice.visible { display: block; }
        .unlock-notice p { font-size: 13px; color: #15803d; font-weight: 700; }

        .scan-title { font-size: 19px; font-weight: 700; margin-bottom: 4px; }
        .scan-step  { color: #0ea5e9; font-size: 13px; margin-bottom: 18px; }

        .scan-input {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            font-family: inherit;
            transition: all 0.2s;
            background: white;
        }
        .scan-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
        .scan-input:disabled { background: #f8fafc; border-color: #e2e8f0; cursor: not-allowed; }

        .status-msg {
            margin-top: 14px;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 10px;
            display: none;
        }
        .status-msg.success { background: #dcfce7; color: #15803d; display: block; }
        .status-msg.error   { background: #fee2e2; color: #b91c1c; display: block; }
        .status-msg.info    { background: #eff6ff; color: #1d4ed8; display: block; }

        /* Connection indicator */
        .pi-connection {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 14px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 12px; color: #64748b;
            font-weight: 500;
        }
        .pi-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #cbd5e1; transition: background 0.3s;
        }
        .pi-dot.connected    { background: #22c55e; }
        .pi-dot.connecting   { background: #f59e0b; animation: pulse 1s infinite; }
        .pi-dot.disconnected { background: #ef4444; }

        /* Floating shortcut */
        .floating-btn {
            position: fixed; bottom: 24px; right: 24px;
            background: #0ea5e9; color: white;
            padding: 13px 20px; border-radius: 50px;
            box-shadow: 0 8px 24px rgba(14,165,233,0.35);
            text-decoration: none; font-size: 13px; font-weight: 600;
            transition: all 0.3s;
        }
        .floating-btn:hover { background: #0284c7; transform: translateY(-2px); }

        /* Camera Selector Dropdown */
        .camera-selector-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .camera-select {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            outline: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .camera-select:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        .camera-select:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 2px rgba(14,165,233,0.15);
        }

        /* Styling Baru untuk Inspeksi AI */
        .btn-inspect-ai {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            outline: none;
        }
        .btn-inspect-ai:hover:not(:disabled) {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(14, 165, 233, 0.3);
        }
        .btn-inspect-ai:disabled {
            background: #cbd5e1;
            color: #64748b;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .result-pass {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 12px;
            text-align: center;
            font-weight: 800;
            font-size: 16px;
        }
        .result-reject {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 12px;
            text-align: center;
            font-weight: 800;
            font-size: 16px;
        }
        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .result-item:last-child {
            border-bottom: none;
        }
        .result-label {
            color: #64748b;
            font-weight: 500;
        }
        .result-value {
            color: #0f172a;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            .navbar { padding: 14px 16px; }
            .main-grid { padding: 16px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">
        <h2>Station Insert</h2>
        <div class="badge-live">
            <div class="pulse-dot"></div> LIVE
        </div>
    </div>
    <div class="nav-right">
        <div class="pi-connection" id="piConnection">
            <div class="pi-dot connecting" id="piDot"></div>
            <span id="piLabel">Menghubungkan ke kamera...</span>
        </div>
        <div class="operator-badge">
            <span>ID</span> {{ session('id_karyawan') ?? 'Unknown' }}
        </div>
        <a href="/select-station" class="nav-btn btn-back">Kembali</a>
        <a href="/downtime" class="nav-btn btn-target">Downtime</a>
    </div>
</div>

<!-- MAIN -->
<div class="main-grid">

    <!-- KIRI: Vision Camera Panel -->
    <div class="vision-panel">
        <div class="vision-header">
            <div>
                <div class="vision-title">Kamera Inspeksi Kualitas (AI)</div>
                <div class="vision-subtitle" id="visionSubtitle">Raspberry Pi Camera — Real-time Quality Inspection</div>
            </div>
            <div class="camera-selector-container">
                <select id="cameraSource" class="camera-select">
                    <option value="pi">Raspberry Pi Camera</option>
                    <option value="local">Kamera Laptop/PC (Webcam Lokal)</option>
                </select>
                <div class="cam-status-badge cam-badge-connecting" id="camBadge">
                    <div style="width:7px;height:7px;border-radius:50%;background:currentColor;opacity:0.7;"></div>
                    <span id="camBadgeText">Connecting...</span>
                </div>
            </div>
        </div>

        <!-- Stream Video -->
        <div class="stream-wrap" id="streamWrap">
            <!-- Permanent Alignment Marking Guides (Garis Acuan Posisi Produk) -->
            <div class="marking-guide-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                <div class="tray-frame" style="position: relative; width: 420px; height: 280px; border: 3px dashed rgba(255, 255, 255, 0.4); border-radius: 12px; background: rgba(255, 255, 255, 0.02);">
                    <span style="position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 10px; font-weight: 800; background: rgba(255,255,255,0.15); color: #fff; padding: 2px 8px; border-radius: 4px; backdrop-filter: blur(4px); white-space: nowrap;">ACUAN POSISI DUS (TRAY)</span>
                    
                    <!-- Box 1: TWS Hitam (Sisi Kiri Dus) -->
                    <div class="box-tws" style="position: absolute; top: 30px; left: 25px; width: 150px; height: 220px; border: 2.5px solid #ff0055; border-radius: 8px; box-shadow: 0 0 8px rgba(255,0,85,0.3);">
                        <span style="position: absolute; top: 6px; left: 6px; font-size: 9px; font-weight: 800; background: #ff0055; color: #fff; padding: 2px 5px; border-radius: 4px;">KOTAK TWS HITAM</span>
                    </div>
                    
                    <!-- Box 2: Adaptor Charger (Sisi Kanan Dus) -->
                    <div class="box-adaptor" style="position: absolute; top: 30px; right: 25px; width: 180px; height: 220px; border: 2.5px solid #00d2ff; border-radius: 8px; box-shadow: 0 0 8px rgba(0,210,255,0.3);">
                        <span style="position: absolute; top: 6px; left: 6px; font-size: 9px; font-weight: 800; background: #00d2ff; color: #000; padding: 2px 5px; border-radius: 4px;">ADAPTOR CHARGER</span>
                    </div>
                </div>
            </div>

            <img id="streamImg" class="stream-img" alt="Camera Stream">
            <video id="localVideo" autoplay playsinline style="display:none; width: 100%; height: 100%; object-fit: contain;"></video>
            <canvas id="localCanvas" style="display:none; width: 100%; height: 100%; object-fit: contain;"></canvas>
            <div class="stream-placeholder" id="streamPlaceholder">
                <div class="icon">&#128247;</div>
                <p><strong>Kamera belum terhubung</strong><br>
                   Pastikan Raspberry Pi sudah menyala<br>dan terhubung ke jaringan yang sama.</p>
                <small id="piIpHint">IP Pi: <span id="piIpDisplay">—</span> | Port: 5000</small>
            </div>
        </div>

        <!-- Detection Status Bar -->
        <div class="detection-bar" id="detectionBar">
            <div class="det-icon waiting" id="detIcon">?</div>
            <div class="det-info">
                <div class="det-main" id="detMain">Menunggu Barcode Produk...</div>
                <div class="det-sub" id="detSub">Lakukan scan barcode produk dan part terlebih dahulu</div>
            </div>
            <button id="btnInspect" class="btn-inspect-ai" onclick="runInspection()" disabled>
                📷 AMBIL FOTO & INSPEKSI AI
            </button>
        </div>
    </div>

    <!-- KANAN: Scan Panel -->
    <div class="scan-panel">

        <!-- Contoh OK (Referensi) -->
        <div class="requirement-card">
            <div class="req-title">Contoh OK (Referensi)</div>
            <div class="reference-container" style="text-align: center; background: #0f172a; border-radius: 10px; padding: 8px; min-height: 150px; display: flex; align-items: center; justify-content: center; position: relative;">
                <img id="referenceImg" src="" style="width: 100%; border-radius: 8px; max-height: 180px; object-fit: contain; display: none;" alt="Reference Image">
                <div id="referencePlaceholder" style="color: #64748b; font-size: 13px;">
                    <div style="font-size: 24px; margin-bottom: 8px;">🖼️</div>
                    Belum ada contoh referensi untuk produk ini
                </div>
            </div>
        </div>

        <!-- Hasil Inspeksi Visual -->
        <div class="requirement-card" id="resultCard" style="display: none;">
            <div class="req-title">Hasil Analisis AI</div>
            <div id="resultContent">
                <!-- Hasil diisi dinamis oleh JS -->
            </div>
        </div>

        <!-- Scan Barcode Box -->
        <div class="scan-box" id="scanBox">
            <!-- Overlay untuk Konfirmasi Reject -->
            <div class="lock-overlay" id="rejectOverlay" style="background: #fef2f2; border: 1px solid #fecaca; padding: 18px; border-radius: 12px; margin-bottom: 18px; display: none; text-align: center;">
                <p style="color: #b91c1c; font-weight: 700; font-size: 15px; margin-bottom: 6px;">❌ PRODUK DI-REJECT AI</p>
                <p id="rejectReasonText" style="font-size: 13px; color: #7f1d1d; margin-bottom: 14px;"></p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button class="btn-action-rej btn-inspect-again" onclick="reInspect()" style="background: #2563eb; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px;">Inspeksi Ulang</button>
                    <button class="btn-action-rej btn-confirm-reject" onclick="confirmReject()" style="background: #dc2626; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px;">Konfirmasi Reject</button>
                </div>
            </div>

            <div class="scan-title">Scan Barcode</div>
            <div class="scan-step" id="stepText">Scan Produk</div>

            <input
                type="text"
                id="scanInput"
                class="scan-input"
                placeholder="Arahkan scanner ke barcode..."
                autocomplete="off"
            >

            <div class="status-msg" id="statusMsg"></div>
        </div>

    </div>
</div>

<!-- Floating shortcut -->
<a href="/downtime" class="floating-btn">Downtime</a>

<script>
// ============================================================
// KONFIGURASI
// ============================================================
const STREAM_URL_PATH   = '/vision/stream-url';
const HEALTH_PATH       = '/vision/health';
const INSPECT_PATH      = '/vision/inspect';

let step          = 1;
let kodeProduk    = '';
let kodePart      = '';
let piConnected   = false;
let inspectionImgData = null; // Menyimpan base64 dari frame terinspeksi
let activeDefectType  = 'ok';
let activeConfidence  = 100.0;

// State Kamera Lokal (Laptop/PC)
let localStream = null;
let localAnimFrameId = null;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    loadStreamUrl();
    checkCameraHealth();
    setInterval(checkCameraHealth, 5000); // Cek kesehatan kamera setiap 5 detik

    // Event listener ganti kamera
    document.getElementById('cameraSource').addEventListener('change', (e) => {
        switchCameraSource(e.target.value);
    });

    // Shortcut F2 → downtime
    document.addEventListener('keydown', e => {
        if (e.key === 'F2') window.location.href = '/downtime';
    });

    // Auto focus ke scanInput
    document.getElementById('scanInput').focus();
});

// ============================================================
// CEK KESEHATAN KAMERA (HEALTH CHECK)
// ============================================================
function checkCameraHealth() {
    const activeSource = document.getElementById('cameraSource').value;
    if (activeSource === 'local') return;

    fetch(HEALTH_PATH)
        .then(r => r.json())
        .then(data => {
            piConnected = data.ok === true && data.camera === true;
            updateConnectionUI(piConnected, data.error);
        })
        .catch(() => {
            piConnected = false;
            updateConnectionUI(false, "Tidak terhubung ke Pi.");
        });
}

function updateConnectionUI(connected, error) {
    const piDot   = document.getElementById('piDot');
    const piLabel = document.getElementById('piLabel');
    const badge     = document.getElementById('camBadge');
    const badgeText = document.getElementById('camBadgeText');
    const activeSource = document.getElementById('cameraSource').value;

    if (activeSource === 'local') {
        piDot.className   = 'pi-dot connected';
        piLabel.textContent = 'Webcam Laptop Aktif';
        badge.className = 'cam-status-badge cam-badge-ok';
        badgeText.textContent = 'Kamera Laptop';
        return;
    }

    if (connected) {
        piDot.className   = 'pi-dot connected';
        piLabel.textContent = 'Pi Camera Terhubung';
        badge.className = 'cam-status-badge cam-badge-ok';
        badgeText.textContent = 'Kamera Aktif';
    } else {
        piDot.className   = 'pi-dot disconnected';
        piLabel.textContent = 'Pi Offline';
        badge.className = 'cam-status-badge cam-badge-error';
        badgeText.textContent = error ? 'Kamera Offline' : 'Menghubungkan...';
    }
}

// ============================================================
// SWITCH CAMERA SOURCE
// ============================================================
function switchCameraSource(source) {
    if (source === 'pi') {
        stopLocalWebcam();
        
        const img = document.getElementById('streamImg');
        img.style.display = 'block';
        document.getElementById('streamPlaceholder').style.display = 'flex';
        document.getElementById('visionSubtitle').textContent = 'Raspberry Pi Camera — Real-time Quality Inspection';
        
        loadStreamUrl();
        checkCameraHealth();
    } else if (source === 'local') {
        document.getElementById('visionSubtitle').textContent = 'Kamera Laptop/PC — Simulasi Webcam Lokal';
        startLocalWebcam();
        updateConnectionUI(true);
    }
}

// ============================================================
// LOCAL WEBCAM CAPTURING
// ============================================================
function startLocalWebcam() {
    const video = document.getElementById('localVideo');
    const canvas = document.getElementById('localCanvas');
    const img = document.getElementById('streamImg');
    const placeholder = document.getElementById('streamPlaceholder');
    
    img.style.display = 'none';
    placeholder.style.display = 'none';
    canvas.style.display = 'block';
    
    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            localStream = stream;
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
                canvas.width = 640;
                canvas.height = 480;
                runLocalPreviewLoop();
            };
        })
        .catch(err => {
            console.error("Gagal membuka webcam lokal:", err);
            alert("Tidak dapat mengakses kamera laptop.");
            document.getElementById('cameraSource').value = 'pi';
            switchCameraSource('pi');
        });
}

function stopLocalWebcam() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }
    if (localAnimFrameId) {
        cancelAnimationFrame(localAnimFrameId);
        localAnimFrameId = null;
    }
    const video = document.getElementById('localVideo');
    video.srcObject = null;
    document.getElementById('localCanvas').style.display = 'none';
}

function runLocalPreviewLoop() {
    if (document.getElementById('cameraSource').value !== 'local') return;
    
    const video = document.getElementById('localVideo');
    const canvas = document.getElementById('localCanvas');
    const ctx = canvas.getContext('2d');
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        
        // Tambahkan garis bantu pembingkai/ROI (Region of Interest)
        ctx.strokeStyle = 'rgba(14, 165, 233, 0.5)';
        ctx.lineWidth = 2;
        ctx.strokeRect(canvas.width * 0.15, canvas.height * 0.15, canvas.width * 0.7, canvas.height * 0.7);
        ctx.fillStyle = 'rgba(14, 165, 233, 0.7)';
        ctx.font = '12px Inter, sans-serif';
        ctx.fillText("SEJAJARKAN PRODUK DI SINI", canvas.width * 0.15 + 10, canvas.height * 0.15 + 20);
    }
    
    localAnimFrameId = requestAnimationFrame(runLocalPreviewLoop);
}

// ============================================================
// LOAD STREAM URL DARI LARAVEL (Untuk Mode Pi)
// ============================================================
function loadStreamUrl() {
    fetch(STREAM_URL_PATH)
        .then(r => r.json())
        .then(data => {
            const ip = data.url.split('//')[1].split(':')[0];
            document.getElementById('piIpDisplay').textContent = ip;
            document.getElementById('piIpHint').style.display = 'block';

            const img = document.getElementById('streamImg');
            img.src = data.url;
            img.style.display = 'block';
            document.getElementById('streamPlaceholder').style.display = 'none';

            img.onerror = () => {
                img.style.display = 'none';
                document.getElementById('streamPlaceholder').style.display = 'flex';
                updateConnectionUI(false, "Stream Offline.");
            };
        })
        .catch(() => {
            updateConnectionUI(false, "Gagal memuat URL stream.");
        });
}

// ============================================================
// TAMPILKAN GAMBAR REFERENSI PRODUK
// ============================================================
function loadReferenceImage(productCode) {
    const img = document.getElementById('referenceImg');
    const placeholder = document.getElementById('referencePlaceholder');
    
    img.src = `/vision/reference-image/${productCode}`;
    img.style.display = 'none';
    placeholder.style.display = 'flex';
    placeholder.textContent = 'Memuat contoh referensi...';

    img.onload = () => {
        img.style.display = 'block';
        placeholder.style.display = 'none';
    };

    img.onerror = () => {
        img.style.display = 'none';
        placeholder.style.display = 'flex';
        placeholder.innerHTML = `
            <div style="font-size: 24px; margin-bottom: 8px;">⚠️</div>
            Referensi belum diset oleh Admin.
        `;
    };
}

// ============================================================
// RUN INSPECTION
// ============================================================
function runInspection() {
    const btn = document.getElementById('btnInspect');
    btn.disabled = true;
    btn.textContent = '⏳ MENGANALISIS...';

    const bar = document.getElementById('detectionBar');
    const icon = document.getElementById('detIcon');
    const detMain = document.getElementById('detMain');
    const detSub = document.getElementById('detSub');

    detMain.textContent = 'Inspeksi Visual Berjalan...';
    detSub.textContent = 'AI sedang menganalisis kecacatan produk';
    icon.className = 'det-icon waiting';
    icon.textContent = '🔄';

    const activeSource = document.getElementById('cameraSource').value;

    if (activeSource === 'local') {
        // Simulasi webcam lokal
        setTimeout(() => {
            const canvas = document.getElementById('localCanvas');
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            inspectionImgData = dataUrl.split(',')[1]; // Dapatkan base64 saja
            
            // Buat hasil simulasi acak (80% pass, 20% reject)
            const isPass = Math.random() > 0.20;
            const similarity = isPass ? (85 + Math.random() * 12) : (50 + Math.random() * 30);
            const similarity_rounded = Math.round(similarity * 10) / 10;
            
            let defect = 'ok';
            let confidence = similarity_rounded;
            if (!isPass) {
                const defects = ['tear', 'dent', 'missing'];
                defect = defects[Math.floor(Math.random() * defects.length)];
                confidence = Math.round((70 + Math.random() * 25) * 10) / 10;
            }

            const responseSimulated = {
                ok: true,
                status: isPass ? 'pass' : 'reject',
                defect_type: defect,
                confidence: confidence,
                similarity: similarity_rounded,
                image_base64: inspectionImgData
            };
            
            showInspectionResult(responseSimulated);
        }, 1500);
    } else {
        // Panggil endpoint Pi sesungguhnya
        fetch(INSPECT_PATH, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_code: kodeProduk })
        })
        .then(r => {
            if (!r.ok) {
                return r.json().then(err => { throw err; });
            }
            return r.json();
        })
        .then(data => {
            showInspectionResult(data);
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = '📷 AMBIL FOTO & INSPEKSI AI';
            
            bar.className = 'detection-bar error-state';
            icon.className = 'det-icon error-state';
            icon.textContent = '❌';
            detMain.textContent = 'Inspeksi AI Gagal';
            detSub.textContent = err.error || 'Terjadi kesalahan saat memproses inspeksi.';
            showStatus(err.error || 'Gagal inspeksi', 'error');

            // Buka kembali input scan jika terjadi kesalahan agar operator dapat scan ulang / reset
            scanInput.disabled = false;
            scanInput.focus();
        });
    }
}

// ============================================================
// TAMPILKAN HASIL INSPEKSI
// ============================================================
function showInspectionResult(data) {
    const btn = document.getElementById('btnInspect');
    btn.textContent = '📷 AMBIL FOTO & INSPEKSI AI';
    
    const card = document.getElementById('resultCard');
    const bar = document.getElementById('detectionBar');
    const icon = document.getElementById('detIcon');
    const detMain = document.getElementById('detMain');
    const detSub = document.getElementById('detSub');

    // Sembunyikan card hasil detail di kanan agar UI bersih
    card.style.display = 'none';
    inspectionImgData = data.image_base64;
    activeDefectType = data.defect_type;
    activeConfidence = data.confidence;

    let defectLabel = 'PRODUK OK';
    if (data.defect_type === 'tear') defectLabel = 'ROBEK / GORESAN / BROKEN';
    if (data.defect_type === 'dent') defectLabel = 'PENYOK / PEYOT / DEFORMASI';
    if (data.defect_type === 'missing') defectLabel = 'KOMPONEN HILANG / MISSING';

    if (data.status === 'pass') {
        bar.className = 'detection-bar complete';
        icon.className = 'det-icon complete';
        icon.textContent = 'OK';
        detMain.textContent = '✅ PASS — PRODUK OK';
        detSub.textContent = 'Barang lengkap & sesuai acuan. Mencatat ke database...';

        // Kirim data PASS ke database dan auto-reset form
        kirimData(kodeProduk, kodePart, 'process', 'ok', data.similarity, inspectionImgData);

    } else {
        bar.className = 'detection-bar error-state';
        icon.className = 'det-icon error-state';
        icon.textContent = 'NG';
        detMain.textContent = '❌ NOT PASS — REJECT';
        detSub.textContent = `Peringatan: Terdeteksi ${defectLabel}. Mencatat reject...`;

        // Kirim data REJECT ke database secara otomatis dan auto-reset form
        kirimData(kodeProduk, kodePart, 'reject', data.defect_type, data.confidence, inspectionImgData);
    }
}

// ============================================================
// HANDLER REJECT
// ============================================================
function showRejectConfirmation(defectType, confidence) {
    const input = document.getElementById('scanInput');
    input.disabled = true;

    let text = 'Kerusakan: ';
    if (defectType === 'tear') text += 'ROBEK / GORESAN / BROKEN';
    if (defectType === 'dent') text += 'PENYOK / PEYOT / DEFORMASI';
    if (defectType === 'missing') text += 'KOMPONEN HILANG / MISSING';

    document.getElementById('rejectReasonText').textContent = text;
    document.getElementById('rejectOverlay').style.display = 'block';
}

function reInspect() {
    // Sembunyikan overlay
    document.getElementById('rejectOverlay').style.display = 'none';
    document.getElementById('resultCard').style.display = 'none';
    
    // Kembalikan preview video aktif
    const activeSource = document.getElementById('cameraSource').value;
    if (activeSource === 'pi') {
        loadStreamUrl();
    }

    // Aktifkan kembali tombol inspeksi
    const btn = document.getElementById('btnInspect');
    btn.disabled = false;

    const bar = document.getElementById('detectionBar');
    const icon = document.getElementById('detIcon');
    const detMain = document.getElementById('detMain');
    const detSub = document.getElementById('detSub');

    bar.className = 'detection-bar';
    icon.className = 'det-icon waiting';
    icon.textContent = '📷';
    detMain.textContent = 'Kamera Siap';
    detSub.textContent = 'Atur ulang posisi barang, lalu tekan tombol inspeksi';
}

function confirmReject() {
    document.getElementById('rejectOverlay').style.display = 'none';
    showStatus('Mencatat data Reject ke DB...', 'info');
    kirimData(kodeProduk, kodePart, 'reject', activeDefectType, activeConfidence, inspectionImgData);
}

// ============================================================
// SCAN SYSTEM (BARCODE READER)
// ============================================================
const scanInput  = document.getElementById('scanInput');
const stepText   = document.getElementById('stepText');
const statusMsg  = document.getElementById('statusMsg');

scanInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();

        if (step === 1) {
            kodeProduk = scanInput.value.trim();
            scanInput.value = '';

            if (!kodeProduk) {
                showStatus('Barcode produk tidak boleh kosong', 'error');
                return;
            }

            step = 2;
            stepText.textContent = 'Scan Part / Aksesori';
            showStatus('Produk: ' + kodeProduk + ' — Scan part selanjutnya', 'info');
            loadReferenceImage(kodeProduk);

        } else if (step === 2) {
            kodePart = scanInput.value.trim();
            scanInput.value = '';

            if (!kodePart) {
                showStatus('Barcode part tidak boleh kosong', 'error');
                return;
            }

            // Lock scan input dan jalankan inspeksi otomatis
            scanInput.disabled = true;
            stepText.textContent = 'Menganalisis Kualitas (AI)...';
            showStatus('Scan lengkap! Mengambil foto & menjalankan inspeksi AI otomatis...', 'info');
            
            const btn = document.getElementById('btnInspect');
            btn.disabled = true;
            btn.textContent = '⏳ MENGANALISIS...';

            const bar = document.getElementById('detectionBar');
            const icon = document.getElementById('detIcon');
            const detMain = document.getElementById('detMain');
            const detSub = document.getElementById('detSub');

            bar.className = 'detection-bar complete';
            icon.className = 'det-icon complete';
            icon.textContent = '🔄';
            detMain.textContent = 'Inspeksi AI Otomatis...';
            detSub.textContent = 'Memeriksa kecacatan barang (robek/peyot/missing) secara otomatis...';

            // Otomatis jalankan inspeksi tanpa perlu klik tombol
            setTimeout(() => {
                runInspection();
            }, 300);
        }
    }
});

// ============================================================
// KIRIM DATA KE LARAVEL
// ============================================================
function kirimData(produk, part, status, defectType, confidence, fotoBase64) {
    showStatus('Menyimpan data...', 'info');

    fetch('/insert/proses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            produk: produk,
            part: part,
            status: status,
            defect_type: defectType,
            confidence: confidence,
            foto: fotoBase64
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showStatus(status === 'reject' ? 'Data reject berhasil dicatat!' : 'Data produksi berhasil disimpan!', 'success');
            setTimeout(resetForm, 2000);
        } else {
            showStatus(res.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(() => {
        showStatus('Server error — data gagal disimpan', 'error');
    });
}

// ============================================================
// HELPERS
// ============================================================
function showStatus(msg, type) {
    statusMsg.textContent = msg;
    statusMsg.className   = 'status-msg ' + type;
    statusMsg.style.display = 'block';
}

function resetForm() {
    step          = 1;
    kodeProduk    = '';
    kodePart      = '';
    inspectionImgData = null;
    
    document.getElementById('resultCard').style.display = 'none';
    document.getElementById('rejectOverlay').style.display = 'none';
    
    const img = document.getElementById('referenceImg');
    img.src = '';
    img.style.display = 'none';
    document.getElementById('referencePlaceholder').style.display = 'flex';
    document.getElementById('referencePlaceholder').innerHTML = `
        <div style="font-size: 24px; margin-bottom: 8px;">🖼️</div>
        Belum ada contoh referensi untuk produk ini
    `;

    const bar = document.getElementById('detectionBar');
    const icon = document.getElementById('detIcon');
    const detMain = document.getElementById('detMain');
    const detSub = document.getElementById('detSub');

    bar.className = 'detection-bar';
    icon.className = 'det-icon waiting';
    icon.textContent = '?';
    detMain.textContent = 'Menunggu Barcode Produk...';
    detSub.textContent = 'Lakukan scan barcode produk dan part terlebih dahulu';

    const btn = document.getElementById('btnInspect');
    btn.disabled = true;
    btn.textContent = '📷 AMBIL FOTO & INSPEKSI AI';

    const input = document.getElementById('scanInput');
    input.disabled = false;
    input.value = '';
    input.focus();

    stepText.textContent = 'Scan Produk';
    statusMsg.className  = 'status-msg';
    statusMsg.textContent = '';
    statusMsg.style.display = 'none';

    // Kembalikan preview video dari Pi jika mode Pi
    const activeSource = document.getElementById('cameraSource').value;
    if (activeSource === 'pi') {
        loadStreamUrl();
    }
}
</script>

</body>
</html>