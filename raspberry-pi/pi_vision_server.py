#!/usr/bin/env python3
"""
=============================================================
  ProTrack Vision Server — Raspberry Pi
  Versi: 2.0 (UART Serial Camera Support)
  Kamera: Serial UART Camera Module (VC0706 / compatible)
  Koneksi:
    Hitam  (5V)  → Pin 4  (5V)
    Orange (GND) → Pin 6  (GND)
    Merah  (GND) → Pin 9  (GND)
    Kuning (TX)  → Pin 10 (RXD Pi / GPIO15)
    Putih  (RX)  → Pin 8  (TXD Pi / GPIO14)
    Hijau  (AV)  → Tidak dipakai
=============================================================
"""

import io
import cv2
import json
import time
import threading
import numpy as np
from flask import Flask, jsonify, Response, request, send_from_directory
from flask_cors import CORS
from datetime import datetime
import os
import gc
import statistics

# Import serial (untuk kamera UART)
try:
    import serial
    SERIAL_AVAILABLE = True
except ImportError:
    SERIAL_AVAILABLE = False
    print("[WARN] pyserial tidak tersedia. Install: pip3 install pyserial")

# Import PIL untuk decode JPEG dari UART
try:
    from PIL import Image
    PIL_AVAILABLE = True
except ImportError:
    PIL_AVAILABLE = False

# Import GPIO (untuk timbangan HX711)
try:
    import RPi.GPIO as GPIO  # type: ignore
    GPIO_AVAILABLE = True
except ImportError:
    GPIO_AVAILABLE = False
    print("[WARN] RPi.GPIO tidak tersedia. Driver timbangan dinonaktifkan.")

app = Flask(__name__)
CORS(app)

@app.after_request
def add_header(response):
    response.headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0'
    response.headers['Pragma'] = 'no-cache'
    response.headers['Expires'] = '0'
    return response

# ============================================================
# KONFIGURASI
# ============================================================
CONFIG = {
    # Server
    "server_port": 5000,
    "server_host": "0.0.0.0",

    # Kamera UART Serial
    "use_serial_camera": False,      # Set True jika memakai kamera serial UART VC0706
    "serial_port": "/dev/serial0",   # Port UART GPIO Pi (Pin 8 & 10)
    "serial_baud": 38400,            # Baud rate default VC0706
    "serial_timeout": 3,

    # Fallback: USB camera (kalau serial gagal)
    "usb_camera_index": 0,

    # IP Camera (Aplikasi HP IP Webcam, e.g. "http://192.168.100.50:8080/video")
    # Atur ke None jika ingin memakai kamera serial atau USB bawaan.
    "ip_camera_url": None,

    # Resolusi capture
    "capture_width":  640,
    "capture_height": 480,

    # ROI — area box dalam frame (persen 0.0 - 1.0)
    "roi_x": 0.1,
    "roi_y": 0.1,
    "roi_w": 0.8,
    "roi_h": 0.8,

    # Deteksi
    "required_item_count": 2,
    "min_object_area":    2500,
    "max_object_area":   90000,
    "stable_seconds":     1.5,

    # FPS stream
    "stream_fps": 10,

    # Timbangan (HX711)
    "scale_calibration": 148295.0,  # Nilai kalibrasi: (Raw - Offset) / Scale
}

# ============================================================
# STATE GLOBAL
# ============================================================
state = {
    "camera_ok":      False,
    "camera_type":    None,   # "serial" atau "usb"
    "item_count":     0,
    "is_complete":    False,
    "stable_since":   None,
    "last_frame_time": None,
    "error":          None,
    "annotated_frame": None,
    "product_config": None,
    "weight":         0.0,
    "raw_weight":     0,
    "raw_frame":      None,
    "lock":           threading.Lock(),
}

product_configs = {}

def load_product_config():
    path = os.path.join(os.path.dirname(__file__), "product_config.json")
    if os.path.exists(path):
        with open(path) as f:
            return json.load(f)
    return {}

product_configs = load_product_config()


