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
from flask import Flask, jsonify, Response, request
from flask_cors import CORS
from datetime import datetime
import os

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

app = Flask(__name__)
CORS(app)

# ============================================================
# KONFIGURASI
# ============================================================
CONFIG = {
    # Server
    "server_port": 5000,
    "server_host": "0.0.0.0",

    # Kamera UART Serial
    "serial_port": "/dev/serial0",   # Port UART GPIO Pi (Pin 8 & 10)
    "serial_baud": 38400,            # Baud rate default VC0706
    "serial_timeout": 3,

    # Fallback: USB camera (kalau serial gagal)
    "usb_camera_index": 0,

    # IP Camera (Aplikasi HP IP Webcam, e.g. "http://192.168.100.50:8080/video")
    # Atur ke None jika ingin memakai kamera serial atau USB bawaan.
    "ip_camera_url": "http://192.168.100.3:8080/video",

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
    valid = [c for c in contours
             if CONFIG["min_object_area"] < cv2.contourArea(c) < CONFIG["max_object_area"]]

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
    if usb_cap is None and SERIAL_AVAILABLE:
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

    # ---- Fallback ke USB camera (jika IP Cam dan UART Cam gagal) ----
    if usb_cap is None and cam is None:
        print(f"[CAMERA] Mencoba USB Camera index={CONFIG['usb_camera_index']}...")
        usb_cap = cv2.VideoCapture(CONFIG["usb_camera_index"], cv2.CAP_V4L2)
        if usb_cap.isOpened():
            usb_cap.set(cv2.CAP_PROP_FRAME_WIDTH,  CONFIG["capture_width"])
            usb_cap.set(cv2.CAP_PROP_FRAME_HEIGHT, CONFIG["capture_height"])
            usb_cap.set(cv2.CAP_PROP_FPS,          CONFIG["stream_fps"])
            with state["lock"]:
                state["camera_ok"]   = True
                state["camera_type"] = "usb"
                state["error"]       = None
            print("[CAMERA] Mode: USB Camera")
        else:
            usb_cap = None
            with state["lock"]:
                state["camera_ok"] = False
                state["error"]     = "Tidak ada kamera yang terdeteksi (UART & USB gagal)"
            print("[CAMERA] ERROR: Tidak ada kamera tersedia!")
            return

    delay = 1.0 / CONFIG["stream_fps"]

    while True:
        # ---- Ambil frame ----
        frame = None
        if cam is not None:
            frame = cam.capture_frame()
        elif usb_cap is not None:
            ret, f = usb_cap.read()
            if ret:
                frame = f

        if frame is None:
            placeholder = np.zeros((CONFIG["capture_height"], CONFIG["capture_width"], 3), np.uint8)
            cv2.putText(placeholder, "Menunggu frame kamera...", (60, 240),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.8, (120,120,120), 2)
            with state["lock"]:
                state["annotated_frame"] = placeholder
            time.sleep(0.5)
            continue

        # ---- Deteksi objek ----
        with state["lock"]:
            prod_cfg  = state.get("product_config")
            req_count_local = prod_cfg["required_count"] if prod_cfg and "required_count" in prod_cfg else req_count

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
        "timestamp":      datetime.now().isoformat()
    })

@app.route('/stream')
def stream():
    return Response(generate_stream(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')

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
