<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Station Insert — ProTrack</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">

<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #eef2f7, #e2e8f0);
    color: #1f2937;
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
    min-height: 340px;
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

/* Item count progress */
.item-progress {
    display: flex; align-items: center; gap: 10px;
}
.item-dots {
    display: flex; gap: 6px;
}
.item-dot {
    width: 18px; height: 18px;
    border-radius: 5px;
    border: 2px solid #e2e8f0;
    background: #f1f5f9;
    transition: all 0.3s;
}
.item-dot.filled  { background: #22c55e; border-color: #16a34a; }
.item-dot.missing { background: #fbbf24; border-color: #d97706; animation: blink 1s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.4} }

.item-count-label {
    font-size: 13px; font-weight: 700; color: #0f172a;
    white-space: nowrap;
}

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
.req-items { display: flex; flex-direction: column; gap: 8px; }
.req-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px;
    border-radius: 10px;
    background: #f8fafc;
    font-size: 13px; color: #374151;
    transition: all 0.3s;
}
.req-item.detected { background: #f0fdf4; color: #15803d; }
.req-item-dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: #cbd5e1;
    flex-shrink: 0; transition: all 0.3s;
}
.req-item.detected .req-item-dot { background: #22c55e; }

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
        <a href="/downtime" class="nav-btn btn-target">Target & Downtime</a>
    </div>
</div>

<!-- MAIN -->
<div class="main-grid">

    <!-- KIRI: Vision Camera Panel -->
    <div class="vision-panel">
        <div class="vision-header">
            <div>
                <div class="vision-title">Kamera Deteksi Isi Box</div>
                <div class="vision-subtitle" id="visionSubtitle">Raspberry Pi Camera — Real-time Object Detection</div>
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
                <div class="det-main" id="detMain">Menunggu koneksi kamera...</div>
                <div class="det-sub" id="detSub">Raspberry Pi Vision Server belum terhubung</div>
            </div>
            <div class="item-progress">
                <div class="item-dots" id="itemDots"></div>
                <div class="item-count-label" id="itemCountLabel">0/0</div>
            </div>
        </div>
    </div>

    <!-- KANAN: Scan Panel -->
    <div class="scan-panel">

        <!-- Checklist Item Box -->
        <div class="requirement-card">
            <div class="req-title">Item Yang Harus Ada Dalam Box</div>
            <div class="req-items" id="reqItems">
                <div class="req-item">
                    <div class="req-item-dot"></div>
                    <span>Menunggu data produk...</span>
                </div>
            </div>
        </div>

        <!-- Scan Barcode Box -->
        <div class="scan-box locked" id="scanBox">

            <!-- Lock notice -->
            <div class="lock-overlay visible" id="lockOverlay">
                <p>Scan dikunci</p>
                <small>Kelengkapan isi box belum terverifikasi oleh kamera</small>
            </div>

            <!-- Unlock notice -->
            <div class="unlock-notice" id="unlockNotice">
                <p>Box lengkap — Scan barcode sekarang!</p>
            </div>

            <div class="scan-title">Scan Barcode</div>
            <div class="scan-step" id="stepText">Scan Produk</div>

            <input
                type="text"
                id="scanInput"
                class="scan-input"
                placeholder="Arahkan scanner ke barcode..."
                disabled
                autocomplete="off"
            >

            <div class="status-msg" id="statusMsg"></div>
        </div>

    </div>
</div>

<!-- Floating shortcut -->
<a href="/downtime" class="floating-btn">Target & Downtime</a>

<script>
// ============================================================
// KONFIGURASI
// ============================================================
const POLL_INTERVAL_MS  = 1500;   // polling ke /vision/status tiap 1.5 detik
const STREAM_URL_PATH   = '/vision/stream-url';
const STATUS_PATH       = '/vision/status';
const RESET_PATH        = '/vision/reset';

// Item default (akan diupdate dari server)
let requiredCount = {{ env('VISION_REQUIRED_COUNT', 2) }};
let productItems  = [];
let scanAllowed   = false;
let piConnected   = false;
let step          = 1;
let kodeProduk    = '';
let pollTimer     = null;

// State Kamera Lokal (Laptop/PC)
let localStream = null;
let localAnimFrameId = null;
let localStableStart = null;
let lastLocalCount = -1;
let localIsComplete = false;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    loadStreamUrl();
    startPolling();

    // Event listener ganti kamera
    document.getElementById('cameraSource').addEventListener('change', (e) => {
        switchCameraSource(e.target.value);
    });

    // Shortcut F2 → downtime
    document.addEventListener('keydown', e => {
        if (e.key === 'F2') window.location.href = '/downtime';
    });
});

