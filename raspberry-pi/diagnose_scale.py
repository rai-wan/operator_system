import time
import gc
import RPi.GPIO as GPIO  # type: ignore

# Gunakan nomor pin GPIO BCM
DT_PIN = 5   # GPIO 5 (Pin Fisik 29)
SCK_PIN = 6  # GPIO 6 (Pin Fisik 31)

def setup():
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(SCK_PIN, GPIO.OUT)
    GPIO.setup(DT_PIN, GPIO.IN)
    GPIO.output(SCK_PIN, False)

def read_raw():
    # Tunggu DT turun (siap)
    timeout = 200
    while GPIO.input(DT_PIN) == 1:
        time.sleep(0.001)
        timeout -= 1
        if timeout <= 0:
            return None # Timeout

    # --- CRITICAL SECTION ---
    gc_was_enabled = gc.isenabled()
    gc.disable()

    value = 0
    try:
        for _ in range(24):
            GPIO.output(SCK_PIN, True)
            value = value << 1
            GPIO.output(SCK_PIN, False)
            if GPIO.input(DT_PIN):
                value += 1

        # Pulsa ke-25
        GPIO.output(SCK_PIN, True)
        GPIO.output(SCK_PIN, False)
    finally:
        if gc_was_enabled:
            gc.enable()
    # --- END CRITICAL SECTION ---

    # Ubah ke komplemen 2 signed 24-bit
    if value & 0x800000:
        value -= 0x1000000

    return value

def main():
    setup()
    print("=== ProTrack Timbangan Diagnostics ===")
    print("Membaca 50 data mentah untuk menguji stabilitas...")
    print("Harap biarkan timbangan kosong saat pengujian ini dimulai.\n")
    
    readings = []
    timeouts = 0
    
    for i in range(50):
        val = read_raw()
        if val is not None:
            readings.append(val)
            print(f"Data #{i+1:02d}: {val}")
        else:
            timeouts += 1
            print(f"Data #{i+1:02d}: TIMEOUT (Tidak ada sinyal/kabel lepas)")
        time.sleep(0.1)
        
    GPIO.cleanup()
    
    print("\n=== Hasil Analisis Stabilitas ===")
    if timeouts > 10:
        print("❌ KONEKSI BURUK: Terlalu banyak Timeout. Periksa kabel DT/SCK atau daya!")
        return
        
    if not readings:
        print("❌ ERROR: Tidak ada data yang berhasil dibaca dari HX711.")
        return
        
    avg = sum(readings) / len(readings)
    min_val = min(readings)
    max_val = max(readings)
    diff = max_val - min_val
    
    print(f"Total Data Terbaca  : {len(readings)}/50")
    print(f"Nilai Rata-rata (Avg): {avg:.2f}")
    print(f"Nilai Minimum       : {min_val}")
    print(f"Nilai Maksimum      : {max_val}")
    print(f"Selisih (Max - Min) : {diff}")
    print("---------------------------------")
    
    if diff < 1500:
        print("✅ TIMBANGAN STABIL: Sinyal sangat bagus dan siap dikalibrasi.")
    elif diff < 10000:
        print("⚠️ TIMBANGAN AGAK BERISIK (NOISE): Ada interferensi. Kencangkan kabel GND.")
    else:
        print("❌ TIMBANGAN TIDAK KONSISTEN: Angka melompat terlalu jauh.")
        print("   Kemungkinan penyebab:")
        print("   1. Ada solderan kawat load cell yang menempel longgar.")
        print("   2. Kaki GND pada Raspberry Pi kendor.")
        print("   3. Fisik load cell tertekan casing/terganjal kabel di dalam timbangan.")

if __name__ == "__main__":
    main()
