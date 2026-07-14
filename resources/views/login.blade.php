<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ProTrack Operator Login</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', system-ui, -apple-system, sans-serif; }
body {
    background-color: #f4f7f8;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #334155;
    overflow: hidden;
    position: relative;
}

/* Background floating shapes */
.bg-shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    z-index: -1;
    animation: float 12s infinite ease-in-out alternate;
}
.shape-1 { 
    width: 500px; height: 500px; 
    background: rgba(0, 205, 174, 0.15); 
    top: -150px; left: -100px; 
}
.shape-2 { 
    width: 600px; height: 600px; 
    background: rgba(56, 189, 248, 0.1); 
    bottom: -200px; right: -200px; 
    animation-delay: -5s;
    animation-duration: 15s;
}

@keyframes float {
    0% { transform: translateY(0) scale(1) rotate(0deg); }
    100% { transform: translateY(40px) scale(1.1) rotate(5deg); }
}

@keyframes slideUpFade {
    0% { opacity: 0; transform: translateY(40px); }
    100% { opacity: 1; transform: translateY(0); }
}

.login-container {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    padding: 50px 45px;
    border-radius: 28px;
    width: 440px;
    box-shadow: 0 25px 50px -12px rgba(0, 205, 174, 0.15), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255,255,255,0.6);
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin-bottom: 10px;
}
.logo-icon {
    width: 56px;
    height: 56px;
    background: #00cdae; /* ProTrack Teal */
    border-radius: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    box-shadow: 0 10px 20px -5px rgba(0, 205, 174, 0.4);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.logo-container:hover .logo-icon {
    transform: rotate(10deg) scale(1.05);
}
.logo-icon svg {
    width: 32px;
    height: 32px;
}
.logo-text {
    font-size: 34px;
    font-weight: 800;
    color: #00cdae;
    letter-spacing: -0.5px;
}

.subtitle {
    text-align: center;
    color: #64748b;
    margin-bottom: 35px;
    font-size: 15px;
    font-weight: 500;
}

.input-group {
    margin-bottom: 25px;
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}
.input-group:nth-child(2) { animation-delay: 0.1s; }
.input-group:nth-child(3) { animation-delay: 0.2s; }
.input-group:nth-child(4) { animation-delay: 0.3s; }

.input-group label {
    display: block;
    margin-bottom: 10px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
}

.input-field {
    width: 100%;
    padding: 16px 20px;
    border-radius: 14px;
    border: 2px solid #e2e8f0;
    background: #ffffff;
    color: #1e293b;
    font-size: 16px;
    font-weight: 500;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.02);
}

select.input-field {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1.2rem center;
    background-size: 1.2em;
}

.input-field:focus {
    border-color: #00cdae;
    box-shadow: 0 0 0 4px rgba(0, 205, 174, 0.15), inset 0 2px 4px 0 rgba(0,0,0,0.02);
    transform: translateY(-2px);
}

.btn-login {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #00cdae, #00aa90);
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 700;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: 15px;
    box-shadow: 0 10px 20px -5px rgba(0, 205, 174, 0.4);
    opacity: 0;
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: 0.4s;
    position: relative;
    overflow: hidden;
}

.btn-login::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300%;
    height: 300%;
    background: rgba(255,255,255,0.2);
    transform: translate(-50%, -50%) rotate(45deg) translateY(-100%);
    transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px -8px rgba(0, 205, 174, 0.6);
}

.btn-login:hover::after {
    transform: translate(-50%, -50%) rotate(45deg) translateY(100%);
}

.error-msg {
    color: #ef4444;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 25px;
    background: rgba(239, 68, 68, 0.1);
    padding: 12px 16px;
    border-radius: 10px;
    border-left: 4px solid #ef4444;
    animation: slideUpFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
</head>
<body>

<div class="bg-shape shape-1"></div>
<div class="bg-shape shape-2"></div>

<div class="login-container">
    <div class="logo-container">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
        </div>
        <div class="logo-text">ProTrack</div>
    </div>
    
    <div class="subtitle">Operator Production Terminal</div>

    @if ($errors->any())
        <div class="error-msg">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="/login" method="POST">
        @csrf
        
        <div class="input-group">
            <label>ID Karyawan</label>
            <input type="text" name="id_karyawan" class="input-field" placeholder="Scan atau ketik ID" required autofocus autocomplete="off">
        </div>

        <div class="input-group">
            <label>Station (Mesin)</label> 
            <select name="station" class="input-field" required>
                <option value="" disabled selected>-- Pilih Station --</option>
                <option value="insert">Station Insert</option>
                <option value="timbang">Station Timbang</option>
                <option value="packing">Station Packing</option>
            </select>
        </div>

        <button type="submit" class="btn-login">Mulai Seesi</button>
    </form>
</div>

</body>
</html>