// ============================================================
// SWITCH CAMERA SOURCE
// ============================================================
function switchCameraSource(source) {
    if (source === 'pi') {
        stopLocalWebcam();
        
        const img = document.getElementById('streamImg');
        img.style.display = 'block';
        document.getElementById('streamPlaceholder').style.display = 'flex';
        document.getElementById('visionSubtitle').textContent = 'Raspberry Pi Camera — Real-time Object Detection';
        
        loadStreamUrl();
        if (!pollTimer) {
            startPolling();
        }
    } else if (source === 'local') {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
        
        document.getElementById('visionSubtitle').textContent = 'Kamera Laptop/PC — Simulasi Webcam Lokal';
        startLocalWebcam();
    }
}

// ============================================================
// LOCAL WEBCAM CAPTURING & BLOB DETECTION
// ============================================================
function startLocalWebcam() {
    const video = document.getElementById('localVideo');
    const canvas = document.getElementById('localCanvas');
    const img = document.getElementById('streamImg');
    const placeholder = document.getElementById('streamPlaceholder');
    
    img.style.display = 'none';
    placeholder.style.display = 'none';
    canvas.style.display = 'block';
    
    // Reset local state
    localStableStart = null;
    lastLocalCount = -1;
    localIsComplete = false;

    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            localStream = stream;
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
                canvas.width = 640;
                canvas.height = 480;
                runLocalDetectionLoop();
            };
        })
        .catch(err => {
            console.error("Gagal membuka webcam lokal:", err);
            alert("Tidak dapat mengakses kamera laptop. Pastikan izin kamera telah diberikan.");
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

function runLocalDetectionLoop() {
    if (document.getElementById('cameraSource').value !== 'local') return;
    
    const video = document.getElementById('localVideo');
    const canvas = document.getElementById('localCanvas');
    const ctx = canvas.getContext('2d');
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        // 1. Gambar frame cermin agar natural saat dilihat di layar
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        
        // 2. Tentukan ROI (Region of Interest) - 80% dari ukuran frame
        const rx = Math.floor(canvas.width * 0.1);
        const ry = Math.floor(canvas.height * 0.1);
        const rw = Math.floor(canvas.width * 0.8);
        const rh = Math.floor(canvas.height * 0.8);
        
        // 3. Lakukan deteksi objek local
        const blobs = detectLocalBlobs(ctx, canvas.width, canvas.height, rx, ry, rw, rh);
        const count = blobs.length;
        
        // 4. Gambar Box ROI (Orange)
        ctx.strokeStyle = '#ff8c00';
        ctx.lineWidth = 3;
        ctx.strokeRect(rx, ry, rw, rh);
        ctx.fillStyle = '#ff8c00';
        ctx.font = 'bold 13px Inter, sans-serif';
        ctx.fillText("AREA DETEKSI (LOCAL WEBCAM)", rx + 10, ry + 22);
        
        // 5. Gambar bounding box untuk setiap objek yang terdeteksi
        ctx.strokeStyle = '#00c850';
        ctx.lineWidth = 3;
        blobs.forEach((blob, idx) => {
            ctx.strokeRect(blob.x, blob.y, blob.w, blob.h);
            ctx.fillStyle = '#00c850';
            ctx.font = 'bold 16px Inter, sans-serif';
            ctx.fillText(`#${idx + 1}`, blob.x, blob.y - 8);
        });
        
        // 6. Logika Stabilisasi Count (harus stabil selama 1.5 detik)
        if (count >= requiredCount) {
            if (count === lastLocalCount) {
                if (localStableStart === null) {
                    localStableStart = Date.now();
                }
                const elapsed = (Date.now() - localStableStart) / 1000;
                if (elapsed >= 1.5) {
                    localIsComplete = true;
                }
            } else {
                localStableStart = null;
                localIsComplete = false;
                lastLocalCount = count;
            }
        } else {
            localStableStart = null;
            localIsComplete = false;
            lastLocalCount = count;
        }
        
        // 7. Update UI dengan mock status data agar sinkron dengan HTML
        const mockData = {
            ok: true,
            camera_ok: true,
            is_complete: localIsComplete,
            scan_allowed: localIsComplete,
            item_count: count,
            required_count: requiredCount,
            stable_seconds: localStableStart ? Math.round((Date.now() - localStableStart)/1000) : 0,
            product_config: {
                required_count: requiredCount,
                items: productItems.length > 0 ? productItems : null
            },
            error: null
        };
        updateVisionUI(mockData);
        
        // 8. Gambar visual status bar di atas kanvas (persis seperti visual dari Pi)
        ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
        ctx.fillRect(0, 0, canvas.width, 55);
        
        const statusColor = localIsComplete ? '#22c55e' : '#fbbf24';
        ctx.fillStyle = statusColor;
        ctx.font = 'bold 18px Inter, sans-serif';
        ctx.fillText(`Item: ${count}/${requiredCount}`, 20, 24);
        ctx.font = '13px Inter, sans-serif';
        ctx.fillText(localIsComplete ? "LENGKAP — SCAN OK" : "BELUM LENGKAP", 20, 44);
        
        // Tampilkan timestamp
        ctx.fillStyle = '#94a3b8';
        ctx.font = '12px Inter, sans-serif';
        const ts = new Date().toLocaleTimeString('id-ID');
        ctx.fillText(ts, canvas.width - 90, 32);
    }
    
    localAnimFrameId = requestAnimationFrame(runLocalDetectionLoop);
}

// Connected Component Labeling via BFS sederhana untuk mendeteksi blob kontras tinggi di browser
function detectLocalBlobs(ctx, width, height, roiX, roiY, roiW, roiH) {
    const imgData = ctx.getImageData(roiX, roiY, roiW, roiH);
    const data = imgData.data;
    
    // 1. Ubah ke grayscale & hitung rata-rata kecerahan
    const gray = new Uint8Array(roiW * roiH);
    let sum = 0;
    for (let i = 0, j = 0; i < data.length; i += 4, j++) {
        const r = data[i];
        const g = data[i+1];
        const b = data[i+2];
        const v = 0.299*r + 0.587*g + 0.114*b;
        gray[j] = v;
        sum += v;
    }
    const avg = sum / (roiW * roiH);
    
    // 2. Thresholding: beri tanda pixel kontras tinggi (dibanding rata-rata)
    const threshold = 35; // sensitivitas deteksi
    const binary = new Uint8Array(roiW * roiH);
    for (let i = 0; i < gray.length; i++) {
        binary[i] = Math.abs(gray[i] - avg) > threshold ? 1 : 0;
    }
    
    // 3. Pelabelan kontur / BFS
    const visited = new Uint8Array(roiW * roiH);
    const blobs = [];
    
    // Lewati tiap 2 pixel (step 2) agar proses render sangat ringan & FPS tinggi
    for (let y = 0; y < roiH; y += 2) {
        for (let x = 0; x < roiW; x += 2) {
            const idx = y * roiW + x;
            if (binary[idx] === 1 && !visited[idx]) {
                // BFS mencari luas blob
                let minX = x, maxX = x, minY = y, maxY = y;
                let count = 0;
                const queue = [idx];
                visited[idx] = 1;
                
                let qHead = 0;
                while (qHead < queue.length && queue.length < 2000) {
                    const curr = queue[qHead++];
                    const cy = Math.floor(curr / roiW);
                    const cx = curr % roiW;
                    count++;
                    
                    if (cx < minX) minX = cx;
                    if (cx > maxX) maxX = cx;
                    if (cy < minY) minY = cy;
                    if (cy > maxY) maxY = cy;
                    
                    const neighbors = [
                        [cx + 2, cy], [cx - 2, cy],
                        [cx, cy + 2], [cx, cy - 2]
                    ];
                    for (const [nx, ny] of neighbors) {
                        if (nx >= 0 && nx < roiW && ny >= 0 && ny < roiH) {
                            const nIdx = ny * roiW + nx;
                            if (binary[nIdx] === 1 && !visited[nIdx]) {
                                visited[nIdx] = 1;
                                queue.push(nIdx);
                            }
                        }
                    }
                }
                
                // Filter berdasarkan ukuran blob (agar noise kecil & background besar terabaikan)
                if (count > 80 && count < (roiW * roiH * 0.4)) {
                    blobs.push({
                        x: minX + roiX,
                        y: minY + roiY,
                        w: maxX - minX + 1,
                        h: maxY - minY + 1,
                        size: count
                    });
                }
            }
        }
    }
    return blobs;
}

// ============================================================
// LOAD STREAM URL DARI LARAVEL
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
            };
        })
        .catch(() => {});
}

