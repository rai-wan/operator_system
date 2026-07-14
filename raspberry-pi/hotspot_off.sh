#!/bin/bash
# ==========================================================
# hotspot_off.sh – Menonaktifkan Hotspot WiFi di Raspberry Pi
# ==========================================================

echo "=============================================="
echo "  PROTRACK - Menonaktifkan Hotspot WiFi..."
echo "=============================================="

# 1. Matikan koneksi Hotspot
echo "[1/2] Mematikan koneksi Hotspot..."
sudo nmcli connection down Hotspot 2>/dev/null
sudo nmcli connection down id "Hotspot" 2>/dev/null

# 2. Hubungkan kembali ke WiFi client internet (misal: 'abc')
echo "[2/2] Menghubungkan kembali ke WiFi 'abc'..."
sudo nmcli connection up id "abc" 2>/dev/null

echo "=============================================="
echo "✅ Hotspot Dinonaktifkan."
echo "   Raspberry Pi akan terhubung kembali ke internet."
echo "=============================================="