# ============================================================
# KAMERA UART SERIAL (VC0706 Protocol)
# ============================================================
class SerialCamera:
    """Driver untuk UART Serial Camera (VC0706 compatible)"""

    CMD_RESET      = bytes([0x56, 0x00, 0x26, 0x00])
    CMD_VERSION    = bytes([0x56, 0x00, 0x11, 0x00])
    CMD_TAKE_PHOTO = bytes([0x56, 0x00, 0x36, 0x01, 0x00])
    CMD_GET_SIZE   = bytes([0x56, 0x00, 0x34, 0x01, 0x00])
    CMD_READ_DATA  = bytes([0x56, 0x00, 0x32, 0x0C, 0x00, 0x0A,
                             0x00, 0x00, 0x00, 0x00,
                             0x00, 0x00, 0x00, 0x00,
                             0x00, 0x0A])
    CMD_RESUME     = bytes([0x56, 0x00, 0x36, 0x01, 0x03])

    def __init__(self, port, baud=38400, timeout=3):
        self.port    = port
        self.baud    = baud
        self.timeout = timeout
        self.ser     = None
        self.ready   = False

    def connect(self):
        try:
            self.ser = serial.Serial(
                self.port, self.baud,
                timeout=self.timeout,
                bytesize=serial.EIGHTBITS,
                parity=serial.PARITY_NONE,
                stopbits=serial.STOPBITS_ONE
            )
            time.sleep(0.5)
            # Kirim reset
            self.ser.write(self.CMD_RESET)
            time.sleep(2)
            self.ser.reset_input_buffer()
            # Cek versi
            self.ser.write(self.CMD_VERSION)
            time.sleep(0.5)
            resp = self.ser.read(16)
            if resp and len(resp) > 0:
                self.ready = True
                print(f"[UART CAM] Terhubung: {self.port} @ {self.baud} baud")
                return True
            else:
                print(f"[UART CAM] Tidak ada respons dari kamera")
                return False
        except Exception as e:
            print(f"[UART CAM] Gagal koneksi: {e}")
            return False

    def capture_frame(self):
        """Ambil 1 frame JPEG dari kamera serial. Return: numpy array BGR atau None."""
        if not self.ser or not self.ready:
            return None
        try:
            # 1. Ambil foto
            self.ser.write(self.CMD_TAKE_PHOTO)
            time.sleep(0.1)
            self.ser.read(5)  # flush response

            # 2. Dapatkan ukuran file
            self.ser.write(self.CMD_GET_SIZE)
            time.sleep(0.1)
            size_resp = self.ser.read(9)
            if len(size_resp) < 9:
                return None

            img_size = (size_resp[7] << 8) | size_resp[8]
            if img_size == 0 or img_size > 100000:
                return None

            # 3. Baca data JPEG (buat READ_DATA command dengan ukuran sebenarnya)
            read_cmd = bytearray(self.CMD_READ_DATA)
            read_cmd[10] = (img_size >> 24) & 0xFF
            read_cmd[11] = (img_size >> 16) & 0xFF
            read_cmd[12] = (img_size >> 8)  & 0xFF
            read_cmd[13] = (img_size)       & 0xFF
            self.ser.write(bytes(read_cmd))
            time.sleep(0.1)
            self.ser.read(5)  # header response

            jpeg_data = self.ser.read(img_size)
            self.ser.read(5)  # footer

            # 4. Decode JPEG ke numpy array
            if len(jpeg_data) == img_size:
                arr  = np.frombuffer(jpeg_data, dtype=np.uint8)
                frame = cv2.imdecode(arr, cv2.IMREAD_COLOR)
                # 5. Resume video
                self.ser.write(self.CMD_RESUME)
                time.sleep(0.05)
                self.ser.read(5)
                return frame
            return None

        except Exception as e:
            print(f"[UART CAM] Error capture: {e}")
            return None

    def close(self):
        if self.ser:
            self.ser.close()


import subprocess

class LibcameraCamera:
    """Driver untuk Raspberry Pi CSI Camera menggunakan subprocess rpicam-vid"""
    def __init__(self, width=640, height=480, fps=10):
        self.width = width
        self.height = height
        self.fps = fps
        self.process = None
        self.running = False
        self.thread = None
        self.last_frame = None
        self.lock = threading.Lock()

    def connect(self):
        try:
            cmd = [
                "rpicam-vid",
                "--timeout", "0",
                "--width", str(self.width),
                "--height", str(self.height),
                "--codec", "mjpeg",
                "--inline",
                "--framerate", str(self.fps),
                "-o", "-",
                "-n"
            ]
            self.process = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, bufsize=0)
            self.running = True
            self.thread = threading.Thread(target=self._read_loop, daemon=True)
            self.thread.start()
            print("[LIBCAMERA] Driver rpicam-vid berhasil dijalankan")
            return True
        except Exception as e:
            print(f"[LIBCAMERA] Gagal menjalankan rpicam-vid: {e}")
            return False

    def _read_loop(self):
        buffer = b""
        while self.running:
            try:
                chunk = self.process.stdout.read(4096)
                if not chunk:
                    break
                buffer += chunk
                
                a = buffer.find(b"\xff\xd8")
                b = buffer.find(b"\xff\xd9")
                while a != -1 and b != -1 and b > a:
                    jpg = buffer[a:b+2]
                    buffer = buffer[b+2:]
                    
                    arr = np.frombuffer(jpg, dtype=np.uint8)
                    frame = cv2.imdecode(arr, cv2.IMREAD_COLOR)
                    if frame is not None:
                        with self.lock:
                            self.last_frame = frame.copy()
                            
                    a = buffer.find(b"\xff\xd8")
                    b = buffer.find(b"\xff\xd9")
            except Exception as e:
                time.sleep(0.1)

    def capture_frame(self):
        with self.lock:
            if self.last_frame is not None:
                return self.last_frame.copy()
        return None

    def close(self):
        self.running = False
        if self.process:
            self.process.terminate()
            try:
                self.process.wait(timeout=1)
            except subprocess.TimeoutExpired:
                self.process.kill()


