# =========================================================
# push_listener.py
# Proses TERPISAH dari poller.py, khusus buat dengerin fitur
# Robot Push (port 19301) secara terus-menerus.
#
# Jalanin ini bareng poller.py di terminal yang beda, dua-duanya
# aman jalan bersamaan soalnya beda koneksi TCP dan beda tabel.
#
# Cara pakai:
#   python push_listener.py
# =========================================================

import socket
import struct
import json
import time
from datetime import datetime

import config
import db

HEADER_FORMAT = ">BBHIH6x"
HEADER_SIZE = 16
SYNC_BYTE = 0x5A
VERSION_BYTE = 0x01


def build_request(api_number, data: dict = None, seq: int = 1) -> bytes:
    body = json.dumps(data).encode("utf-8") if data else b""
    header = struct.pack(HEADER_FORMAT, SYNC_BYTE, VERSION_BYTE, seq, len(body), api_number)
    return header + body


def recv_exact(sock: socket.socket, n: int) -> bytes:
    chunks = []
    remaining = n
    while remaining > 0:
        chunk = sock.recv(remaining)
        if not chunk:
            raise ConnectionError("Koneksi terputus sebelum data lengkap diterima")
        chunks.append(chunk)
        remaining -= len(chunk)
    return b"".join(chunks)


def read_message(sock: socket.socket):
    header_bytes = recv_exact(sock, HEADER_SIZE)
    sync, version, seq, data_len, api_number = struct.unpack(HEADER_FORMAT, header_bytes)
    if sync != SYNC_BYTE:
        raise ValueError(f"Sync byte nggak sesuai: {sync:#x}")
    if data_len == 0:
        return api_number, {}
    body_bytes = recv_exact(sock, data_len)
    return api_number, json.loads(body_bytes.decode("utf-8"))


def listen_to_robot(robot_pk: int, ip: str):
    """Konek ke port push, minta konfigurasi, terus dengerin terus-menerus."""
    conn_db = db.get_connection()
    sock = None
    try:
        sock = socket.create_connection((ip, config.PUSH_PORT), timeout=10)
        db.insert_connection_log(conn_db, robot_pk, "connected", "push listener konek")
        print(f"[{datetime.now()}] Konek ke {ip}:{config.PUSH_PORT}")

        # Minta konfigurasi interval push
        sock.sendall(build_request(config.PUSH_CONFIG_API, data={"interval": config.PUSH_INTERVAL_MS}))
        api_num, resp = read_message(sock)
        if resp.get("ret_code", 0) != 0:
            print(f"[{datetime.now()}] PERINGATAN konfigurasi push: {resp}")
        else:
            print(f"[{datetime.now()}] Konfigurasi push OK, interval {config.PUSH_INTERVAL_MS}ms")

        # Dengerin push data terus-menerus
        while True:
            api_num, push_data = read_message(sock)
            db.insert_push_log(conn_db, robot_pk, datetime.now(), push_data)

    except (OSError, ConnectionError, ValueError, json.JSONDecodeError) as e:
        print(f"[{datetime.now()}] Push listener terputus: {e}")
        db.insert_connection_log(conn_db, robot_pk, "timeout", f"push listener error: {e}")
    finally:
        if sock:
            try:
                sock.close()
            except OSError:
                pass
        conn_db.close()


def main():
    print("Push listener AMR dimulai. Tekan Ctrl+C buat berhenti.")

    conn_db = db.get_connection()
    robot_pks = {}
    for robot in config.ROBOTS:
        pk = db.get_or_create_robot(conn_db, robot["robot_id_str"], robot["ip"], robot["port"])
        robot_pks[robot["robot_id_str"]] = pk
    conn_db.close()

    # MVP: baru nangani satu robot pertama di config.ROBOTS.
    # Kalau ada beberapa robot, jalanin beberapa instance script ini
    # dengan config berbeda, atau kembangin jadi multi-thread nanti.
    robot = config.ROBOTS[0]
    pk = robot_pks[robot["robot_id_str"]]

    try:
        while True:
            listen_to_robot(pk, robot["ip"])
            print(f"[{datetime.now()}] Nyoba konek ulang dalam {config.RETRY_DELAY_SECONDS} detik...")
            time.sleep(config.RETRY_DELAY_SECONDS)
    except KeyboardInterrupt:
        print("\nPush listener dihentikan manual.")


if __name__ == "__main__":
    main()
