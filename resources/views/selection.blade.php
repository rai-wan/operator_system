<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pilih Station - Smart Factory</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }
body {
    background:linear-gradient(135deg, #eef2f7, #e2e8f0);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: #1f2937;
}

/* NAVBAR */
.navbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 40px;
    background:rgba(255,255,255,0.8);
    backdrop-filter:blur(15px);
    box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);
}
.navbar h2 {
    font-size:22px;
    font-weight:600;
}
.operator-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.badge-id {
    background: #0ea5e9;
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
}
.btn-logout {
    text-decoration: none;
    color: #ef4444;
    font-weight: 500;
    transition: 0.3s;
    font-size: 14px;
}
.btn-logout:hover { color: #dc2626; }

/* MAIN CONTENT */
.container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px;
}
.header-text {
    text-align: center;
    margin-bottom: 50px;
}
.header-text h1 {
    font-size: 32px;
    margin-bottom: 10px;
}
.header-text p {
    color: #64748b;
    font-size: 16px;
}

.cards {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    justify-content: center;
}
.card {
    background: white;
    width: 260px;
    height: 300px;
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    color: #1f2937;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
    transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: 2px solid transparent;
}
.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
    border-color: #0ea5e9;
}
.icon-box {
    width: 80px;
    height: 80px;
    background: #f1f5f9;
    border-radius: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 35px;
    margin-bottom: 20px;
    transition: 0.4s;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
.card:hover .icon-box {
    background: #0ea5e9;
    color: white;
    box-shadow: 0 10px 15px -3px rgba(14,165,233,0.3);
}
.card h3 {
    font-size: 24px;
    font-weight: 600;
}
</style>
</head>
<body>

<div class="navbar">
    <h2>Main Dashboard</h2>
    <div class="operator-info">
        <span class="badge-id">ID: {{ session('id_karyawan') }}</span>
        <a href="/logout" class="btn-logout">Logout</a>
    </div>
</div>

<div class="container">
    <div class="header-text">
        <h1>Pilih Station</h1>
        <p>Silakan pilih proses produksi yang akan Anda jalankan saat ini</p>
    </div>

    <div class="cards">
        <a href="/insert" class="card">
            <div class="icon-box">🔧</div>
            <h3>Insert</h3>
        </a>
        <a href="/timbang" class="card">
            <div class="icon-box">⚖️</div>
            <h3>Timbang</h3>
        </a>
        <a href="/packing" class="card">
            <div class="icon-box">📦</div>
            <h3>Packing</h3>
        </a>
    </div>
</div>

</body>
</html>