# ============================================================
# DETEKSI OBJEK (OpenCV)
# ============================================================
def detect_objects(frame):
    """
    Deteksi jumlah objek dalam ROI.
    Return: (count, annotated_frame, bboxes)
    """
    h, w = frame.shape[:2]
    rx = int(CONFIG["roi_x"] * w)
    ry = int(CONFIG["roi_y"] * h)
    rw = int(CONFIG["roi_w"] * w)
    rh = int(CONFIG["roi_h"] * h)

    roi    = frame[ry:ry+rh, rx:rx+rw]
    gray   = cv2.cvtColor(roi, cv2.COLOR_BGR2GRAY)
    blur   = cv2.GaussianBlur(gray, (7, 7), 0)
    edges  = cv2.Canny(blur, 30, 100)
    dil    = cv2.dilate(edges, None, iterations=2)
    thresh = cv2.adaptiveThreshold(blur, 255,
                 cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                 cv2.THRESH_BINARY_INV, 11, 3)
    mask   = cv2.bitwise_or(dil, thresh)
    kernel = np.ones((5,5), np.uint8)
    mask   = cv2.morphologyEx(mask, cv2.MORPH_CLOSE, kernel)
    mask   = cv2.morphologyEx(mask, cv2.MORPH_OPEN,  kernel)

    contours, _ = cv2.findContours(mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    # Filter berdasarkan area lalu ambil MAKSIMAL 2 OBJEK TERBESAR saja
    valid_all = [c for c in contours
                 if CONFIG["min_object_area"] < cv2.contourArea(c) < CONFIG["max_object_area"]]
    valid_all.sort(key=cv2.contourArea, reverse=True)  # Urutkan dari yang terbesar
    valid = valid_all[:2]  # Ambil hanya 2 terbesar

    annotated = frame.copy()
    cv2.rectangle(annotated, (rx, ry), (rx+rw, ry+rh), (255, 140, 0), 2)
    cv2.putText(annotated, "AREA DETEKSI", (rx+5, ry+18),
                cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255,140,0), 1)

    bboxes = []
    for i, cnt in enumerate(valid):
        x, y, bw, bh = cv2.boundingRect(cnt)
        ax, ay = rx+x, ry+y
        cv2.rectangle(annotated, (ax, ay), (ax+bw, ay+bh), (0,200,80), 2)
        cv2.putText(annotated, f"#{i+1}", (ax, ay-5),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0,200,80), 2)
        bboxes.append({"x": ax, "y": ay, "w": bw, "h": bh})

    return len(valid), annotated, bboxes


def overlay_status(frame, count, req, is_complete):
    """Overlay teks status di bagian atas frame."""
    h, w = frame.shape[:2]
    color = (30, 200, 60) if is_complete else (0, 60, 220)
    label = f"LENGKAP — SCAN OK" if is_complete else f"BELUM LENGKAP"
    count_txt = f"Item: {count}/{req}"

    cv2.rectangle(frame, (0,0), (w, 55), (20,20,20), -1)
    cv2.putText(frame, count_txt, (10, 24),
                cv2.FONT_HERSHEY_SIMPLEX, 0.75, color, 2)
    cv2.putText(frame, label, (10, 47),
                cv2.FONT_HERSHEY_SIMPLEX, 0.6, color, 2)
    ts = datetime.now().strftime("%H:%M:%S")
    cv2.putText(frame, ts, (w-80, 20),
                cv2.FONT_HERSHEY_SIMPLEX, 0.45, (160,160,160), 1)
    return frame


