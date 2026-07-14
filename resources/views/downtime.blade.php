<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Target & Downtime — ProTrack</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>
<style>

* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Inter', sans-serif;
    background: #f0f4f8;
    color: #1e293b;
    min-height: 100vh;
}

/* ===== HEADER ===== */
.header {
    background: white;
    padding: 16px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 50;
}
.header-left { display: flex; align-items: center; gap: 14px; }
.back-btn {
    text-decoration: none;
    background: #f1f5f9;
    color: #475569;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    transition: background 0.2s;
}
.back-btn:hover { background: #e2e8f0; }
.page-title { font-size: 20px; font-weight: 800; color: #0f172a; }
.page-badge {
    background: #e0f2fe;
    color: #0284c7;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ===== FILTER ===== */
.filter-area { display: flex; align-items: center; gap: 12px; }
.filter-area select {
    padding: 8px 14px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
}
.filter-area select:focus { border-color: #0ea5e9; }
.refresh-btn {
    background: #0ea5e9;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}
.refresh-btn:hover { background: #0284c7; }
.refresh-btn.loading { opacity: 0.7; pointer-events: none; }

/* ===== MAIN CONTENT ===== */
.main { padding: 28px 32px; max-width: 1300px; margin: 0 auto; }

/* ===== ERROR BANNER ===== */
.error-banner {
    background: #fef2f2;
    border: 1.5px solid #fca5a5;
    border-radius: 14px;
    padding: 18px 24px;
    margin-bottom: 24px;
    display: none;
    align-items: flex-start;
    gap: 14px;
}
.error-banner.visible { display: flex; }
.error-icon { font-size: 24px; flex-shrink: 0; margin-top: 2px; }
.error-title { font-weight: 700; color: #b91c1c; font-size: 15px; margin-bottom: 4px; }
.error-desc { color: #991b1b; font-size: 13px; line-height: 1.5; }

/* ===== KPI CARDS ===== */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}
.kpi-card {
    background: white;
    border-radius: 18px;
    padding: 22px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); }
.kpi-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; justify-content: center; align-items: center;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}
.ic-blue   { background: #eff6ff; color: #2563eb; }
.ic-green  { background: #f0fdf4; color: #16a34a; }
.ic-amber  { background: #fffbeb; color: #d97706; }
.ic-purple { background: #faf5ff; color: #7c3aed; }

.kpi-label {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.6px;
    color: #94a3b8; margin-bottom: 4px;
}
.kpi-value {
    font-size: 28px; font-weight: 800;
    color: #0f172a; letter-spacing: -1px;
    line-height: 1;
}
.kpi-sub { font-size: 11px; color: #94a3b8; margin-top: 4px; }

/* ===== CHART CARD ===== */
.chart-card {
    background: white;
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
    margin-bottom: 24px;
}
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.chart-title { font-size: 17px; font-weight: 700; color: #0f172a; }
.chart-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
.chart-legend {
    display: flex;
    gap: 16px;
    align-items: center;
}
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; }
.legend-dot { width: 10px; height: 10px; border-radius: 3px; }
.dot-green  { background: #10b981; }
.dot-red    { background: #f87171; }
.dot-gray   { background: #cbd5e1; }

.chart-wrap { position: relative; margin-top: 20px; }
.chart-loading {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: rgba(255,255,255,0.9);
    border-radius: 10px;
    gap: 10px;
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}
.spinner {
    width: 32px; height: 32px;
    border: 3px solid #e2e8f0;
    border-top-color: #0ea5e9;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.last-update {
    text-align: right;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 8px;
}

/* ===== MODAL ===== */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,0.55);
    backdrop-filter: blur(6px);
    display: none;
    justify-content: center; align-items: center;
    z-index: 999;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: white;
    width: 460px;
    max-width: calc(100vw - 40px);
    padding: 32px;
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2);
    animation: slideUp 0.25s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
.modal-box h3 { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
.modal-box .modal-sub { color: #64748b; font-size: 14px; margin-bottom: 22px; }
.modal-box .modal-sub strong { color: #0ea5e9; font-weight: 700; }

.rekomendasi {
    background: #fff1f2;
    border: 1.5px dashed #fca5a5;
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: none;
}
.rekomendasi.show { display: block; }
.rekomendasi-title { font-size: 12px; font-weight: 700; color: #be123c; text-transform: uppercase; margin-bottom: 6px; }
.rekomendasi-row { display: flex; justify-content: space-between; font-size: 13px; color: #9f1239; }
.rekomendasi-row strong { font-weight: 700; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 7px; }
.form-group select,
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 11px 14px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    font-size: 14px;
    color: #1e293b;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}
.form-group select:focus,
.form-group input:focus,
.form-group textarea:focus { border-color: #ef4444; }

.modal-alert {
    padding: 11px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 14px;
    display: none;
}
.modal-alert.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; display: block; }
.modal-alert.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; display: block; }

.modal-footer { display: flex; gap: 10px; margin-top: 20px; }
.btn-batal {
    flex: 1; padding: 13px;
    background: #f1f5f9; color: #475569;
    border: none; border-radius: 10px;
    font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
    font-family: inherit;
}
.btn-batal:hover { background: #e2e8f0; }
.btn-simpan {
    flex: 2; padding: 13px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none; border-radius: 10px;
    font-size: 14px; font-weight: 700;
    cursor: pointer; transition: opacity 0.2s;
    font-family: inherit;
}
.btn-simpan:hover { opacity: 0.9; }
.btn-simpan:disabled { opacity: 0.6; pointer-events: none; }

/* Auto-refresh badge */
.auto-badge {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pulse {
    width: 7px; height: 7px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.7); }
}

@media (max-width: 900px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .main { padding: 20px 16px; }
    .header { padding: 14px 16px; }
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <a href="/select-station" class="back-btn">← Kembali</a>
        <div class="page-title">Target &amp; Downtime</div>
        <span class="page-badge">Live</span>
    </div>
    <div class="filter-area">
        <div class="auto-badge">
            <div class="pulse"></div>
            Auto refresh 60s
        </div>
        <select id="stationSelect">
            <option value="insert"  {{ session('station') == 'insert'  ? 'selected' : '' }}>Station Insert</option>
            <option value="timbang" {{ session('station') == 'timbang' ? 'selected' : '' }}>Station Timbang</option>
            <option value="packing" {{ session('station') == 'packing' ? 'selected' : '' }}>Station Packing</option>
        </select>
        <select id="shiftSelect">
            <option value="pagi">Shift Pagi (06–14)</option>
            <option value="siang">Shift Siang (15–22)</option>
            <option value="malam">Shift Malam (23–05)</option>
        </select>
        <button class="refresh-btn" id="refreshBtn" onclick="loadData()">
            <span id="refreshIcon">↻</span> Refresh
        </button>
    </div>
</div>

<!-- MAIN -->
<div class="main">

    <!-- ERROR BANNER -->
    <div class="error-banner" id="errorBanner">
        <div class="error-icon">!</div>
        <div>
            <div class="error-title">Tidak Dapat Mengambil Data dari Server Admin</div>
            <div class="error-desc" id="errorDesc">
                Pastikan server admin berjalan di <code>http://127.0.0.1:8000</code> dan
                endpoint <code>/api/downtime/chart</code> dapat diakses.
            </div>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon ic-blue">TGT</div>
            <div>
                <div class="kpi-label">Pencapaian Target</div>
                <div class="kpi-value" id="kpiTarget">—</div>
                <div class="kpi-sub" id="kpiTargetSub">Menghitung...</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon ic-green">EFF</div>
            <div>
                <div class="kpi-label">Efisiensi Waktu</div>
                <div class="kpi-value" id="kpiEff">—</div>
                <div class="kpi-sub" id="kpiEffSub">Menghitung...</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon ic-amber">OUT</div>
            <div>
                <div class="kpi-label">Output Aktual</div>
                <div class="kpi-value" id="kpiOutput">—</div>
                <div class="kpi-sub" id="kpiOutputSub">Dari target <span id="kpiTargetNominal">—</span></div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon ic-purple">DT</div>
            <div>
                <div class="kpi-label">Total Downtime</div>
                <div class="kpi-value" id="kpiDowntime">—</div>
                <div class="kpi-sub">Menit hilang hari ini</div>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Output Produksi per Jam</div>
                <div class="chart-subtitle">
                    Klik batang grafik untuk melaporkan downtime pada jam tersebut.
                    Target: <strong>100 unit/jam</strong>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-dot dot-green"></div> Mencapai target
                </div>
                <div class="legend-item">
                    <div class="legend-dot dot-red"></div> Di bawah target
                </div>
                <div class="legend-item">
                    <div class="legend-dot dot-gray"></div> Belum berjalan
                </div>
            </div>
        </div>

        <div class="chart-wrap">
            <canvas id="produksiChart" height="75"></canvas>
            <div class="chart-loading" id="chartLoading">
                <div class="spinner"></div>
                Mengambil data dari server admin...
            </div>
        </div>
        <div class="last-update" id="lastUpdate"></div>
    </div>

</div>

<!-- MODAL DOWNTIME -->
<div class="modal-overlay" id="downtimeModal">
    <div class="modal-box">
        <h3>Laporan Downtime</h3>
        <p class="modal-sub">
            Melaporkan kehilangan waktu untuk jam
            <strong id="lblJam">--:00</strong>
            — Station: <strong id="lblStation">—</strong>
        </p>

        <div class="modal-alert" id="modalAlert"></div>

        <div class="rekomendasi" id="rekomenBox">
            <div class="rekomendasi-title">Jam ini di bawah target</div>
            <div class="rekomendasi-row">
                <span>Output jam ini:</span>
                <strong><span id="lblOutput">0</span> / 100 unit</strong>
            </div>
            <div class="rekomendasi-row" style="margin-top:4px;">
                <span>Rekomendasi downtime:</span>
                <strong>± <span id="lblSaran">0</span> menit</strong>
            </div>
        </div>

        <form id="downtimeForm">
            <input type="hidden" id="hidJam">

            <div class="form-group">
                <label>Alasan Kendala *</label>
                <select id="inputAlasan" required>
                    <option value="" disabled selected>— Pilih alasan —</option>
                    <option value="Mesin Rusak">Mesin Rusak</option>
                    <option value="Kekurangan Material">Kekurangan Material</option>
                    <option value="Ganti Model">Set Up / Ganti Model</option>
                    <option value="Istirahat / Sholat">Istirahat / Sholat</option>
                    <option value="QC Check">QC Check</option>
                    <option value="Listrik Mati">Listrik / Utilitas</option>
                    <option value="Lainnya">Lainnya...</option>
                </select>
            </div>

            <div class="form-group">
                <label>Durasi Kendala (Menit) *</label>
                <input type="number" id="inputDurasi" min="1" max="60" placeholder="Masukkan durasi dalam menit" required>
            </div>

            <div class="form-group">
                <label>Keterangan Tambahan</label>
                <textarea id="inputKomentar" rows="2" placeholder="Deskripsi singkat (opsional)"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-batal" onclick="tutupModal()">Batal</button>
                <button type="submit" class="btn-simpan" id="btnSimpan">Simpan Laporan</button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================================
// CONSTANTS
// ============================================================
const TARGET_PER_JAM = 100;
let chartInstance   = null;
let lastLabels      = [];
let lastData        = [];
let refreshTimer    = null;

// ============================================================
// EVENT LISTENERS
// ============================================================
document.getElementById('stationSelect').addEventListener('change', loadData);
document.getElementById('shiftSelect').addEventListener('change', loadData);

// ============================================================
// MAIN: AMBIL DATA DARI ADMIN VIA PROXY OPERATOR
// ============================================================
function loadData() {
    const station = document.getElementById('stationSelect').value;
    const shift   = document.getElementById('shiftSelect').value;

    // Tampilkan loading
    setRefreshLoading(true);
    document.getElementById('chartLoading').style.display = 'flex';
    document.getElementById('errorBanner').classList.remove('visible');

    // Reset KPI
    ['kpiTarget','kpiEff','kpiOutput','kpiDowntime'].forEach(id => {
        document.getElementById(id).textContent = '...';
    });

    fetch(`/downtime/chart?station=${station}&shift=${shift}`)
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        })
        .then(json => {
            if (json.error) {
                throw json;
            }

            lastLabels = json.labels  || [];
            lastData   = json.data    || [];

            updateKPI(json);
            renderChart(lastLabels, lastData, json.elapsed_hours || 0);

            // Update last update time
            const now = new Date();
            document.getElementById('lastUpdate').textContent =
                `Terakhir diperbarui: ${now.toLocaleTimeString('id-ID')} — Station: ${station} | Shift: ${shift}`;
        })
        .catch(errObj => {
            const msg = errObj.message || 'Tidak dapat terhubung ke server admin.';
            showError(msg);
            // Tampilkan chart kosong agar UI tidak patah
            const emptyLabels = getDefaultLabels(document.getElementById('shiftSelect').value);
            renderChart(emptyLabels, emptyLabels.map(() => 0), 0);
        })
        .finally(() => {
            setRefreshLoading(false);
            document.getElementById('chartLoading').style.display = 'none';
        });
}

// ============================================================
// UPDATE KPI CARDS
// ============================================================
function updateKPI(json) {
    // Target %
    const targetPct = json.output_percent ?? 0;
    const kpiTargetEl = document.getElementById('kpiTarget');
    kpiTargetEl.textContent = targetPct + '%';
    kpiTargetEl.style.color = targetPct >= 100 ? '#16a34a' : targetPct >= 70 ? '#d97706' : '#dc2626';

    const elapsed = json.elapsed_hours ?? 0;
    document.getElementById('kpiTargetSub').textContent =
        elapsed > 0 ? `Dari ${elapsed} jam shift berjalan` : 'Shift belum dimulai';

    // Efisiensi %
    const effPct = json.efficiency ?? 0;
    const kpiEffEl = document.getElementById('kpiEff');
    kpiEffEl.textContent = effPct + '%';
    kpiEffEl.style.color = effPct >= 90 ? '#16a34a' : effPct >= 70 ? '#d97706' : '#dc2626';
    document.getElementById('kpiEffSub').textContent =
        `Downtime: ${json.downtime ?? 0} menit`;

    // Output
    document.getElementById('kpiOutput').textContent   = json.actual_output ?? 0;
    document.getElementById('kpiTargetNominal').textContent = json.target_output ?? 0;

    // Downtime
    document.getElementById('kpiDowntime').textContent = (json.downtime ?? 0) + ' mnt';
}

// ============================================================
// RENDER CHART
// ============================================================
function renderChart(labels, dataArr, elapsedCount) {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    const bgColors = dataArr.map((v, i) => {
        if (i >= elapsedCount) return '#e2e8f0'; // belum berjalan - abu
        return v >= TARGET_PER_JAM ? '#10b981' : '#f87171';
    });

    const borderColors = bgColors.map(c =>
        c === '#10b981' ? '#059669' : c === '#f87171' ? '#ef4444' : '#cbd5e1'
    );

    const ctx = document.getElementById('produksiChart').getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.map(l => l + ':00'),
            datasets: [{
                label: 'Output / Jam',
                data: dataArr,
                backgroundColor: bgColors,
                borderColor: borderColors,
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            animation: { duration: 500 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) => 'Jam ' + items[0].label,
                        label: (item) => {
                            const val = item.raw;
                            const diff = val - TARGET_PER_JAM;
                            const status = val >= TARGET_PER_JAM
                                ? `Mencapai target (+${diff})`
                                : `Di bawah target (${diff})`;
                            return [`Output: ${val} unit`, status];
                        }
                    }
                },
                annotation: {}
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 130,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 12 }, color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 }, color: '#64748b' }
                }
            },
            onClick: (e, elements) => {
                if (elements.length > 0) {
                    const idx = elements[0].index;
                    bukaModal(labels[idx], dataArr[idx]);
                }
            }
        }
    });

    // Draw target line (100)
    Chart.register({
        id: 'targetLine',
        afterDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea) return;
            const y = scales.y.getPixelForValue(TARGET_PER_JAM);
            ctx.save();
            ctx.setLineDash([6, 4]);
            ctx.strokeStyle = '#f59e0b';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(chartArea.left, y);
            ctx.lineTo(chartArea.right, y);
            ctx.stroke();
            ctx.fillStyle = '#f59e0b';
            ctx.font = '11px Inter, sans-serif';
            ctx.fillText('Target 100', chartArea.right - 60, y - 6);
            ctx.restore();
        }
    });
}

// ============================================================
// MODAL DOWNTIME
// ============================================================
function bukaModal(jam, outputVal) {
    const station = document.getElementById('stationSelect').value;
    document.getElementById('lblJam').textContent     = jam + ':00';
    document.getElementById('lblStation').textContent = station.toUpperCase();
    document.getElementById('hidJam').value           = jam;
    document.getElementById('inputAlasan').value      = '';
    document.getElementById('inputKomentar').value    = '';
    document.getElementById('modalAlert').className   = 'modal-alert';
    document.getElementById('modalAlert').textContent = '';

    // Rekomendasi
    const rekomenBox = document.getElementById('rekomenBox');
    if (outputVal < TARGET_PER_JAM) {
        const missing = TARGET_PER_JAM - outputVal;
        const saran   = Math.round((missing / TARGET_PER_JAM) * 60);
        document.getElementById('lblOutput').textContent = outputVal;
        document.getElementById('lblSaran').textContent  = saran;
        document.getElementById('inputDurasi').value     = saran;
        rekomenBox.classList.add('show');
    } else {
        rekomenBox.classList.remove('show');
        document.getElementById('inputDurasi').value = '';
    }

    document.getElementById('downtimeModal').classList.add('open');
}

function tutupModal() {
    document.getElementById('downtimeModal').classList.remove('open');
}

// Klik luar modal = tutup
document.getElementById('downtimeModal').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});

// ============================================================
// SUBMIT DOWNTIME
// ============================================================
document.getElementById('downtimeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const payload = {
        station  : document.getElementById('stationSelect').value,
        jam      : document.getElementById('hidJam').value,
        alasan   : document.getElementById('inputAlasan').value,
        durasi   : document.getElementById('inputDurasi').value,
        komentar : document.getElementById('inputKomentar').value,
        _token   : '{{ csrf_token() }}'
    };

    fetch('/downtime/store', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': payload._token },
        body    : JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success || res.message?.toLowerCase().includes('tersimpan')) {
            showModalAlert('Laporan downtime berhasil disimpan!', 'success');
            setTimeout(() => {
                tutupModal();
                loadData(); // refresh chart + KPI
            }, 1200);
        } else {
            showModalAlert(res.message || 'Gagal menyimpan laporan.', 'error');
        }
    })
    .catch(() => {
        showModalAlert('Tidak dapat terhubung ke server. Coba lagi.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Simpan Laporan';
    });
});

