# =========================================================
# Konfigurasi poller AMR
# =========================================================

# --- Port Robokit dibagi per jenis API, BUKAN satu port buat semua ---
# Robot Status API   (nomor 1000-1999) -> port 19204
# Robot Control API  (nomor 2000-2999) -> port 19205
# Robot Navigation API (nomor 3000-3999) -> port 19206
# Robot Configuration API (nomor 4000-4999) -> port 19207
# Other APIs (nomor 6000-6999) -> port 19210
#
# Semua API yang dipakai poller ini (info, lokasi, baterai, speed, imu,
# motor, control lock, task) ada di range 1000-1999, jadi semuanya
# konek ke port 19204 (Robot Status API). Kalau nanti nambah API dari
# kategori lain (misal Control API buat gerakin robot), itu butuh
# koneksi/port terpisah (19205), nggak bisa digabung ke koneksi yang sama.
ROBOT_STATUS_API_PORT = 19204

# --- Konfigurasi robot yang mau dipoll ---
# ip masih placeholder, ganti begitu udah dapet info dari tim internship
ROBOTS = [
    {
        "robot_id_str": "robot-1",   # bebas, ini id internal buat nandain robot di script
        "ip": "127.0.0.1",       # GANTI sesuai IP robot yang sebenarnya
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

# --- API number Robokit yang dipakai (semua di range Robot Status API 1000-1999) ---
API_ROBOT_INFO = 1000
API_ROBOT_LOC = 1004
API_ROBOT_BATTERY = 1007
API_ROBOT_SPEED = 1005
API_ROBOT_IMU = 1014
API_ROBOT_MOTOR = 1040
API_ROBOT_CONTROL_LOCK = 1060
API_ROBOT_TASK = 1020
