#!/bin/bash
# ==========================================================
# hotspot_on.sh – Mengaktifkan Hotspot WiFi di Raspberry Pi
# ==========================================================

echo "=============================================="
echo "  PROTRACK - Mengaktifkan Hotspot WiFi Pi..."
echo "=============================================="

# 1. Daftarkan domain lokal di /etc/hosts Pi jika belum ada
if ! grep -q "10.42.0.1 productionweb.com" /etc/hosts; then
    echo "[1/3] Menambahkan domain lokal ke /etc/hosts Pi..."
    echo -e "\n10.42.0.1 productionweb.com\n10.42.0.1 trackweb.com" | sudo tee -a /etc/hosts > /dev/null
else
    echo "[1/3] Domain lokal sudah terdaftar di /etc/hosts Pi."
fi

# 2. Hentikan koneksi WiFi client saat ini (supaya wlan0 bebas)
echo "[2/3] Membebaskan wlan0..."
sudo nmcli connection down id "abc" 2>/dev/null

# 3. Nyalakan Hotspot NetworkManager
echo "[3/3] Menyalakan Hotspot WiFi..."
sudo nmcli device wifi hotspot ssid ProTrack-WiFi password "protrack123" ifname wlan0

echo "=============================================="
echo "✅ Hotspot ProTrack-WiFi Aktif!"
echo "   • Nama WiFi (SSID) : ProTrack-WiFi"
echo "   • Password (WPA2)  : protrack123"
echo "   • IP Raspberry Pi  : 10.42.0.1"
echo "=============================================="
echo "Silakan hubungkan Laptop/HP Anda ke WiFi 'ProTrack-WiFi'."
echo "Setelah terhubung, Anda bisa langsung membuka:"
echo " -> http://productionweb.com/login"
echo " -> http://trackweb.com/dashboard"
echo "=============================================="
