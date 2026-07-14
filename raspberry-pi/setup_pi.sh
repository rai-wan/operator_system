#!/bin/bash
# ========================================================
# ProTrack Web Server Setup Script for Raspberry Pi
# ========================================================

echo "=============================================="
echo "  ProTrack — Starting Server Installation..."
echo "=============================================="

# 1. Update system packages
echo "[1/6] Mengupdate list paket OS..."
sudo apt update -y

# 2. Install PHP, Extensions, dan MariaDB (MySQL)
echo "[2/6] Menginstall PHP, MySQL (MariaDB), dan unzip..."
sudo apt install -y php php-cli php-common php-mbstring php-xml php-bcmath php-curl php-zip php-sqlite3 php-mysql mariadb-server unzip

# 3. Install Composer secara Global
echo "[3/6] Menginstall Composer..."
if ! command -v composer &> /dev/null; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    echo "Composer berhasil diinstall!"
else
    echo "Composer sudah terinstall."
fi

# 4. Konfigurasi Database MariaDB (MySQL)
echo "[4/6] Mengaktifkan dan mengonfigurasi MariaDB..."
sudo systemctl start mariadb
sudo systemctl enable mariadb
# Membuat database web_based dan mengatur user root tanpa password (sesuai .env web-based)
sudo mysql -u root -e "CREATE DATABASE IF NOT EXISTS web_based;"
sudo mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY ''; FLUSH PRIVILEGES;"

# 5. Extract Project ke folder ProTrack
echo "[5/6] Mengekstrak file project..."
mkdir -p /home/webbased/protrack/operator-system
mkdir -p /home/webbased/protrack/web-based

tar -xzf /home/webbased/operator-system.tar.gz -C /home/webbased/protrack/operator-system
tar -xzf /home/webbased/web-based.tar.gz -C /home/webbased/protrack/web-based

# Hapus file tar.gz setelah di-extract untuk hemat ruang
rm -f /home/webbased/operator-system.tar.gz /home/webbased/web-based.tar.gz

# 6. Install Dependencies Laravel & Jalankan Migrasi
echo "[6/6] Menginstall dependencies Laravel dan Migrasi Database..."

echo "-> Memproses project: operator-system..."
cd /home/webbased/protrack/operator-system
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
# Buat database sqlite kosong jika belum ada
touch database/database.sqlite
php artisan migrate --force

echo "-> Memproses project: web-based..."
cd /home/webbased/protrack/web-based
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
php artisan migrate --force

echo "=============================================="
echo "  Instalasi Selesai! Web Server Siap Digunakan."
echo "=============================================="
echo "Untuk menguji jalankan stasiun operator:"
echo "  cd /home/webbased/protrack/operator-system"
echo "  php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "Untuk menguji jalankan dashboard monitoring:"
echo "  cd /home/webbased/protrack/web-based"
echo "  php artisan serve --host=0.0.0.0 --port=8001"
echo "=============================================="
