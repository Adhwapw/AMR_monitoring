# =========================================================
# db.py
# Modul koneksi MySQL dan fungsi insert per tabel
# =========================================================

import mysql.connector
from mysql.connector import Error as MySQLError
import json
from datetime import datetime

from config import DB_CONFIG


def get_connection():
    """Bikin koneksi baru ke MySQL. Dipanggil tiap mau insert biar konsisten (bisa dioptimasi pakai pool nanti)."""
    return mysql.connector.connect(**DB_CONFIG)


def get_or_create_robot(conn, robot_id_str: str, ip: str, port: int) -> int:
    """
    Cek robot udah ada di tabel robots atau belum berdasarkan robot_id_str.
    Kalau belum ada, insert baris baru minimal (ip dan port doang).
    Return id internal (PK) robot itu.
    """
    cursor = conn.cursor()
    cursor.execute("SELECT id FROM robots WHERE robot_id_str = %s", (robot_id_str,))
    row = cursor.fetchone()
    if row:
        cursor.close()
        return row[0]

    cursor.execute(
        """
        INSERT INTO robots (robot_id_str, current_ip, port, is_active)
        VALUES (%s, %s, %s, 1)
        """,
        (robot_id_str, ip, port),
    )
    conn.commit()
    new_id = cursor.lastrowid
    cursor.close()
    return new_id


def update_robot_info(conn, robot_pk: int, info: dict):
    """Update kolom info statis robot dari response API 1000 (robot_status_info)."""
    if not info:
        return
    cursor = conn.cursor()
    cursor.execute(
        """
        UPDATE robots
        SET vehicle_id = %s,
            model = %s,
            version = %s,
            dsp_version = %s,
            map_version = %s,
            current_map = %s,
            last_info_synced_at = %s
        WHERE id = %s
        """,
        (
            info.get("vehicle_id"),
            info.get("model"),
            info.get("version"),
            info.get("dsp_version"),
            info.get("map_version"),
            info.get("current_map"),
            datetime.now(),
            robot_pk,
        ),
    )
    conn.commit()
    cursor.close()


def insert_status_log(conn, robot_pk: int, logged_at: datetime, battery: dict, loc: dict, speed: dict = None):
    """Gabungin data baterai (1007), lokasi (1004), dan speed keseluruhan (1005) jadi satu baris di robot_status_logs."""
    battery = battery or {}
    loc = loc or {}
    speed = speed or {}
    raw = {"battery": battery, "location": loc, "speed": speed}

    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO robot_status_logs (
            robot_id, logged_at,
            battery_level, battery_temp, charging, voltage, `current`,
            max_charge_voltage, max_charge_current, manual_charge, auto_charge, battery_cycle,
            pos_x, pos_y, angle, loc_confidence, current_station, last_station, loc_method,
            vx, vy, w, is_stop,
            raw_json
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """,
        (
            robot_pk, logged_at,
            battery.get("battery_level"), battery.get("battery_temp"), battery.get("charging"),
            battery.get("voltage"), battery.get("current"),
            battery.get("max_charge_voltage"), battery.get("max_charge_current"),
            battery.get("manual_charge"), battery.get("auto_charge"), battery.get("battery_cycle"),
            loc.get("x"), loc.get("y"), loc.get("angle"), loc.get("confidence"),
            loc.get("current_station"), loc.get("last_station"), loc.get("loc_method"),
            speed.get("vx"), speed.get("vy"), speed.get("w"), speed.get("is_stop"),
            json.dumps(raw),
        ),
    )
    conn.commit()
    cursor.close()


def insert_motor_logs(conn, robot_pk: int, logged_at: datetime, motor_response: dict):
    """Insert satu baris per motor dari response API 1040 (robot_status_motor)."""
    motor_list = (motor_response or {}).get("motor_info", [])
    if not motor_list:
        return

    cursor = conn.cursor()
    for motor in motor_list:
        cursor.execute(
            """
            INSERT INTO robot_motor_logs (
                robot_id, logged_at, motor_name, motor_type, can_router, can_id,
                position, speed, `current`, voltage, stop, error_code, err, emc,
                temperature, encoder, passive, calib
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """,
            (
                robot_pk, logged_at, motor.get("motor_name"), motor.get("type"),
                motor.get("can_router"), motor.get("can_id"),
                motor.get("position"), motor.get("speed"), motor.get("current"), motor.get("voltage"),
                motor.get("stop"), motor.get("error_code"), motor.get("err"), motor.get("emc"),
                motor.get("temperature"), motor.get("encoder"), motor.get("passive"), motor.get("calib"),
            ),
        )
    conn.commit()
    cursor.close()


def insert_imu_log(conn, robot_pk: int, logged_at: datetime, imu: dict):
    """Insert data IMU dari API 1014 (robot_status_imu)."""
    if not imu:
        return
    header = imu.get("imu_header", {})
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO robot_imu_logs (
            robot_id, logged_at, data_nsec, pub_nsec, seq,
            yaw, roll, pitch, acc_x, acc_y, acc_z, rot_x, rot_y, rot_z,
            qx, qy, qz, qw
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """,
        (
            robot_pk, logged_at, header.get("data_nsec"), header.get("pub_nsec"), header.get("seq"),
            imu.get("yaw"), imu.get("roll"), imu.get("pitch"),
            imu.get("acc_x"), imu.get("acc_y"), imu.get("acc_z"),
            imu.get("rot_x"), imu.get("rot_y"), imu.get("rot_z"),
            imu.get("QX"), imu.get("QY"), imu.get("QZ"), imu.get("QW"),
        ),
    )
    conn.commit()
    cursor.close()


def insert_control_lock_log(conn, robot_pk: int, logged_at: datetime, lock: dict):
    """Insert status kontrol dari API 1060 (robot_status_current_lock)."""
    if not lock:
        return
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO robot_control_lock_logs (
            robot_id, logged_at, locked, control_ip, control_port,
            control_type, nick_name, locked_time_t, `desc`
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """,
        (
            robot_pk, logged_at, lock.get("locked"), lock.get("ip"), lock.get("port"),
            lock.get("type"), lock.get("nick_name"), lock.get("time_t"), lock.get("desc"),
        ),
    )
    conn.commit()
    cursor.close()


def insert_task_log(conn, robot_pk: int, logged_at: datetime, task: dict):
    """Insert status task/navigasi dari API 1020 (robot_status_task)."""
    if not task:
        return
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO robot_task_logs (
            robot_id, logged_at, task_status, task_type, target_id,
            target_point, finished_path, unfinished_path, move_status_info,
            containers, raw_json
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """,
        (
            robot_pk, logged_at, task.get("task_status"), task.get("task_type"), task.get("target_id"),
            json.dumps(task.get("target_point")) if task.get("target_point") is not None else None,
            json.dumps(task.get("finished_path")) if task.get("finished_path") is not None else None,
            json.dumps(task.get("unfinished_path")) if task.get("unfinished_path") is not None else None,
            task.get("move_status_info"),
            json.dumps(task.get("containers")) if task.get("containers") is not None else None,
            json.dumps(task),
        ),
    )
    conn.commit()
    cursor.close()


def insert_connection_log(conn, robot_pk: int, event_type: str, message: str = None):
    """Catat event koneksi (connected / disconnected / parse_error / timeout)."""
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO connection_logs (robot_id, event_type, message, occurred_at)
        VALUES (%s, %s, %s, %s)
        """,
        (robot_pk, event_type, message, datetime.now()),
    )
    conn.commit()
    cursor.close()
