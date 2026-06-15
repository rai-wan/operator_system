#!/bin/bash
# ================================================
# ProTrack Vision Server — Auto Start Script
# Jalankan ini di Raspberry Pi untuk start server
# ================================================

echo "=============================================="
echo "  ProTrack Vision Server — Starting..."
echo "=============================================="

# Pindah ke direktori script
cd "$(dirname "$0")"

# Cek apakah Python3 tersedia
if ! command -v python3 &> /dev/null; then
    echo "[ERROR] Python3 tidak ditemukan!"
    exit 1
fi

# Buat virtual environment jika belum ada
if [ ! -d "venv" ]; then
    echo "[SETUP] Membuat virtual environment (venv)..."
    python3 -m venv venv
    if [ $? -ne 0 ]; then
        echo "[ERROR] Gagal membuat virtual environment!"
        exit 1
    fi
fi

# Aktifkan virtual environment
source venv/bin/activate

# Install / update dependencies
echo "[SETUP] Mengecek/menginstall dependencies di venv..."
pip3 install -r requirements.txt --quiet
if [ $? -ne 0 ]; then
    echo "[ERROR] Gagal menginstall dependencies!"
    exit 1
fi

# Tampilkan IP address Pi
echo ""
echo "[INFO] IP Address Raspberry Pi:"
hostname -I | tr ' ' '\n' | grep -v '^$' | while read ip; do
    echo "  -> http://$ip:5000"
done
echo ""

# Jalankan server
echo "[SERVER] Menjalankan pi_vision_server.py..."
python3 pi_vision_server.py
