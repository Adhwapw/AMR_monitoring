# Modul koneksi MySQL dan fungsi insert per tabel
import mysql.connector
from mysql.connector import Error as MySQLError
import json
from datetime import datetime
from typing import Optional

from config import DB_CONFIG


def get_connection():
    """Bikin koneksi baru ke MySQL. Dipanggil tiap mau insert biar konsisten (bisa dioptimasi pakai pool nanti)."""
    return mysql.connector.connect(**DB_CONFIG)


def _execute(conn, sql: str, params: tuple) -> int:
    """Jalanin satu query INSERT/UPDATE. Balikin cursor.lastrowid (berguna abis INSERT baris baru)."""
    cursor = conn.cursor()
    cursor.execute(sql, params)
    last_row_id = cursor.lastrowid
    cursor.close()
    return last_row_id


def _json_or_none(value) -> Optional[str]:
    """Serialize value ke JSON string, atau None kalau value-nya emang None. Buat kolom JSON yang opsional."""
    if value is None:
        return None
    return json.dumps(value)

# robots
def _find_robot_id_by_str(conn, robot_id_str: str) -> Optional[int]:
    """Cari id internal robot berdasarkan robot_id_str. None kalau belum terdaftar."""
    cursor = conn.cursor()
    cursor.execute("SELECT id FROM robots WHERE robot_id_str = %s", (robot_id_str,))
    row = cursor.fetchone()
    cursor.close()
    return row[0] if row else None


def _insert_new_robot(conn, robot_id_str: str, ip: str, port: int) -> int:
    """Daftarin robot baru ke tabel robots, balikin id internal (PK) yang baru dibuat."""
    sql = """
        INSERT INTO robots (robot_id_str, current_ip, port, is_active)
        VALUES (%s, %s, %s, 1)
    """
    new_id = _execute(conn, sql, (robot_id_str, ip, port))
    conn.commit()
    return new_id


def get_or_create_robot(conn, robot_id_str: str, ip: str, port: int) -> int:
    """Cari robot berdasarkan robot_id_str; kalau belum ada, daftarin baru. Selalu balikin id internal (PK)."""
    existing_id = _find_robot_id_by_str(conn, robot_id_str)
    if existing_id is not None:
        return existing_id

    return _insert_new_robot(conn, robot_id_str, ip, port)


def update_robot_info(conn, robot_pk: int, info: dict):
    """Update kolom info statis robot dari response API 1000 (robot_status_info)."""
    if not info:
        return

    sql = """
        UPDATE robots
        SET vehicle_id = %s,
            model = %s,
            version = %s,
            dsp_version = %s,
            map_version = %s,
            current_map = %s,
            last_info_synced_at = %s
        WHERE id = %s
    """
    params = (
        info.get("vehicle_id"),
        info.get("model"),
        info.get("version"),
        info.get("dsp_version"),
        info.get("map_version"),
        info.get("current_map"),
        datetime.now(),
        robot_pk,
    )
    _execute(conn, sql, params)
    conn.commit()


# ---------------------------------------------------------
# robot_status_logs (gabungan baterai + lokasi + speed keseluruhan)
# ---------------------------------------------------------

