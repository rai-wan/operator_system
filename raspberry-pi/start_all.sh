#!/bin/bash
# ==========================================================
# start_all.sh – Menjalankan semua layanan secara dinamis (FIXED)
# ==========================================================

# 1. Hentikan Apache (port 80)
echo "[1] Menghentikan Apache..."
sudo systemctl stop apache2
sudo systemctl disable apache2 2>/dev/null

# 2. Restart Nginx
echo "[2] Merestart Nginx..."
sudo systemctl restart nginx

# 3. Jalankan Operator System (port 8000)
echo "[3] Menjalankan Operator System (port 8000)..."
cd /home/webbased/protrack/operator-system || { echo "Folder operator-system tidak ada!"; exit 1; }
composer install --no-interaction --prefer-dist
php artisan config:cache
php artisan route:cache
php artisan view:cache
nohup php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/operator.log 2>&1 &

# 4. Jalankan Web-Based (port 8001)
echo "[4] Menjalankan Web-Based (port 8001)..."
cd /home/webbased/protrack/web-based || { echo "Folder web-based tidak ada!"; exit 1; }
composer install --no-interaction --prefer-dist
php artisan config:cache
php artisan route:cache
php artisan view:cache
nohup php artisan serve --host=0.0.0.0 --port=8001 > storage/logs/webbased.log 2>&1 &

# 5. Jalankan Server Webcam (Port 5000)
echo "[5] Menjalankan server webcam..."
cd /home/webbased/protrack/operator-system/raspberry-pi || { echo "Folder webcam tidak ada!"; exit 1; }
nohup bash start.sh > pi_vision_server.log 2>&1 &

# 6. Deteksi IP aktif secara dinamis untuk cetak informasi
IP_ACTIVE=$(hostname -I | awk '{print $1}')
if [ -z "$IP_ACTIVE" ]; then
    IP_ACTIVE="10.42.0.1" # Fallback ke IP Hotspot default
fi

# ==========================================================
echo "=========================================================="
echo "✅ Semua layanan telah berhasil dijalankan kembali!"
echo "   • Operator  : http://productionweb.com  (atau http://${IP_ACTIVE}:8000)"
echo "   • Admin     : http://trackweb.com       (atau http://${IP_ACTIVE}:8001)"
echo "   • Webcam    : http://${IP_ACTIVE}:5000"
echo "=========================================================="
