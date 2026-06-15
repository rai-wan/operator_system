import serial
import time

print("=" * 50)
print("  DIAGNOSA KAMERA — ProTrack")
print("=" * 50)

PORT = '/dev/serial0'

# === TEST 1: Dengarkan data dari kamera saat boot ===
print("\n[TEST 1] Mendengarkan data dari kamera selama 5 detik...")
print("         (Cabut lalu colok kembali kabel HITAM/5V kamera untuk restart)")
try:
    s = serial.Serial(PORT, 38400, timeout=5)
    s.reset_input_buffer()
    data = s.read(100)
    if data:
        print(f"  -> DATA DITERIMA! ({len(data)} bytes): {data.hex()}")
        print(f"  -> ASCII: {data}")
    else:
        print("  -> Tidak ada data diterima dari kamera")
    s.close()
except Exception as e:
    print(f"  -> Error: {e}")

# === TEST 2: Kirim berbagai perintah reset VC0706 ===
print("\n[TEST 2] Mengirim perintah-perintah VC0706...")
commands = {
    "System Reset":    b'\x56\x00\x26\x00',
    "Get Version":     b'\x56\x00\x11\x00',
    "Stop Frame":      b'\x56\x00\x36\x01\x00',
    "Resume Frame":    b'\x56\x00\x36\x01\x03',
    "Get FBUF Len":    b'\x56\x00\x34\x01\x00',
}
try:
    s = serial.Serial(PORT, 38400, timeout=2)
    for name, cmd in commands.items():
        s.reset_input_buffer()
        s.write(cmd)
        time.sleep(0.5)
        resp = s.read(20)
        status = f"Respon: {resp.hex()}" if resp else "Tidak ada respon"
        print(f"  {name}: {status}")
    s.close()
except Exception as e:
    print(f"  -> Error: {e}")

# === TEST 3: Cek apakah ada data mentah masuk ===
print("\n[TEST 3] Mengirim byte acak dan mendengarkan echo...")
try:
    s = serial.Serial(PORT, 38400, timeout=2)
    s.reset_input_buffer()
    s.write(b'\xAA\xBB\xCC\xDD')
    time.sleep(0.5)
    resp = s.read(20)
    if resp:
        print(f"  -> Ada echo/respon: {resp.hex()}")
    else:
        print("  -> Tidak ada respon sama sekali")
    s.close()
except Exception as e:
    print(f"  -> Error: {e}")

# === TEST 4: Coba baud rate 9600 dengan perintah reset ===
print("\n[TEST 4] Mencoba baud rate 9600 (beberapa kamera default 9600)...")
try:
    s = serial.Serial(PORT, 9600, timeout=2)
    s.reset_input_buffer()
    s.write(b'\x56\x00\x26\x00')
    time.sleep(1)
    resp = s.read(20)
    if resp:
        print(f"  -> Respon pada 9600 baud: {resp.hex()}")
    else:
        print("  -> Tidak ada respon pada 9600")
    s.close()
except Exception as e:
    print(f"  -> Error: {e}")

# === TEST 5: Cek GPIO UART config ===
print("\n[TEST 5] Mengecek konfigurasi UART di system...")
import subprocess
try:
    result = subprocess.run(['ls', '-la', '/dev/serial0'], capture_output=True, text=True)
    print(f"  serial0: {result.stdout.strip()}")
except:
    print("  -> /dev/serial0 tidak ditemukan")

try:
    result = subprocess.run(['cat', '/boot/firmware/config.txt'], capture_output=True, text=True)
    lines = [l for l in result.stdout.split('\n') if 'uart' in l.lower() or 'serial' in l.lower()]
    if lines:
        print(f"  config.txt UART settings:")
        for l in lines:
            print(f"    {l}")
    else:
        print("  -> Tidak ada setting UART di config.txt")
except:
    try:
        result = subprocess.run(['cat', '/boot/config.txt'], capture_output=True, text=True)
        lines = [l for l in result.stdout.split('\n') if 'uart' in l.lower() or 'serial' in l.lower()]
        if lines:
            print(f"  config.txt UART settings:")
            for l in lines:
                print(f"    {l}")
        else:
            print("  -> Tidak ada setting UART di config.txt")
    except:
        print("  -> Tidak bisa membaca config.txt")

print("\n" + "=" * 50)
print("DIAGNOSA SELESAI")
print("=" * 50)
print("\nPertanyaan penting:")
print("1. Apakah ada lampu LED merah kecil yang menyala di board kamera?")
print("2. Apa tulisan/label yang tercetak di PCB kamera? (contoh: VC0706, OV7670, dll)")
print("=" * 50)
