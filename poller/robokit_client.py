# robokit_client.py
# Modul buat komunikasi TCP ke robot pakai protokol Robokit
# Header 16 byte: sync(1) + version(1) + seq(2) + data_len(4) + api_num(2) + reserved(6)
import socket
import struct
import json
from typing import NamedTuple, Optional

HEADER_FORMAT = ">BBHIH6x"   # big-endian: B=uint8, H=uint16, I=uint32, 6x=padding 6 byte
HEADER_SIZE = 16
SYNC_BYTE = 0x5A
VERSION_BYTE = 0x01


class RobokitError(Exception):
    """Dipakai buat semua error yang berhubungan sama komunikasi ke robot."""
    pass


class RobokitHeader(NamedTuple):
    """Hasil parsing header 16-byte, biar nggak kerja sama tuple mentah tak bernama."""
    sync: int
    version: int
    seq: int
    data_length: int
    api_number: int



# Membangun request
def _encode_body(data: Optional[dict]) -> bytes:
    """Serialize dict jadi JSON bytes. Dict kosong/None -> body kosong (data_len = 0)."""
    if not data:
        return b""
    return json.dumps(data).encode("utf-8")


def build_request(api_number: int, data: Optional[dict] = None, seq: int = 1) -> bytes:
    """Bikin request Robokit lengkap: header 16-byte + body JSON (kalau ada)."""
    body = _encode_body(data)
    header = struct.pack(HEADER_FORMAT, SYNC_BYTE, VERSION_BYTE, seq, len(body), api_number)
    return header + body


# Membaca response
def _read_exact_bytes(sock: socket.socket, num_bytes: int) -> bytes:
    """Baca persis num_bytes byte dari socket, ngelempar error kalau koneksi putus di tengah jalan."""
    chunks = []
    bytes_remaining = num_bytes
    while bytes_remaining > 0:
        chunk = sock.recv(bytes_remaining)
        if not chunk:
            raise RobokitError("Koneksi terputus sebelum data lengkap diterima")
        chunks.append(chunk)
        bytes_remaining -= len(chunk)
    return b"".join(chunks)


def _read_header(sock: socket.socket) -> RobokitHeader:
    """Baca dan validasi header 16-byte. Guard clause: langsung raise kalau sync byte salah."""
    header_bytes = _read_exact_bytes(sock, HEADER_SIZE)
    sync, version, seq, data_length, api_number = struct.unpack(HEADER_FORMAT, header_bytes)

    if sync != SYNC_BYTE:
        raise RobokitError(f"Sync byte tidak sesuai, dapat {sync:#x}, harusnya {SYNC_BYTE:#x}")

    return RobokitHeader(sync, version, seq, data_length, api_number)


def _read_body(sock: socket.socket, header: RobokitHeader) -> dict:
    """Baca body JSON sesuai data_length di header. Guard clause: data_len 0 -> dict kosong."""
    if header.data_length == 0:
        return {}

    body_bytes = _read_exact_bytes(sock, header.data_length)
    try:
        return json.loads(body_bytes.decode("utf-8"))
    except json.JSONDecodeError as e:
        raise RobokitError(f"Gagal parsing JSON dari response API {header.api_number}: {e}")


def read_response(sock: socket.socket) -> dict:
    """Baca satu response Robokit lengkap: header dulu, baru body sesuai panjangnya."""
    header = _read_header(sock)
    return _read_body(sock, header)


# Fungsi dipakai langsung dari poller.py
def send_request(sock: socket.socket, api_number: int, data: Optional[dict] = None, seq: int = 1) -> dict:
    """Kirim satu request dan langsung tunggu responsenya. Dipakai buat query request/response biasa."""
    request_bytes = build_request(api_number, data=data, seq=seq)
    sock.sendall(request_bytes)
    return read_response(sock)


def open_connection(ip: str, port: int, timeout: float = 3.0) -> socket.socket:
    """Buka koneksi TCP baru ke robot."""
    return socket.create_connection((ip, port), timeout=timeout)