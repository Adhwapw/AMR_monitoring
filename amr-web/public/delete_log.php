<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Method tidak diizinkan"]);
    exit;
}

$id = isset($_POST["id"]) ? (int)$_POST["id"] : null;
$type = $_POST["type"] ?? null;

if (!$id || !in_array($type, ["status", "motor"], true)) {
    echo json_encode(["success" => false, "error" => "Parameter tidak lengkap"]);
    exit;
}

$pdo = get_db_connection();

try {
    if ($type === "status") {
        $affected = delete_single_status_log($pdo, $id);
    } else {
        $affected = delete_single_motor_log($pdo, $id);
    }

    if ($affected > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Data tidak ditemukan"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