// ============================================================
// HELPERS
// ============================================================
function showError(msg) {
    document.getElementById('errorBanner').classList.add('visible');
    document.getElementById('errorDesc').textContent = msg;
}

function showModalAlert(msg, type) {
    const el = document.getElementById('modalAlert');
    el.textContent = msg;
    el.className = 'modal-alert ' + type;
}

function setRefreshLoading(on) {
    const btn  = document.getElementById('refreshBtn');
    const icon = document.getElementById('refreshIcon');
    btn.classList.toggle('loading', on);
    icon.textContent = on ? '...' : '↻';
}

function getDefaultLabels(shift) {
    if (shift === 'pagi')  return ['06','07','08','09','10','11','12','13','14'];
    if (shift === 'siang') return ['15','16','17','18','19','20','21','22'];
    return ['23','00','01','02','03','04','05'];
}

// ============================================================
// AUTO-REFRESH SETIAP 60 DETIK
// ============================================================
function startAutoRefresh() {
    clearInterval(refreshTimer);
    refreshTimer = setInterval(() => {
        loadData();
    }, 60000);
}

// Shortcut F2 → downtime page (sudah di sini)
document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupModal(); });

// ============================================================
// INIT — wait for Chart.js to be ready
// ============================================================
function initWhenReady() {
    if (typeof Chart !== 'undefined') {
        loadData();
        startAutoRefresh();
    } else {
        setTimeout(initWhenReady, 50);
    }
}
initWhenReady();
</script>

<!-- Chart.js lokal — tidak membutuhkan internet -->
<script src="/js/chart.umd.min.js"></script>

</body>
</html>