# ============================================================
# THREAD KAMERA — Jalan terus di background
# ============================================================
def camera_thread():
    req_count    = CONFIG["required_item_count"]
    stable_start = None
    last_count   = -1
    cam          = None
    usb_cap      = None

    # ---- Coba IP Camera (HP) jika dikonfigurasi ----
    if CONFIG.get("ip_camera_url"):
        print(f"[CAMERA] Mencoba IP Camera: {CONFIG['ip_camera_url']}...")
        usb_cap = cv2.VideoCapture(CONFIG["ip_camera_url"])
        if usb_cap.isOpened():
            with state["lock"]:
                state["camera_ok"]   = True
                state["camera_type"] = "ip"
                state["error"]       = None
            print(f"[CAMERA] Mode: IP Camera ({CONFIG['ip_camera_url']})")
        else:
            usb_cap = None
            print("[CAMERA] IP Camera gagal terhubung.")

    # ---- Coba kamera UART Serial jika IP Camera tidak aktif/gagal ----
    if usb_cap is None and SERIAL_AVAILABLE and CONFIG.get("use_serial_camera", False):
        print(f"[CAMERA] Mencoba UART Serial: {CONFIG['serial_port']}...")
        cam = SerialCamera(CONFIG["serial_port"], CONFIG["serial_baud"])
        if cam.connect():
            with state["lock"]:
                state["camera_ok"]   = True
                state["camera_type"] = "serial"
                state["error"]       = None
            print("[CAMERA] Mode: UART Serial")
        else:
            cam = None
            print("[CAMERA] UART gagal. Coba USB camera...")

    # ---- Fallback ke USB/CSI Camera via V4L2 ----
    # Catatan: jalankan server via "libcamerify python3 pi_vision_server.py" agar
    # Raspberry Pi Camera Module (CSI) dapat diakses sebagai /dev/video0 oleh OpenCV.
    # JANGAN gunakan LibcameraCamera (rpicam-vid subprocess) di sini karena rpicam-vid
    # akan mewarisi LD_PRELOAD dari libcamerify dan mengalami konflik.
    if usb_cap is None and cam is None:
        print(f"[CAMERA] Mencoba USB/CSI Camera index={CONFIG['usb_camera_index']} via V4L2...")
        usb_cap = cv2.VideoCapture(CONFIG["usb_camera_index"], cv2.CAP_V4L2)
        if usb_cap.isOpened():
            usb_cap.set(cv2.CAP_PROP_FRAME_WIDTH,  CONFIG["capture_width"])
            usb_cap.set(cv2.CAP_PROP_FRAME_HEIGHT, CONFIG["capture_height"])
            usb_cap.set(cv2.CAP_PROP_FPS,          CONFIG["stream_fps"])
            with state["lock"]:
                state["camera_ok"]   = True
                state["camera_type"] = "usb"
                state["error"]       = None
            print("[CAMERA] Mode: USB/CSI Camera via V4L2 (libcamerify)")
        else:
            usb_cap = None
            with state["lock"]:
                state["camera_ok"] = False
                state["error"]     = "Tidak ada kamera yang terdeteksi (UART & V4L2 gagal)"
            print("[CAMERA] ERROR: Tidak ada kamera tersedia! Pastikan server dijalankan dengan libcamerify.")
            return

    delay = 1.0 / CONFIG["stream_fps"]

    # Tracker untuk mendeteksi kamera mati dan melakukan reconnect
    consecutive_failures = 0
    MAX_FAILURES = 20  # Setelah 20 frame kosong berturut-turut (~2 detik), reconnect

    while True:
        # ---- Ambil frame ----
        frame = None
        if cam is not None:
            frame = cam.capture_frame()
        elif usb_cap is not None:
            ret, f = usb_cap.read()
            if ret:
                frame = f

        # ---- Deteksi kamera mati & lakukan reconnect ----
        if frame is None:
            consecutive_failures += 1

            placeholder = np.zeros((CONFIG["capture_height"], CONFIG["capture_width"], 3), np.uint8)
            msg = f"Menunggu frame kamera... ({consecutive_failures}/{MAX_FAILURES})"
            cv2.putText(placeholder, msg, (30, 240),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (120,120,120), 2)
            with state["lock"]:
                state["annotated_frame"] = placeholder
                state["raw_frame"] = None

            # Jika terlalu banyak frame kosong, coba reconnect kamera
            if consecutive_failures >= MAX_FAILURES:
                consecutive_failures = 0
                print(f"[CAMERA] Frame kosong {MAX_FAILURES}x berturut-turut. Mencoba reconnect kamera...")

                # Tutup koneksi lama
                try:
                    if cam is not None:
                        cam.close()
                        cam = None
                    if usb_cap is not None:
                        usb_cap.release()
                        usb_cap = None
                except Exception:
                    pass

                with state["lock"]:
                    state["camera_ok"] = False

                time.sleep(3)  # Tunggu 3 detik sebelum reconnect

                # Reconnect via V4L2 (libcamerify) — cara yang sama dengan inisialisasi awal
                print(f"[CAMERA] Mencoba reconnect USB/CSI Camera index={CONFIG['usb_camera_index']}...")
                usb_cap = cv2.VideoCapture(CONFIG["usb_camera_index"], cv2.CAP_V4L2)
                if usb_cap.isOpened():
                    usb_cap.set(cv2.CAP_PROP_FRAME_WIDTH,  CONFIG["capture_width"])
                    usb_cap.set(cv2.CAP_PROP_FRAME_HEIGHT, CONFIG["capture_height"])
                    usb_cap.set(cv2.CAP_PROP_FPS,          CONFIG["stream_fps"])
                    with state["lock"]:
                        state["camera_ok"]   = True
                        state["camera_type"] = "usb"
                        state["error"]       = None
                    print("[CAMERA] Reconnect USB/CSI berhasil!")
                else:
                    usb_cap = None
                    with state["lock"]:
                        state["camera_ok"] = False
                        state["error"]     = "Reconnect gagal. Pastikan libcamerify aktif."
                    print("[CAMERA] Reconnect gagal. Menunggu 10 detik...")
                    time.sleep(10)

            else:
                time.sleep(0.1)

            continue

        # Frame berhasil diambil — reset failure counter
        consecutive_failures = 0

        # ---- Deteksi objek ----
        with state["lock"]:
            prod_cfg  = state.get("product_config")
            req_count_local = prod_cfg["required_count"] if prod_cfg and "required_count" in prod_cfg else req_count

        # Beri jeda kecil (10ms) agar thread timbangan tidak terganggu GIL / CPU contention
        time.sleep(0.01)
        count, annotated, _ = detect_objects(frame)

        # Logika stabilisasi
        is_complete = (count >= req_count_local)
        if count == last_count:
            if stable_start is None:
                stable_start = time.time()
            stable = (time.time() - stable_start) >= CONFIG["stable_seconds"]
        else:
            stable_start = None
            stable       = False
            last_count   = count

        is_complete_stable = is_complete and stable
        annotated = overlay_status(annotated, count, req_count_local, is_complete_stable)

        with state["lock"]:
            state["item_count"]      = count
            state["is_complete"]     = is_complete_stable
            state["last_frame_time"] = time.time()
            state["annotated_frame"] = annotated.copy()
            state["raw_frame"]       = frame.copy()
            if is_complete_stable and state["stable_since"] is None:
                state["stable_since"] = time.time()
            elif not is_complete_stable:
                state["stable_since"] = None

        time.sleep(delay)

    if cam:     cam.close()
    if usb_cap: usb_cap.release()


