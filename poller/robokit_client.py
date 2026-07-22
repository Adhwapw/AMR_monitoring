# =========================================================
# robokit_client.py
# Modul buat komunikasi TCP ke robot pakai protokol Robokit
# Header 16 byte: sync(1) + version(1) + seq(2) + data_len(4) + api_num(2) + reserved(6)
# =========================================================

import socket
import struct
import json

HEADER_FORMAT = ">BBHIH6x"   # big-endian: B=uint8, H=uint16, I=uint32, 6x=padding 6 byte
HEADER_SIZE = 16
SYNC_BYTE = 0x5A
VERSION_BYTE = 0x01


class RobokitError(Exception):
    """Dipakai buat semua error yang berhubungan sama komunikasi ke robot."""
    pass


def build_request(api_number, data: dict = None, seq: int = 1) -> bytes:
    """
    Bikin request Robokit lengkap (header + body JSON kalau ada).
    Kalau data None atau kosong, request dikirim tanpa data area (data_len = 0).
    """
    if data:
        body = json.dumps(data).encode("utf-8")
    else:
        body = b""

    header = struct.pack(HEADER_FORMAT, SYNC_BYTE, VERSION_BYTE, seq, len(body), api_number)
    return header + body


def _recv_exact(sock: socket.socket, n: int) -> bytes:
    """Baca persis n byte dari socket, ngelempar error kalau koneksi putus di tengah jalan."""
    chunks = []
    remaining = n
    while remaining > 0:
        chunk = sock.recv(remaining)
        if not chunk:
            raise RobokitError("Koneksi terputus sebelum data lengkap diterima")
        chunks.append(chunk)
        remaining -= len(chunk)
    return b"".join(chunks)


def read_response(sock: socket.socket) -> dict:
    """
    Baca satu response Robokit lengkap dari socket.
    Return dict hasil parsing JSON body (atau {} kalau data_len = 0).
    """
    header_bytes = _recv_exact(sock, HEADER_SIZE)
    sync, version, seq, data_len, api_number = struct.unpack(HEADER_FORMAT, header_bytes)

    if sync != SYNC_BYTE:
        raise RobokitError(f"Sync byte tidak sesuai, dapat {sync:#x}, harusnya {SYNC_BYTE:#x}")

    if data_len == 0:
        return {}

    body_bytes = _recv_exact(sock, data_len)
    try:
        return json.loads(body_bytes.decode("utf-8"))
    except json.JSONDecodeError as e:
        raise RobokitError(f"Gagal parsing JSON dari response API {api_number}: {e}")


def send_request(sock: socket.socket, api_number: int, data: dict = None, seq: int = 1) -> dict:
    """Kirim satu request dan langsung tunggu responsenya. Dipakai buat query request/response biasa."""
    request_bytes = build_request(api_number, data=data, seq=seq)
    sock.sendall(request_bytes)
    return read_response(sock)


def open_connection(ip: str, port: int, timeout: float = 3.0) -> socket.socket:
    """Buka koneksi TCP baru ke robot."""
    sock = socket.create_connection((ip, port), timeout=timeout)
    return sock
