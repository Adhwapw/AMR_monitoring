<?php
// =========================================================
// config/database.php
// Koneksi database pakai PDO
// =========================================================

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