def insert_status_log(conn, robot_pk: int, logged_at: datetime, battery: dict, loc: dict, speed: dict = None):
    """Gabungin data baterai (1007), lokasi (1004), dan speed keseluruhan (1005) jadi satu baris di robot_status_logs."""
    battery = battery or {}
    loc = loc or {}
    speed = speed or {}
    raw_json_combined = {"battery": battery, "location": loc, "speed": speed}

    sql = """
        INSERT INTO robot_status_logs (
            robot_id, logged_at,
            battery_level, battery_temp, charging, voltage, `current`,
            max_charge_voltage, max_charge_current, manual_charge, auto_charge, battery_cycle,
            pos_x, pos_y, angle, loc_confidence, current_station, last_station, loc_method,
            vx, vy, w, is_stop,
            raw_json
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    params = (
        robot_pk, logged_at,
        battery.get("battery_level"), battery.get("battery_temp"), battery.get("charging"),
        battery.get("voltage"), battery.get("current"),
        battery.get("max_charge_voltage"), battery.get("max_charge_current"),
        battery.get("manual_charge"), battery.get("auto_charge"), battery.get("battery_cycle"),
        loc.get("x"), loc.get("y"), loc.get("angle"), loc.get("confidence"),
        loc.get("current_station"), loc.get("last_station"), loc.get("loc_method"),
        speed.get("vx"), speed.get("vy"), speed.get("w"), speed.get("is_stop"),
        json.dumps(raw_json_combined),
    )
    _execute(conn, sql, params)
    conn.commit()


# ---------------------------------------------------------
# robot_motor_logs (satu baris per motor per siklus polling)
# ---------------------------------------------------------

def _build_motor_log_params(robot_pk: int, logged_at: datetime, motor: dict) -> tuple:
    """Susun satu tuple parameter buat insert satu baris motor. Dipisah biar insert_motor_logs fokus ke looping-nya."""
    return (
        robot_pk, logged_at, motor.get("motor_name"), motor.get("type"),
        motor.get("can_router"), motor.get("can_id"),
        motor.get("position"), motor.get("speed"), motor.get("current"), motor.get("voltage"),
        motor.get("stop"), motor.get("error_code"), motor.get("err"), motor.get("emc"),
        motor.get("temperature"), motor.get("encoder"), motor.get("passive"), motor.get("calib"),
    )


def insert_motor_logs(conn, robot_pk: int, logged_at: datetime, motor_response: dict):
    """Insert satu baris per motor dari response API 1040 (robot_status_motor)."""
    motor_list = (motor_response or {}).get("motor_info", [])
    if not motor_list:
        return

    sql = """
        INSERT INTO robot_motor_logs (
            robot_id, logged_at, motor_name, motor_type, can_router, can_id,
            position, speed, `current`, voltage, stop, error_code, err, emc,
            temperature, encoder, passive, calib
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    for motor in motor_list:
        params = _build_motor_log_params(robot_pk, logged_at, motor)
        _execute(conn, sql, params)

    conn.commit()  # satu commit buat semua motor di siklus ini, bukan per motor


# ---------------------------------------------------------
# robot_imu_logs
# ---------------------------------------------------------

def insert_imu_log(conn, robot_pk: int, logged_at: datetime, imu: dict):
    """Insert data IMU dari API 1014 (robot_status_imu)."""
    if not imu:
        return

    imu_header_data = imu.get("imu_header", {})
    sql = """
        INSERT INTO robot_imu_logs (
            robot_id, logged_at, data_nsec, pub_nsec, seq,
            yaw, roll, pitch, acc_x, acc_y, acc_z, rot_x, rot_y, rot_z,
            qx, qy, qz, qw
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    params = (
        robot_pk, logged_at,
        imu_header_data.get("data_nsec"), imu_header_data.get("pub_nsec"), imu_header_data.get("seq"),
        imu.get("yaw"), imu.get("roll"), imu.get("pitch"),
        imu.get("acc_x"), imu.get("acc_y"), imu.get("acc_z"),
        imu.get("rot_x"), imu.get("rot_y"), imu.get("rot_z"),
        imu.get("QX"), imu.get("QY"), imu.get("QZ"), imu.get("QW"),
    )
    _execute(conn, sql, params)
    conn.commit()


# ---------------------------------------------------------
# robot_control_lock_logs
# ---------------------------------------------------------

def insert_control_lock_log(conn, robot_pk: int, logged_at: datetime, lock: dict):
    """Insert status kontrol dari API 1060 (robot_status_current_lock)."""
    if not lock:
        return

    sql = """
        INSERT INTO robot_control_lock_logs (
            robot_id, logged_at, locked, control_ip, control_port,
            control_type, nick_name, locked_time_t, `desc`
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    params = (
        robot_pk, logged_at, lock.get("locked"), lock.get("ip"), lock.get("port"),
        lock.get("type"), lock.get("nick_name"), lock.get("time_t"), lock.get("desc"),
    )
    _execute(conn, sql, params)
    conn.commit()


# ---------------------------------------------------------
# robot_task_logs
# ---------------------------------------------------------

def insert_task_log(conn, robot_pk: int, logged_at: datetime, task: dict):
    """Insert status task/navigasi dari API 1020 (robot_status_task)."""
    if not task:
        return

    sql = """
        INSERT INTO robot_task_logs (
            robot_id, logged_at, task_status, task_type, target_id,
            target_point, finished_path, unfinished_path, move_status_info,
            containers, raw_json
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    params = (
        robot_pk, logged_at, task.get("task_status"), task.get("task_type"), task.get("target_id"),
        _json_or_none(task.get("target_point")),
        _json_or_none(task.get("finished_path")),
        _json_or_none(task.get("unfinished_path")),
        task.get("move_status_info"),
        _json_or_none(task.get("containers")),
        json.dumps(task),
    )
    _execute(conn, sql, params)
    conn.commit()


# ---------------------------------------------------------
# robot_stations (upsert, data statis nggak perlu tabel log per waktu)
# ---------------------------------------------------------

def _build_station_upsert_params(robot_pk: int, station: dict, map_name: Optional[str], synced_at_timestamp: datetime) -> tuple:
    """Susun satu tuple parameter buat upsert satu station. Dipisah biar upsert_stations fokus ke looping-nya."""
    return (
        robot_pk, station.get("id"), station.get("type"),
        station.get("x"), station.get("y"), station.get("r"),
        station.get("desc"), map_name, synced_at_timestamp,
    )


def upsert_stations(conn, robot_pk: int, stations: list, map_name: str = None):
    """
    Simpen/update daftar station dari API 1301 (robot_status_station).
    Pakai upsert (INSERT ... ON DUPLICATE KEY UPDATE) biar aman dipanggil berkali-kali
    tanpa bikin duplikat, soalnya data ini nggak perlu tabel log per waktu.
    """
    if not stations:
        return

    sql = """
        INSERT INTO robot_stations (robot_id, station_id, station_type, x, y, r, description, map_name, synced_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            station_type = VALUES(station_type),
            x = VALUES(x),
            y = VALUES(y),
            r = VALUES(r),
            description = VALUES(description),
            map_name = VALUES(map_name),
            synced_at = VALUES(synced_at)
    """
    synced_at_timestamp = datetime.now()
    for station in stations:
        params = _build_station_upsert_params(robot_pk, station, map_name, synced_at_timestamp)
        _execute(conn, sql, params)

    conn.commit()  # satu commit buat semua station, bukan per station


# ---------------------------------------------------------
# robot_push_logs
# ---------------------------------------------------------

def insert_push_log(conn, robot_pk: int, logged_at: datetime, push_data: dict):
    """Insert satu data push dari API 19301 (robot_push). Fokus ke field yang CUMA ada di push."""
    if not push_data:
        return

    sql = """
        INSERT INTO robot_push_logs (
            robot_id, logged_at, odo, today_odo, blocked, slowed,
            block_reason, slow_reason, target_dist, raw_json
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    params = (
        robot_pk, logged_at,
        push_data.get("odo"), push_data.get("today_odo"),
        push_data.get("blocked"), push_data.get("slowed"),
        push_data.get("block_reason"), push_data.get("slow_reason"),
        push_data.get("target_dist"),
        json.dumps(push_data),
    )
    _execute(conn, sql, params)
    conn.commit()


# ---------------------------------------------------------
# connection_logs
# ---------------------------------------------------------

def insert_connection_log(conn, robot_pk: int, event_type: str, message: str = None):
    """Catat event koneksi (connected / disconnected / parse_error / timeout)."""
    sql = """
        INSERT INTO connection_logs (robot_id, event_type, message, occurred_at)
        VALUES (%s, %s, %s, %s)
    """
    params = (robot_pk, event_type, message, datetime.now())
    _execute(conn, sql, params)
    conn.commit()