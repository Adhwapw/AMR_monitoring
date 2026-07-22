<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json");

$pdo = get_db_connection();

$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : null;
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;
$page = max(1, (int)($_GET["page"] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$rows = get_motor_logs($pdo, $robotId, $startDate, $endDate, $perPage, $offset);
$totalCount = count_motor_logs($pdo, $robotId, $startDate, $endDate);

$result = array_map(function ($row) {
    return [
        "id" => (int)$row["id"],
        "logged_at" => format_datetime($row["logged_at"]),
        "vehicle_id" => $row["vehicle_id"] ?? $row["robot_id_str"],
        "motor_name" => $row["motor_name"] ?? "-",
        "speed" => format_number($row["speed"]),
        "position" => format_number($row["position"]),
        "current" => format_number($row["current"], 2),
        "temperature" => format_number($row["temperature"], 1),
        "err" => $row["err"] ? "Ya (" . $row["error_code"] . ")" : "Tidak",
        "err_flag" => (bool)$row["err"],
        "stop" => $row["stop"] ? "Ya" : "Tidak",
    ];
}, $rows);

echo json_encode(["rows" => $result, "count" => count($result), "total" => $totalCount]);
