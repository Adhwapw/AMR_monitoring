# =========================================================
# Konfigurasi poller AMR
# =========================================================

ROBOT_STATUS_API_PORT = 19204

# --- Konfigurasi robot yang mau dipoll ---
ROBOTS = [
    {
        "robot_id_str": "robot-1",   # bebas, ini id internal buat nandain robot di script
        "ip": "192.168.192.5",       # GANTI sesuai IP robot yang sebenarnya
        "port": ROBOT_STATUS_API_PORT,
    },
]

# --- Konfigurasi database MySQL ---
DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "",          # isi kalau root MySQL kamu pakai password
    "database": "robot_amr_logging",
}

# --- Konfigurasi polling ---
POLL_INTERVAL_SECONDS = 2       # jarak waktu antar siklus polling
TCP_TIMEOUT_SECONDS = 3         # timeout buat tiap koneksi/request ke robot
RETRY_DELAY_SECONDS = 5         # jeda sebelum retry kalau robot lagi nggak bisa dihubungi

# API number Robokit yang dipakai
API_ROBOT_INFO = 1000
API_ROBOT_LOC = 1004
API_ROBOT_BATTERY = 1007
API_ROBOT_SPEED = 1005
API_ROBOT_IMU = 1014
API_ROBOT_MOTOR = 1040
API_ROBOT_CONTROL_LOCK = 1060
API_ROBOT_TASK = 1020
API_ROBOT_STATION_LIST = 1301  

# --- Konfigurasi Robot Push (port terpisah, sudah dikonfirmasi jalan di 19301) ---
PUSH_PORT = 19301
PUSH_CONFIG_API = 9300
PUSH_CONFIG_RES_API = 19300
PUSH_INTERVAL_MS = 2000  # interval push dari robot, diminta lewat API 9300
