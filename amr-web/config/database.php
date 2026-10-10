<?php
// =========================================================
// config/database.php
// Koneksi database pakai PDO.
// Kredensial dibaca dari environment variable dulu (buat deploy
// di Vercel: set DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS di
// dashboard Vercel), fallback ke config lokal kalau nggak ada.
// =========================================================

// Set timezone eksplisit, biar semua fungsi date()/time()/strtotime() di PHP
// konsisten sama jam Jakarta (WIB). Tanpa ini, timestamp yang dibuat PHP
// (misal started_at sesi) bisa selisih beberapa jam dari timestamp yang
// dibuat poller Python (yang otomatis pakai jam sistem lokal).
date_default_timezone_set("Asia/Jakarta");

$DB_HOST = getenv("DB_HOST") ?: "localhost";
$DB_PORT = getenv("DB_PORT") ?: "3306";
$DB_NAME = getenv("DB_NAME") ?: "robot_amr_logging";
$DB_USER = getenv("DB_USER") ?: "root";
$DB_PASS = getenv("DB_PASS") ?: ""; // isi kalau root MySQL kamu pakai password

function get_db_connection(): PDO
{
    global $DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS;

    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Di production (Vercel) jangan bocorin detail error ke pengunjung
        if (getenv("VERCEL")) {
            http_response_code(500);
            die("Koneksi database gagal. Periksa environment variable DB_* di dashboard Vercel.");
        }
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
