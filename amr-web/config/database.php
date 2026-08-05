<?php
// =========================================================
// config/database.php
// Koneksi database pakai PDO
// =========================================================

// Set timezone eksplisit, biar semua fungsi date()/time()/strtotime() di PHP
// konsisten sama jam laptop (WIB), nggak ikut default php.ini yang kadang
// masih di-set UTC. Tanpa ini, timestamp yang dibuat PHP (misal started_at
// sesi) bisa selisih beberapa jam dari timestamp yang dibuat poller Python
// (yang otomatis pakai jam sistem lokal).
date_default_timezone_set("Asia/Jakarta");

$DB_HOST = "localhost";
$DB_PORT = "3306";
$DB_NAME = "robot_amr_logging";
$DB_USER = "root";
$DB_PASS = ""; // isi kalau root MySQL kamu pakai password

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
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