// ============================================================
// POLLING STATUS DETEKSI
// ============================================================
function startPolling() {
    pollOnce();
    pollTimer = setInterval(pollOnce, POLL_INTERVAL_MS);
}

// Reset local detection state saat scan berhasil
function resetLocalDetection() {
    localStableStart = null;
    lastLocalCount = -1;
    localIsComplete = false;
}

function pollOnce() {
    fetch(STATUS_PATH)
        .then(r => r.json())
        .then(data => updateVisionUI(data))
        .catch(() => updateVisionUI({ ok: false, camera_ok: false, is_complete: false, scan_allowed: false, item_count: 0, required_count: requiredCount, error: 'Tidak dapat menghubungi server.' }));
}

// ============================================================
// UPDATE UI BERDASARKAN STATUS VISION
// ============================================================
function updateVisionUI(data) {
    piConnected   = data.camera_ok === true;
    scanAllowed   = data.scan_allowed === true;
    requiredCount = data.required_count || requiredCount;
    const count   = data.item_count || 0;
    const isComplete = data.is_complete === true;

    // ---- Koneksi Pi (navbar) ----
    const piDot   = document.getElementById('piDot');
    const piLabel = document.getElementById('piLabel');
    
    // Bedakan label koneksi antara mode Pi dan mode Local
    const activeSource = document.getElementById('cameraSource').value;
    if (activeSource === 'local') {
        piDot.className   = 'pi-dot connected';
        piLabel.textContent = 'Webcam Laptop Aktif';
    } else {
        if (piConnected) {
            piDot.className   = 'pi-dot connected';
            piLabel.textContent = 'Pi Camera Terhubung';
        } else if (!data.ok) {
            piDot.className   = 'pi-dot disconnected';
            piLabel.textContent = 'Pi Tidak Terhubung';
        } else {
            piDot.className   = 'pi-dot connecting';
            piLabel.textContent = 'Menghubungkan...';
        }
    }

    // ---- Camera badge ----
    const badge     = document.getElementById('camBadge');
    const badgeText = document.getElementById('camBadgeText');
    
    if (activeSource === 'local') {
        badge.className = 'cam-status-badge cam-badge-ok';
        badgeText.textContent = 'Kamera Laptop';
    } else {
        badge.className = 'cam-status-badge ' + (piConnected ? 'cam-badge-ok' : !data.ok ? 'cam-badge-error' : 'cam-badge-connecting');
        badgeText.textContent = piConnected ? 'Kamera Aktif' : (!data.ok ? 'Kamera Offline' : 'Menghubungkan...');
    }

    // ---- Detection bar ----
    const bar    = document.getElementById('detectionBar');
    const icon   = document.getElementById('detIcon');
    const detMain = document.getElementById('detMain');
    const detSub  = document.getElementById('detSub');

    if (activeSource !== 'local' && !piConnected) {
        bar.className = 'detection-bar';
        icon.className = 'det-icon waiting';
        icon.textContent = '?';
        detMain.textContent = 'Kamera tidak terhubung';
        detSub.textContent  = data.error || 'Periksa koneksi Raspberry Pi ke jaringan';
    } else if (isComplete) {
        bar.className = 'detection-bar complete';
        icon.className = 'det-icon complete';
        icon.textContent = 'OK';
        detMain.textContent = 'Box Lengkap — Siap Discan';
        detSub.textContent  = `Semua ${requiredCount} item terdeteksi dalam box`;
    } else {
        bar.className = 'detection-bar incomplete';
        icon.className = 'det-icon incomplete';
        icon.textContent = '!';
        detMain.textContent = `Belum Lengkap — ${count} dari ${requiredCount} item terdeteksi`;
        detSub.textContent  = 'Pastikan semua item sudah dimasukkan ke dalam box';
    }

    // ---- Item dots ----
    const dotsContainer = document.getElementById('itemDots');
    dotsContainer.innerHTML = '';
    for (let i = 0; i < requiredCount; i++) {
        const dot = document.createElement('div');
        dot.className = 'item-dot ' + (i < count ? 'filled' : ((piConnected || activeSource === 'local') ? 'missing' : ''));
        dotsContainer.appendChild(dot);
    }
    document.getElementById('itemCountLabel').textContent = `${count}/${requiredCount}`;

    // ---- Requirement checklist ----
    updateChecklist(count, data.product_config);

    // ---- Scan box lock/unlock ----
    const scanBox      = document.getElementById('scanBox');
    const lockOverlay  = document.getElementById('lockOverlay');
    const unlockNotice = document.getElementById('unlockNotice');
    const scanInput    = document.getElementById('scanInput');

    if (scanAllowed) {
        scanBox.className = 'scan-box unlocked';
        lockOverlay.className  = 'lock-overlay';
        unlockNotice.className = 'unlock-notice visible';
        scanInput.disabled = false;
        scanInput.focus();
    } else {
        scanBox.className = 'scan-box locked';
        lockOverlay.className  = 'lock-overlay visible';
        unlockNotice.className = 'unlock-notice';
        scanInput.disabled = true;
    }
}

