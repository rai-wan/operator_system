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
    timeout = 1000
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

        # Pulsa ke-25 untuk menetapkan gain 128
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
    print("=== ProTrack Scale Raw Test ===")
    print("Membaca data dari HX711...")
    print("Coba tekan sensor untuk melihat perubahan nilai.")
    print("Tekan CTRL+C untuk berhenti.\n")
    
    try:
        while True:
            val = read_raw()
            if val is not None:
                print(f"Raw Value: {val}")
            else:
                print("Sensor tidak siap / Timeout!")
            time.sleep(0.2)
    except KeyboardInterrupt:
        print("\nPengetesan selesai.")
    finally:
        GPIO.cleanup()

if __name__ == "__main__":
    main()
