<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Station Timbang</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Segoe UI, sans-serif;
}

body{
    background:linear-gradient(135deg,#eef2f7,#e2e8f0);
    color:#1f2937;
}

/* NAVBAR */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 30px;
    background:rgba(255,255,255,0.7);
    backdrop-filter:blur(12px);
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

.nav-left{
    display:flex;
    align-items:center;
    gap:10px;
}

.badge{
    background:#0ea5e9;
    color:white;
    padding:5px 10px;
    border-radius:6px;
    font-size:12px;
}

.navbar h2{
    font-size:20px;
    font-weight:600;
}

.nav-right{
    display:flex;
    gap:10px;
}

.nav-btn{
    text-decoration:none;
    padding:8px 18px;
    border-radius:8px;
    font-size:14px;
    transition:0.3s;
}

.btn-target{
    background:#00cdae;
    color:white;
    font-weight: 500;
}

.btn-target:hover{
    background:#00aa90;
    box-shadow: 0 4px 10px rgba(0, 205, 174, 0.4);
}

/* MAIN */
.container{
    padding:30px;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:calc(100vh - 100px);
}

/* INPUT */
.input-box{
    width:360px;
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 15px 40px rgba(0,0,0,0.1);
    transition:0.4s;
}



/* TITLE */
.title{
    font-size:20px;
    font-weight:600;
    margin-bottom:5px;
}

.step{
    color:#0ea5e9;
    margin-bottom:15px;
}

/* INPUT */
input{
    width:100%;
    padding:14px;
    font-size:18px;
    text-align:center;
    border:1px solid #d1d5db;
    border-radius:10px;
    outline:none;
}

input:focus{
    border-color:#0ea5e9;
    box-shadow:0 0 0 2px rgba(14,165,233,0.2);
}

/* STATUS */
.status{
    margin-top:15px;
    font-size:14px;
}

.success{ color:#16a34a; }
.error{ color:#dc2626; }
.info{ color:#0284c7; }

/* FLOAT BUTTON */
.floating-btn{
    position:fixed;
    bottom:25px;
    right:25px;
    background:#ef4444;
    color:white;
    padding:15px 20px;
    border-radius:50px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    text-decoration:none;
    font-size:14px;
    transition:0.3s;
}

.floating-btn:hover{
    background:#dc2626;
}

/* NEW NAVBAR EXTRAS */
.btn-back {
    background: #f1f5f9;
    color: #475569;
    margin-right: 15px;
    font-weight: 500;
}
.btn-back:hover {
    background: #e2e8f0;
}
.operator-badge {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 14px;
    color: #475569;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-right: 15px;
}
.operator-badge span {
    background: #0ea5e9;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">
        <h2>Station Timbang</h2>
        <div class="badge">LIVE</div>
    </div>

    <div class="nav-right" style="display:flex; align-items:center;">
        <div class="operator-badge"><span>ID</span> {{ session('id_karyawan') ?? 'Unknown' }}</div>
        <a href="/select-station" class="nav-btn btn-back">Kembali</a>
        <a href="/downtime" class="nav-btn btn-target">Target & Downtime</a>
    </div>
</div>

<!-- MAIN -->
<div class="container">

    <!-- INPUT -->
    <div class="input-box" id="inputBox">

        <div class="title">Scan Barcode</div>
        <div class="step" id="stepText">Scan Produk</div>

        <input type="text" id="scanInput" placeholder="Scan barcode..." autofocus>

        <div class="step" style="margin-top:15px; color:#475569;" id="beratLabel">Input Berat Aktual (Kg)</div>
        <input type="number" id="beratInput" placeholder="0.00" step="0.01" style="font-size:24px; font-weight:bold; color:#0ea5e9;">

        <button id="tareBtn" style="width:100%; margin-top:15px; padding:12px; border-radius:10px; background:#475569; color:white; font-weight:600; border:none; cursor:pointer; font-size:14px; transition:0.3s;">Zero / Tare Timbangan</button>

        <div class="status" id="statusText"></div>

    </div>

</div>

<!-- FLOAT BUTTON -->
<a href="/downtime" class="floating-btn" style="background:#00cdae;">Target & Downtime</a>

<script>

// ==========================
// SHORTCUT KEY (CEPAT)
// ==========================
document.addEventListener('keydown', function(e){
    if(e.key === 'F2'){
        window.location.href = '/downtime';
    }
});

// ==========================
// INISIALISASI & POLLING TIMBANGAN (MOVING AVERAGE + LOCK 30 DETIK)
// ==========================
const inputBox = document.getElementById('inputBox');
document.getElementById('scanInput').focus();
const beratInput = document.getElementById('beratInput');

// Buffer untuk kalkulasi rata-rata berat (Moving Average 5 data terakhir)
const weightBuffer = [];
const BUFFER_SIZE = 5;
let weightLocked = false;
let lockTimer = null;
let lockedWeight = null;

// Fungsi tambah data ke buffer rata-rata
function addToBuffer(val) {
    weightBuffer.push(val);
    if (weightBuffer.length > BUFFER_SIZE) {
        weightBuffer.shift();
    }
}

// Fungsi hitung rata-rata dari buffer
function getAverage() {
    if (weightBuffer.length === 0) return 0;
    const sum = weightBuffer.reduce((a, b) => a + b, 0);
    return sum / weightBuffer.length;
}

// Fungsi kunci berat dan mulai timer 30 detik
function lockWeight(weight) {
    weightLocked = true;
    lockedWeight = weight;
    beratInput.value = weight.toFixed(2);
    beratInput.style.backgroundColor = "#e0f2fe";
    beratInput.style.borderColor = "#0ea5e9";
    document.getElementById('scanInput').focus();
    statusText.innerHTML = "<span class='info'>Berat terkunci: " + weight.toFixed(2) + " Kg &mdash; Silakan scan barcode (30 detik)</span>";

    // Reset timer lama jika ada, mulai timer baru 30 detik
    if (lockTimer) clearTimeout(lockTimer);
    lockTimer = setTimeout(() => {
        resetWeightLock();
        statusText.innerHTML = "<span class='error'>Waktu scan habis (30 detik). Berat direset.</span>";
        setTimeout(() => { statusText.innerHTML = ""; }, 2500);
    }, 30000);
}

// Fungsi lepas kunci berat
function resetWeightLock() {
    weightLocked = false;
    lockedWeight = null;
    weightBuffer.length = 0;
    beratInput.value = "";
    beratInput.style.backgroundColor = "";
    beratInput.style.borderColor = "";
    if (lockTimer) { clearTimeout(lockTimer); lockTimer = null; }
    document.getElementById('scanInput').focus();
}

// Polling data berat secara real-time (setiap 250ms)
const POLL_INTERVAL_MS = 250;
setInterval(() => {
    // Jika berat sudah terkunci, tidak perlu polling lagi
    if (weightLocked) return;

    fetch('/vision/status?_t=' + Date.now())
        .then(r => r.json())
        .then(data => {
            if (data.ok && data.weight !== undefined) {
                const currentWeight = data.weight;

                // Tambahkan ke buffer rata-rata
                addToBuffer(currentWeight);
                const avgWeight = getAverage();

                // Tampilkan nilai rata-rata di UI (bukan raw value langsung)
                if (document.activeElement !== beratInput) {
                    beratInput.value = avgWeight.toFixed(2);
                }

                // Kunci berat jika rata-rata sudah stabil (buffer sudah penuh & berat >= 0.5 Kg)
                if (weightBuffer.length >= BUFFER_SIZE && avgWeight >= 0.5) {
                    const min = Math.min(...weightBuffer);
                    const max = Math.max(...weightBuffer);
                    // Kunci jika selisih max-min dalam buffer <= 0.2 Kg (stabil)
                    if ((max - min) <= 0.2) {
                        lockWeight(avgWeight);
                    }
                }
            }
        })
        .catch(() => {});
}, POLL_INTERVAL_MS);

// ==========================
// SCAN SYSTEM
// ==========================
let kodeProduk = '';

const input = document.getElementById('scanInput');
const stepText = document.getElementById('stepText');
const statusText = document.getElementById('statusText');

input.addEventListener('keypress', function(e){
    if(e.key === 'Enter'){
        e.preventDefault();

        kodeProduk = input.value.trim();
        // Gunakan berat terkunci atau nilai manual yang ada di kolom
        let berat = weightLocked ? lockedWeight : parseFloat(beratInput.value);

        if(!kodeProduk){
            statusText.innerHTML = "<span class='error'>Produk kosong</span>";
            return;
        }

        if(isNaN(berat)){
            statusText.innerHTML = "<span class='error'>Timbangan kosong / error</span>";
            return;
        }

        // Validasi berat: 1kg - 3kg
        let keterangan = "OK";
        if(berat < 1.0 || berat > 3.0) {
            keterangan = "NG";
        }

        kirimData(berat, keterangan);
    }
});

// ==========================
// KIRIM DATA
// ==========================
function kirimData(berat, keterangan){
    statusText.innerHTML = "<span class='info'>Mengirim...</span>";

    fetch('/timbang/proses', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({
            produk:kodeProduk,
            berat: berat,
            keterangan: keterangan
        })
    })
    .then(res => res.json())
    .then(res => {
        if(res.success){
            if(keterangan === 'OK') {
                statusText.innerHTML = "<span class='success'>Timbang OK (" + berat.toFixed(2) + " kg)</span>";
            } else {
                statusText.innerHTML = "<span class='error'>NG: Timbang " + berat.toFixed(2) + " kg (Toleransi 1.0 - 3.0 kg)</span>";
            }
        }else{
            statusText.innerHTML = "<span class='error'>Gagal Simpan</span>";
        }

        resetForm();
    })
    .catch(() => {
        statusText.innerHTML = "<span class='error'>Server error</span>";
    });
}

// ==========================
// RESET FORM SETELAH KIRIM DATA
// ==========================
function resetForm(){
    kodeProduk = '';
    input.value = '';
    resetWeightLock();

    // Tunda penghapusan teks status OK/NG agar operator sempat membacanya
    setTimeout(() => {
        statusText.innerHTML = "";
    }, 2000);
}

// ==========================
// EVENT TARE (ZEROING)
// ==========================
document.getElementById('tareBtn').addEventListener('click', function() {
    statusText.innerHTML = "<span class='info'>Menjalankan Tare (Zeroing)...</span>";
    resetWeightLock();

    fetch('/vision/tare', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            statusText.innerHTML = "<span class='success'>Timbangan berhasil di-Zero (Offset: " + Math.round(data.offset) + ")</span>";
            setTimeout(() => { statusText.innerHTML = ""; }, 2500);
        } else {
            statusText.innerHTML = "<span class='error'>Gagal Zeroing</span>";
        }
    })
    .catch(() => {
        statusText.innerHTML = "<span class='error'>Koneksi error</span>";
    });
});

</script>

</body>
</html>