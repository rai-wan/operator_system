import serial
import time

ports = ['/dev/serial0', '/dev/ttyAMA0', '/dev/ttyS0']
baudrates = [9600, 19200, 38400, 57600, 115200]

print("=== ProTrack Camera Baud Rate Scanner ===")
print("Memulai pemindaian port dan baud rate...")

for port in ports:
    for baud in baudrates:
        print(f"Mencoba {port} pada {baud} baud...", end="", flush=True)
        try:
            s = serial.Serial(port, baud, timeout=1.5)
            s.reset_input_buffer()
            # Kirim perintah reset VC0706 (0x56 0x00 0x26 0x00)
            s.write(b'\x56\x00\x26\x00')
            time.sleep(0.3)
            response = s.read(10)
            s.close()
            
            if response:
                print(f" -> BERHASIL! Respon: {response.hex()}")
                print(f"\n[SUKSES] Kamera terdeteksi pada port: {port} dengan Baud Rate: {baud}")
                exit(0)
            else:
                print(" -> Tidak ada respon")
        except Exception as e:
            print(f" -> Error: {e}")

print("\n[GAGAL] Kamera tidak merespon pada kombinasi port/baud rate apa pun.")
print("Saran perbaikan:")
print("1. Pastikan kabel TX (Kuning) ke Pin 10 dan RX (Putih) ke Pin 8 (atau sebaliknya jika terbalik).")
print("2. Pastikan kamera mendapat daya 5V yang cukup (lampu LED merah di belakang kamera menyala).")