// ============================================================
// UPDATE CHECKLIST ITEMS
// ============================================================
function updateChecklist(detectedCount, productConfig) {
    const container = document.getElementById('reqItems');
    let items = [];

    if (productConfig && productConfig.items && productConfig.items.length > 0) {
        items = productConfig.items;
    } else {
        // Default generic items
        for (let i = 0; i < requiredCount; i++) {
            items.push(i === 0 ? 'Unit Utama (Produk)' : `Aksesori ${i}`);
        }
    }

    container.innerHTML = '';
    items.forEach((item, i) => {
        const detected = i < detectedCount;
        const el = document.createElement('div');
        el.className = 'req-item ' + (detected ? 'detected' : '');
        el.innerHTML = `
            <div class="req-item-dot"></div>
            <span>${item}</span>
            <span style="margin-left:auto;font-size:12px;font-weight:700;color:${detected ? '#16a34a' : '#94a3b8'}">${detected ? 'Terdeteksi' : 'Belum'}</span>
        `;
        container.appendChild(el);
    });
}

// ============================================================
// SCAN SYSTEM
// ============================================================
const scanInput  = document.getElementById('scanInput');
const stepText   = document.getElementById('stepText');
const statusMsg  = document.getElementById('statusMsg');

scanInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (!scanAllowed) return;

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

        } else {
            const kodePart = scanInput.value.trim();
            scanInput.value = '';

            if (!kodePart) {
                showStatus('Barcode part tidak boleh kosong', 'error');
                return;
            }

            kirimData(kodeProduk, kodePart);
        }
    }
});

