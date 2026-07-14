@echo off
:: Memeriksa hak Administrator
openfiles >nul 2>&1
if %errorlevel% neq 0 (
    echo =======================================================
    echo  [ERROR] Harap jalankan file ini sebagai Administrator!
    echo =======================================================
    echo  Caranya: 
    echo  Klik kanan file ini dan pilih 'Run as administrator'.
    echo =======================================================
    pause
    exit /b
)

echo =======================================================
echo   PROTRACK - DETEKSI IP PI DAN UPDATE HOSTS OTOMATIS
echo =======================================================
echo   Sedang mencari Raspberry Pi di jaringan Anda...

powershell -NoProfile -ExecutionPolicy Bypass -Command " ^
    try { ^
        $ip = [System.Net.Dns]::GetHostAddresses('webbased.local') | Select-Object -ExpandProperty IPAddressToString | Select-Object -First 1; ^
        if (-not $ip) { throw 'Pi tidak terdeteksi'; } ^
        Write-Host '[1/3] Raspberry Pi ditemukan di IP:' $ip -ForegroundColor Green; ^
        $hostsPath = 'C:\Windows\System32\drivers\etc\hosts'; ^
        Write-Host '[2/3] Memperbarui file hosts...' -ForegroundColor Yellow; ^
        $content = Get-Content $hostsPath; ^
        $cleaned = $content | Where-Object { $_ -notmatch 'productionweb.com' -and $_ -notmatch 'trackweb.com' }; ^
        $newContent = $cleaned + \"`n$ip productionweb.com`n$ip trackweb.com\"; ^
        $newContent | Set-Content $hostsPath; ^
        Write-Host '[3/3] Membersihkan cache DNS...' -ForegroundColor Yellow; ^
        ipconfig /flushdns | Out-Null; ^
        Write-Host '=======================================================' -ForegroundColor Green; ^
        Write-Host '[SUKSES] Konfigurasi selesai!' -ForegroundColor Green; ^
        Write-Host 'Sekarang Anda bisa membuka:' -ForegroundColor Green; ^
        Write-Host ' -> http://productionweb.com/login' -ForegroundColor Cyan; ^
        Write-Host ' -> http://trackweb.com/dashboard' -ForegroundColor Cyan; ^
        Write-Host '=======================================================' -ForegroundColor Green; ^
    } catch { ^
        Write-Host '=======================================================' -ForegroundColor Red; ^
        Write-Host '[ERROR] Raspberry Pi (webbased.local) tidak terdeteksi!' -ForegroundColor Red; ^
        Write-Host 'Pastikan Raspberry Pi sudah menyala dan terhubung' -ForegroundColor Yellow; ^
        Write-Host 'ke WiFi / Hotspot yang sama dengan laptop ini.' -ForegroundColor Yellow; ^
        Write-Host '=======================================================' -ForegroundColor Red; ^
    } ^
"

pause