# ============================================================
# STREAM GENERATOR
# ============================================================
def generate_stream():
    delay = 1.0 / CONFIG["stream_fps"]
    while True:
        with state["lock"]:
            frame = state.get("annotated_frame")
        if frame is None:
            frame = np.zeros((320, 480, 3), np.uint8)
            cv2.putText(frame, "Menunggu kamera...", (60, 160),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (180,180,180), 2)
        _, jpeg = cv2.imencode('.jpg', frame, [cv2.IMWRITE_JPEG_QUALITY, 65])
        yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n'
               + jpeg.tobytes() + b'\r\n')
        time.sleep(delay)


# ============================================================
# FLASK ENDPOINTS
# ============================================================
@app.route('/')
def index():
    with state["lock"]:
        cam_type = state.get("camera_type", "unknown")
    return jsonify({
        "service": "ProTrack Vision Server",
        "version": "2.0",
        "camera_type": cam_type,
        "endpoints": {
            "/status":     "GET  — Status deteksi",
            "/stream":     "GET  — Live MJPEG stream",
            "/health":     "GET  — Health check",
            "/config":     "POST — Kirim konfigurasi produk",
            "/reset":      "POST — Reset status deteksi",
        }
    })

@app.route('/health')
def health():
    with state["lock"]:
        cam_ok = state["camera_ok"]
        err    = state["error"]
        ctype  = state.get("camera_type")
    return jsonify({
        "ok":          cam_ok and err is None,
        "camera":      cam_ok,
        "camera_type": ctype,
        "error":       err,
        "timestamp":   datetime.now().isoformat()
    })

@app.route('/status')
def status():
    with state["lock"]:
        count        = state["item_count"]
        is_complete  = state["is_complete"]
        cam_ok       = state["camera_ok"]
        err          = state["error"]
        stable_since = state["stable_since"]
        prod_cfg     = state.get("product_config")
        last_frame   = state["last_frame_time"]
        weight       = state.get("weight", 0.0)
        raw_weight   = state.get("raw_weight", 0)

    req_count = CONFIG["required_item_count"]
    if prod_cfg and "required_count" in prod_cfg:
        req_count = prod_cfg["required_count"]

    camera_live    = bool(last_frame and (time.time() - last_frame < 8))
    stable_dur     = round(time.time() - stable_since, 1) if stable_since else 0

    return jsonify({
        "ok":             True,
        "camera_ok":      cam_ok and camera_live,
        "is_complete":    is_complete,
        "scan_allowed":   is_complete and cam_ok,
        "item_count":     count,
        "required_count": req_count,
        "stable_seconds": stable_dur,
        "product_config": prod_cfg,
        "error":          err,
        "timestamp":      datetime.now().isoformat(),
        "weight":         weight,
        "raw_weight":     raw_weight
    })