// ============================================================
// KIRIM DATA KE LARAVEL
// ============================================================
function kirimData(produk, part) {
    showStatus('Menyimpan data...', 'info');

    fetch('/insert/proses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ produk, part })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showStatus('Data berhasil disimpan!', 'success');

            // Reset status deteksi lokal jika memakai webcam laptop
            if (document.getElementById('cameraSource').value === 'local') {
                resetLocalDetection();
            } else {
                // Reset Pi detection state setelah scan sukses jika memakai Pi camera
                fetch(RESET_PATH, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).catch(() => {});
            }

        } else {
            showStatus(res.message || 'Gagal menyimpan data', 'error');
        }
        resetForm();
    })
    .catch(() => {
        showStatus('Server error — coba lagi', 'error');
        resetForm();
    });
}

// ============================================================
// HELPERS
// ============================================================
function showStatus(msg, type) {
    statusMsg.textContent = msg;
    statusMsg.className   = 'status-msg ' + type;
}

function resetForm() {
    setTimeout(() => {
        step       = 1;
        kodeProduk = '';
        stepText.textContent = 'Scan Produk';
        statusMsg.className  = 'status-msg';
        statusMsg.textContent = '';
        if (scanAllowed) scanInput.focus();
    }, 2000);
}
</script>

</body>
</html>