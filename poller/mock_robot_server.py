# =========================================================
# mock_robot_server.py
# Server TCP palsu buat simulasiin robot AMR, khusus testing poller
# tanpa perlu koneksi ke robot asli. JANGAN dipakai di production.
# =========================================================

import socket
import struct
import json
import threading

HEADER_FORMAT = ">BBHIH6x"
HEADER_SIZE = 16
SYNC_BYTE = 0x5A
VERSION_BYTE = 0x01

HOST = "127.0.0.1"
PORT = 19205

# Response palsu per API number, dicontek dari dokumentasi Robokit
FAKE_RESPONSES = {
    1000: {  # robot_status_info
        "id": "50307218-7527da15-9bb32e57-0668b14d",
        "vehicle_id": "AMR-TEST-01",
        "version": "v1.1.0",
        "model": "S1",
        "dsp_version": "v1.2.2",
        "map_version": "v1.0.0",
        "model_version": "v1.1.0",
        "netprotocol_version": "v1.2.0",
        "current_map": "map_gudang_a",
    },
    1007: {  # robot_status_battery
        "auto_charge": False,
        "battery_cycle": 9,
        "battery_level": 0.87,
        "battery_temp": 35,
        "battery_user_data": "",
        "charging": False,
        "current": 2,
        "manual_charge": False,
        "max_charge_current": 5,
        "max_charge_voltage": 48,
        "ret_code": 0,
        "voltage": 24.5,
    },
    1004: {  # robot_status_loc
        "angle": -0.0064,
        "confidence": 0.637,
        "x": 3.5069,
        "y": 0.0687,
        "current_station": "LM1",
        "last_station": "LM2",
        "loc_method": 0,
    },
    1005: {  # robot_status_speed
        "vx": 0.41,
        "vy": 0.0,
        "w": 0.02,
        "steer": 0,
        "spin": 0,
        "is_stop": False,
        "ret_code": 0,
    },
    1040: {  # robot_status_motor
        "motor_info": [
            {
                "motor_name": "motor_left",
                "type": 1,
                "can_router": 1,
                "can_id": 1,
                "position": 0,
                "speed": 0.42,
                "current": 1.1,
                "voltage": 24.0,
                "stop": False,
                "error_code": 0,
                "err": False,
                "emc": False,
                "temperature": 30,
                "encoder": 12345,
                "passive": False,
                "calib": True,
            },
            {
                "motor_name": "motor_right",
                "type": 1,
                "can_router": 1,
                "can_id": 2,
                "position": 0,
                "speed": 0.40,
                "current": 1.0,
                "voltage": 24.0,
                "stop": False,
                "error_code": 0,
                "err": False,
                "emc": False,
                "temperature": 30,
                "encoder": 12300,
                "passive": False,
                "calib": True,
            },
        ],
        "ret_code": 0,
    },
    1014: {  # robot_status_imu
        "imu_header": {"data_nsec": "16704707855595", "pub_nsec": "16704707855637", "seq": "0"},
        "yaw": -3.128697633743291,
        "roll": 0,
        "pitch": 0,
        "acc_x": 0,
        "acc_y": 0,
        "acc_z": 0,
        "rot_x": 0,
        "rot_y": 0,
        "rot_z": 0,
        "rot_off_x": 0,
        "rot_off_y": 0,
        "rot_off_z": 0,
        "QX": 0,
        "QY": 0,
        "QZ": 0,
        "QW": 0,
        "ret_code": 0,
    },
    1060: {  # robot_status_current_lock
        "locked": False,
    },
    1020: {  # robot_status_task
        "task_status": 2,
        "task_type": 3,
        "target_id": "LM3",
        "finished_path": ["LM1", "LM2"],
        "unfinished_path": ["LM3", "LM4"],
        "move_status_info": "",
        "ret_code": 0,
    },
}


def handle_client(conn: socket.socket, addr):
    print(f"Klien konek dari {addr}")
    try:
        while True:
            header_bytes = conn.recv(HEADER_SIZE)
            if not header_bytes:
                break
            sync, version, seq, data_len, api_number = struct.unpack(HEADER_FORMAT, header_bytes)

            if data_len > 0:
                conn.recv(data_len)  # buang body request kalau ada, mock ini nggak butuh isinya

            response_data = FAKE_RESPONSES.get(api_number, {})
            body = json.dumps(response_data).encode("utf-8")
            resp_api_number = api_number + 10000  # pola respons Robokit: req + 10000 = res
            resp_header = struct.pack(HEADER_FORMAT, SYNC_BYTE, VERSION_BYTE, seq, len(body), resp_api_number)

            conn.sendall(resp_header + body)
            print(f"Balas API {api_number} -> {resp_api_number}, {len(body)} byte")
    except (ConnectionResetError, BrokenPipeError):
        pass
    finally:
        conn.close()
        print(f"Klien {addr} disconnect")


def main():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server.bind((HOST, PORT))
    server.listen(5)
    print(f"Mock robot server jalan di {HOST}:{PORT}, Ctrl+C buat berhenti")

    try:
        while True:
            conn, addr = server.accept()
            threading.Thread(target=handle_client, args=(conn, addr), daemon=True).start()
    except KeyboardInterrupt:
        print("\nMock server dihentikan.")
    finally:
        server.close()


if __name__ == "__main__":
    main()