@app.route('/stream')
def stream():
    return Response(generate_stream(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/snapshot')
def snapshot():
    """Ambil satu frame terkini sebagai file foto JPEG dan kembalikan ke browser."""
    import os
    with state["lock"]:
        ann = state.get("annotated_frame")

    if ann is None:
        return jsonify({"ok": False, "error": "Kamera belum aktif / tidak ada frame"}), 503

    # Encode frame ke JPEG
    ok, buf = cv2.imencode('.jpg', ann, [cv2.IMWRITE_JPEG_QUALITY, 90])
    if not ok:
        return jsonify({"ok": False, "error": "Gagal encode gambar"}), 500

    # Simpan ke disk agar bisa diakses kembali
    snapshot_dir = '/tmp/protrack_snapshots'
    os.makedirs(snapshot_dir, exist_ok=True)
    filename = datetime.now().strftime('snapshot_%Y%m%d_%H%M%S.jpg')
    filepath = os.path.join(snapshot_dir, filename)
    with open(filepath, 'wb') as f:
        f.write(buf.tobytes())
    print(f"[SNAPSHOT] Foto disimpan: {filepath}")

    # Kembalikan gambar langsung ke browser
    return Response(
        buf.tobytes(),
        mimetype='image/jpeg',
        headers={
            'Content-Disposition': f'inline; filename="{filename}"',
            'X-Snapshot-File': filepath
        }
    )

@app.route('/config', methods=['POST'])
def set_config():
    data = request.get_json()
    if not data:
        return jsonify({"ok": False, "error": "Invalid JSON"}), 400
    with state["lock"]:
        state["product_config"] = data
        state["is_complete"]    = False
        state["stable_since"]   = None
    print(f"[CONFIG] Produk: {data.get('product_name','?')} | Req: {data.get('required_count','?')}")
    return jsonify({"ok": True, "message": "Konfigurasi diperbarui"})

@app.route('/reset', methods=['POST'])
def reset():
    with state["lock"]:
        state["is_complete"]  = False
        state["stable_since"] = None
        state["item_count"]   = 0
    return jsonify({"ok": True, "message": "Status direset"})


# ============================================================
# DRIVER TIMBANGAN (HX711) & THREAD
# ============================================================
class HX711Driver:
    def __init__(self, dout=5, sck=6, scale=1000.0):
        self.dout = dout
        self.sck = sck
        self.scale = scale
        self.offset = 0
        self.last_raw = 0
        self.ready = False

    def connect(self):
        if not GPIO_AVAILABLE:
            return False
        try:
            GPIO.setmode(GPIO.BCM)
            GPIO.setup(self.sck, GPIO.OUT)
            GPIO.setup(self.dout, GPIO.IN)
            GPIO.output(self.sck, False)
            self.ready = True

            # Buang beberapa pembacaan pertama - HX711 belum stabil
            # persis setelah power-up
            for _ in range(3):
                self.read_raw()
                time.sleep(0.05)

            self.tare()
            print("[SCALE] Driver HX711 berhasil diinisialisasi")
            return True
        except Exception as e:
            print(f"[SCALE] Gagal inisialisasi HX711: {e}")
            return False

    def read_raw(self):
        if not self.ready:
            return None

        for _ in range(200):
            if GPIO.input(self.dout) == 0:
                break
            time.sleep(0.001)
        else:
            return None  # Timeout

        # --- CRITICAL SECTION: proteksi dari gangguan thread kamera ---
        gc_was_enabled = gc.isenabled()
        gc.disable()

        value = 0
        try:
            for _ in range(24):
                GPIO.output(self.sck, True)
                value = value << 1
                GPIO.output(self.sck, False)
                if GPIO.input(self.dout):
                    value += 1

            # Pulsa ke-25 (gain 128, channel A)
            GPIO.output(self.sck, True)
            GPIO.output(self.sck, False)
        finally:
            if gc_was_enabled:
                gc.enable()
        # --- END CRITICAL SECTION ---

        if value & 0x800000:
            value -= 0x1000000

        self.last_raw = value
        return value

    def read_average(self, times=7):
        """Ambil beberapa sampel lalu buang outlier (median filtering)."""
        samples = []
        for _ in range(times):
            v = self.read_raw()
            if v is not None:
                samples.append(v)
            time.sleep(0.02)

        if not samples:
            return self.last_raw

        if len(samples) < 3:
            return sum(samples) / len(samples)

        med = statistics.median(samples)
        mad = statistics.median([abs(s - med) for s in samples]) or 1
        filtered = [s for s in samples if abs(s - med) < 5 * mad]

        return sum(filtered) / len(filtered) if filtered else med

    def get_weight(self):
        val = self.read_average(5)
        return (val - self.offset) / self.scale

    def tare(self):
        print("[SCALE] Menjalankan Tare (Zeroing)...")
        self.offset = self.read_average(20)
        print(f"[SCALE] Tare selesai. Offset: {self.offset}")


hx_sensor = None

def scale_thread():
    global hx_sensor
    if not GPIO_AVAILABLE:
        print("[SCALE] GPIO tidak tersedia. Thread timbangan diabaikan.")
        return

    print("[SCALE] Memulai thread timbangan...")
    hx_sensor = HX711Driver(dout=5, sck=6, scale=CONFIG.get("scale_calibration", 1000.0))
    if hx_sensor.connect():
        while True:
            try:
                weight = hx_sensor.get_weight()
                raw = hx_sensor.last_raw
                # Bulatkan ke 2 desimal (presisi 10 gram) untuk kestabilan tampilan
                weight = round(weight, 2)
                if weight < 0.02:
                    weight = 0.0
                with state["lock"]:
                    state["weight"] = weight
                    state["raw_weight"] = raw
            except Exception as e:
                print(f"[SCALE] Error read: {e}")
            time.sleep(0.2)

@app.route('/tare', methods=['POST'])
def run_tare():
    global hx_sensor
    if hx_sensor:
        hx_sensor.tare()
        return jsonify({"ok": True, "message": "Scale tared successfully", "offset": hx_sensor.offset})
    return jsonify({"ok": False, "error": "Scale not initialized"}), 500


# ============================================================
# QUALITY INSPECTION (SSIM + OPENCV)
# ============================================================
def compute_ssim(img1, img2):
    """Hitung Structural Similarity Index (SSIM) antara dua citra grayscale."""
    # Pastikan ukuran sama
    if img1.shape != img2.shape:
        img2 = cv2.resize(img2, (img1.shape[1], img1.shape[0]))

    img1 = img1.astype(np.float64)
    img2 = img2.astype(np.float64)

    # Konstanta untuk menstabilkan pembagian
    C1 = (0.01 * 255) ** 2
    C2 = (0.03 * 255) ** 2

    # Rata-rata lokal menggunakan Gaussian Blur
    mu1 = cv2.GaussianBlur(img1, (11, 11), 1.5)
    mu2 = cv2.GaussianBlur(img2, (11, 11), 1.5)

    mu1_sq = mu1 ** 2
    mu2_sq = mu2 ** 2
    mu1_mu2 = mu1 * mu2

    # Varians dan Kovarians lokal
    sigma1_sq = cv2.GaussianBlur(img1 ** 2, (11, 11), 1.5) - mu1_sq
    sigma2_sq = cv2.GaussianBlur(img2 ** 2, (11, 11), 1.5) - mu2_sq
    sigma12 = cv2.GaussianBlur(img1 * img2, (11, 11), 1.5) - mu1_mu2

    # Rumus SSIM
    num = (2 * mu1_mu2 + C1) * (2 * sigma12 + C2)
    den = (mu1_sq + mu2_sq + C1) * (sigma1_sq + sigma2_sq + C2)
    ssim_map = num / den
    
    return float(np.mean(ssim_map))

def analyze_quality(current_frame, reference_image, threshold=85.0):
    """
    Analisis kualitas frame saat ini terhadap gambar referensi.
    Mengembalikan: (status, defect_type, confidence, similarity)
    """
    # Resize ke 320x240 agar perhitungan SSIM di CPU Raspberry Pi sangat cepat (<0.1s)
    TARGET_SIZE = (320, 240)
    curr_resized = cv2.resize(current_frame, TARGET_SIZE)
    ref_resized = cv2.resize(reference_image, TARGET_SIZE)

    # 1. Konversi ke grayscale
    gray_curr = cv2.cvtColor(curr_resized, cv2.COLOR_BGR2GRAY)
    gray_ref = cv2.cvtColor(ref_resized, cv2.COLOR_BGR2GRAY)

    # 2. Hitung nilai kesamaan SSIM
    similarity = compute_ssim(gray_ref, gray_curr)
    similarity_percentage = round(similarity * 100, 1)
    
    # 4. Tentukan status kelayakan (PASS / REJECT)
    status = "pass" if similarity_percentage >= threshold else "reject"
    defect_type = "ok"
    confidence = similarity_percentage
    
    # Jika reject, lakukan analisis jenis defect (kerobekan/penyok/missing)
    if status == "reject":
        # Selisih absolut (difference map)
        diff = cv2.absdiff(gray_ref, gray_curr)
        _, thresh = cv2.threshold(diff, 30, 255, cv2.THRESH_BINARY)
        
        # Hitung rasio perbedaan
        diff_ratio = (np.sum(thresh == 255) / thresh.size) * 100
        
        if similarity_percentage < 45.0:
            defect_type = "missing"  # Komponen hilang / salah barang
            confidence = round(100.0 - similarity_percentage, 1)
        else:
            # Temukan kontur perbedaan
            contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            large_contours = [c for c in contours if cv2.contourArea(c) > 500]
            
            # Canny edge detection pada peta selisih untuk mendeteksi goresan/sobekan tajam
            edges = cv2.Canny(diff, 50, 150)
            edge_pixels = np.sum(edges > 0)
            
            if edge_pixels > 3000 and len(large_contours) < 3:
                defect_type = "tear"  # Sobek / Goresan
                confidence = round(min(98.5, similarity_percentage + 15), 1)
            else:
                defect_type = "dent"  # Penyok / Deformasi
                confidence = round(min(95.0, 100.0 - similarity_percentage), 1)
                
    return status, defect_type, confidence, similarity_percentage

@app.route('/reference', methods=['POST'])
def upload_reference():
    """Upload dan simpan gambar referensi (contoh produk OK) untuk suatu produk."""
    product_code = request.form.get('product_code') or (request.json.get('product_code') if request.is_json else None)
    if not product_code:
        return jsonify({"ok": False, "error": "Parameter product_code wajib diisi"}), 400
    
    # Bersihkan nama file dari karakter ilegal
    clean_code = "".join([c for c in product_code if c.isalnum() or c in ('-', '_')]).strip()
    if not clean_code:
        return jsonify({"ok": False, "error": "Kode produk tidak valid"}), 400
        
    file_bytes = None
    if 'file' in request.files:
        file_bytes = request.files['file'].read()
    elif request.is_json and 'image_base64' in request.json:
        import base64
        try:
            file_bytes = base64.b64decode(request.json['image_base64'])
        except Exception as e:
            return jsonify({"ok": False, "error": f"Gagal decode base64: {str(e)}"}), 400

    if not file_bytes:
        return jsonify({"ok": False, "error": "Tidak ada data gambar dalam request"}), 400

    try:
        # Decode menjadi OpenCV image untuk memvalidasi
        nparr = np.frombuffer(file_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        if img is None:
            return jsonify({"ok": False, "error": "Format file bukan gambar yang valid"}), 400

        # Tentukan folder penyimpanan referensi
        ref_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'references')
        os.makedirs(ref_dir, exist_ok=True)
        filepath = os.path.join(ref_dir, f"{clean_code}.jpg")

        # Simpan gambar
        cv2.imwrite(filepath, img)
        print(f"[QUALITY] Gambar referensi disimpan: {filepath}")
        
        return jsonify({
            "ok": True,
            "message": f"Gambar referensi untuk {product_code} berhasil disimpan.",
            "path": filepath
        })
    except Exception as e:
        return jsonify({"ok": False, "error": f"Gagal menyimpan gambar referensi: {str(e)}"}), 500

@app.route('/references/<filename>')
def get_reference_file(filename):
    """Ambil file referensi gambar dari disk."""
    ref_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'references')
    return send_from_directory(ref_dir, filename)

@app.route('/inspect', methods=['POST'])
def run_inspection():
    """Ambil frame terkini dan bandingkan dengan gambar referensi produk."""
    product_code = request.form.get('product_code') or (request.json.get('product_code') if request.is_json else None)
    if not product_code:
        return jsonify({"ok": False, "error": "Parameter product_code wajib diisi"}), 400
    
    clean_code = "".join([c for c in product_code if c.isalnum() or c in ('-', '_')]).strip()
    
    ref_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'references')
    ref_path = os.path.join(ref_dir, f"{clean_code}.jpg")
    
    if not os.path.exists(ref_path):
        ref_path = os.path.join(ref_dir, "default.jpg")
        
    if not os.path.exists(ref_path):
        return jsonify({
            "ok": False, 
            "error_type": "no_reference",
            "error": f"Gambar referensi (OK) untuk produk '{product_code}' belum diupload oleh Admin dan default.jpg tidak ditemukan."
        }), 404
        
    ref_img = cv2.imread(ref_path)
    if ref_img is None:
        return jsonify({"ok": False, "error": "Gagal membaca file gambar referensi di Pi"}), 500

    # Ambil frame mentah terkini
    with state["lock"]:
        curr_frame = state.get("raw_frame")
        if curr_frame is None:
            curr_frame = state.get("annotated_frame") # Fallback jika raw_frame kosong

    if curr_frame is None:
        return jsonify({"ok": False, "error": "Kamera belum siap atau tidak ada frame"}), 503

    try:
        # Clone frame agar aman dimanipulasi
        inspect_frame = curr_frame.copy()
        
        # Jalankan algoritma analisis kualitas
        status, defect_type, confidence, similarity = analyze_quality(inspect_frame, ref_img, threshold=85.0)
        
        # Buat visualisasi hasil inspeksi (annotated image)
        annotated_result = inspect_frame.copy()
            
        # Encode gambar hasil inspeksi ke Base64
        _, buf = cv2.imencode('.jpg', annotated_result, [cv2.IMWRITE_JPEG_QUALITY, 85])
        import base64
        img_base64 = base64.b64encode(buf.tobytes()).decode('utf-8')
        
        # Simpan sementara di folder snapshot jika diperlukan log visual
        inspection_log_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'inspections')
        os.makedirs(inspection_log_dir, exist_ok=True)
        filename = f"inspect_{clean_code}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.jpg"
        filepath = os.path.join(inspection_log_dir, filename)
        cv2.imwrite(filepath, annotated_result)
        
        return jsonify({
            "ok": True,
            "status": status,
            "defect_type": defect_type,
            "confidence": confidence,
            "similarity": similarity,
            "image_base64": img_base64,
            "filepath": filepath
        })
    except Exception as e:
        return jsonify({"ok": False, "error": f"Error saat analisis kualitas: {str(e)}"}), 500


# ============================================================
# MAIN
# ============================================================
if __name__ == '__main__':
    print("=" * 56)
    print("  ProTrack Vision Server v2.0 — Raspberry Pi")
    print("  Kamera: UART Serial (VC0706) / USB Fallback")
    print("=" * 56)

    if not SERIAL_AVAILABLE:
        print("[WARN] Install pyserial: pip3 install pyserial")

    cam_thread = threading.Thread(target=camera_thread, daemon=True)
    cam_thread.start()
    
    scale_thr = threading.Thread(target=scale_thread, daemon=True)
    scale_thr.start()
    
    time.sleep(3)

    import socket
    hostname = socket.gethostname()
    try:
        local_ip = socket.gethostbyname(hostname)
    except:
        local_ip = "webbased.local"

    print(f"[SERVER] Hostname : {hostname}")
    print(f"[SERVER] URL      : http://{hostname}.local:{CONFIG['server_port']}")
    print(f"[SERVER] Stream   : http://{hostname}.local:{CONFIG['server_port']}/stream")
    print(f"[SERVER] Status   : http://{hostname}.local:{CONFIG['server_port']}/status")
    print("=" * 56)

    app.run(host=CONFIG["server_host"], port=CONFIG["server_port"],
            debug=False, threaded=True)
