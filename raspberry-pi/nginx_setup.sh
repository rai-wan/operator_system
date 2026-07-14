#!/bin/bash
# ========================================================
# Nginx Reverse Proxy Setup Script for Raspberry Pi
# ========================================================

echo "=============================================="
echo "  ProTrack — Mengonfigurasi Nginx Web Server..."
echo "=============================================="

# 1. Install Nginx jika belum ada
echo "[1/4] Menginstall Nginx..."
sudo apt update
sudo apt install -y nginx

# 2. Buat file konfigurasi virtual host untuk productionweb.com
echo "[2/4] Membuat konfigurasi productionweb.com..."
sudo tee /etc/nginx/sites-available/productionweb.com > /dev/null <<EOF
server {
    listen 80;
    server_name productionweb.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

# 3. Buat file konfigurasi virtual host untuk trackweb.com
echo "[3/4] Membuat konfigurasi trackweb.com..."
sudo tee /etc/nginx/sites-available/trackweb.com > /dev/null <<EOF
server {
    listen 80;
    server_name trackweb.com;

    location / {
        proxy_pass http://127.0.0.1:8001;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

# 4. Aktifkan konfigurasi dengan membuat symlink dan restart Nginx
echo "[4/4] Mengaktifkan konfigurasi dan me-restart Nginx..."
sudo ln -sf /etc/nginx/sites-available/productionweb.com /etc/nginx/sites-enabled/
sudo ln -sf /etc/nginx/sites-available/trackweb.com /etc/nginx/sites-enabled/

# Hapus konfigurasi default Nginx agar tidak tabrakan
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx dan Restart
sudo nginx -t
if [ $? -eq 0 ]; then
    sudo systemctl restart nginx
    echo "=============================================="
    echo "  Konfigurasi Nginx Sukses! Nginx telah di-restart."
    echo "=============================================="
else
    echo "Error: Konfigurasi Nginx salah!"
fi
