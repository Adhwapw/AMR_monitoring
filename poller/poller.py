# =========================================================
# poller.py
# Script utama, polling ke robot AMR secara berkala dan nyimpen
# hasilnya ke MySQL
# =========================================================

import time
import traceback
from datetime import datetime

import config
import db
import robokit_client as rk


def poll_one_robot(robot_pk: int, ip: str, port: int):
    """Satu siklus polling penuh ke satu robot: buka koneksi, tanya semua API, simpen ke DB, tutup koneksi."""
    conn = db.get_connection()
    sock = None
    try:
        sock = rk.open_connection(ip, port, timeout=config.TCP_TIMEOUT_SECONDS)
        db.insert_connection_log(conn, robot_pk, "connected")

        logged_at = datetime.now()

        # 1. Info robot (jarang berubah, tapi tetap dipoll biar current_map dkk keupdate)
        info = rk.send_request(sock, config.API_ROBOT_INFO)
        db.update_robot_info(conn, robot_pk, info)

        # 2. Baterai
        battery = rk.send_request(sock, config.API_ROBOT_BATTERY)

        # 3. Lokasi
        loc = rk.send_request(sock, config.API_ROBOT_LOC)

        # 3b. Kecepatan keseluruhan robot (beda dari speed per motor)
        speed = rk.send_request(sock, config.API_ROBOT_SPEED)

        db.insert_status_log(conn, robot_pk, logged_at, battery, loc, speed)

        # 4. Motor (semua motor, nggak filter motor_names)
        motor = rk.send_request(sock, config.API_ROBOT_MOTOR)
        db.insert_motor_logs(conn, robot_pk, logged_at, motor)

        # 5. IMU
        imu = rk.send_request(sock, config.API_ROBOT_IMU)
        db.insert_imu_log(conn, robot_pk, logged_at, imu)

        # 6. Control lock
        lock = rk.send_request(sock, config.API_ROBOT_CONTROL_LOCK)
        db.insert_control_lock_log(conn, robot_pk, logged_at, lock)

        # 7. Status task/navigasi (penting buat konteks konsumsi baterai)
        task = rk.send_request(sock, config.API_ROBOT_TASK)
        db.insert_task_log(conn, robot_pk, logged_at, task)

        print(f"[{logged_at}] OK polling robot_pk={robot_pk} ({ip}:{port})")

    except (rk.RobokitError, OSError) as e:
        # OSError nyakup socket timeout, connection refused, dll
        print(f"[{datetime.now()}] GAGAL polling robot_pk={robot_pk} ({ip}:{port}): {e}")
        db.insert_connection_log(conn, robot_pk, "timeout", str(e))

    except Exception as e:
        # Jaga-jaga error lain (misal parsing/insert), dicatat juga biar ketahuan
        print(f"[{datetime.now()}] ERROR TAK TERDUGA robot_pk={robot_pk}: {e}")
        traceback.print_exc()
        db.insert_connection_log(conn, robot_pk, "parse_error", str(e))

    finally:
        if sock:
            try:
                sock.close()
            except OSError:
                pass
        conn.close()


def sync_stations(robot_pk: int, ip: str, port: int):
    """
    Ambil daftar station/titik dari peta yang lagi dimuat robot (API 1301).
    Dipanggil sekali pas poller start, bukan tiap siklus, soalnya data ini
    statis dan cuma berubah kalau map robot diganti.
    """
    conn = db.get_connection()
    sock = None
    try:
        sock = rk.open_connection(ip, port, timeout=config.TCP_TIMEOUT_SECONDS)
        station_response = rk.send_request(sock, config.API_ROBOT_STATION_LIST)
        stations = station_response.get("stations", [])
        db.upsert_stations(conn, robot_pk, stations)
        print(f"[{datetime.now()}] Sync {len(stations)} station buat robot_pk={robot_pk}")
    except (rk.RobokitError, OSError) as e:
        print(f"[{datetime.now()}] Gagal sync station buat robot_pk={robot_pk}: {e}")
    finally:
        if sock:
            try:
                sock.close()
            except OSError:
                pass
        conn.close()


def main():
    print("Poller AMR dimulai. Tekan Ctrl+C buat berhenti.")

    conn = db.get_connection()
    robot_pks = {}
    for robot in config.ROBOTS:
        pk = db.get_or_create_robot(conn, robot["robot_id_str"], robot["ip"], robot["port"])
        robot_pks[robot["robot_id_str"]] = pk
    conn.close()

    # Sync daftar station sekali di awal (bukan tiap siklus polling)
    for robot in config.ROBOTS:
        pk = robot_pks[robot["robot_id_str"]]
        sync_stations(pk, robot["ip"], robot["port"])

    try:
        while True:
            for robot in config.ROBOTS:
                pk = robot_pks[robot["robot_id_str"]]
                poll_one_robot(pk, robot["ip"], robot["port"])
            time.sleep(config.POLL_INTERVAL_SECONDS)
    except KeyboardInterrupt:
        print("\nPoller dihentikan manual.")


if __name__ == "__main__":
    main()
