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

$rows = get_status_logs($pdo, $robotId, $startDate, $endDate, $perPage, $offset);
$totalCount = count_status_logs($pdo, $robotId, $startDate, $endDate);

$result = array_map(function ($row) {
    return [
        "id" => (int)$row["id"],
        "logged_at" => format_datetime($row["logged_at"]),
        "vehicle_id" => $row["vehicle_id"] ?? $row["robot_id_str"],
        "battery_level" => $row["battery_level"] !== null ? format_number($row["battery_level"] * 100, 1) : "-",
        "charging" => $row["charging"] ? "Ya" : "Tidak",
        "pos_x" => format_number($row["pos_x"]),
        "pos_y" => format_number($row["pos_y"]),
        "angle" => format_number($row["angle"]),
        "loc_confidence" => format_number($row["loc_confidence"], 2),
        "current_station" => $row["current_station"] ?? "-",
        "vx" => format_number($row["vx"], 2),
        "is_stop" => $row["is_stop"] === null ? "-" : ($row["is_stop"] ? "Diam" : "Bergerak"),
    ];
}, $rows);

echo json_encode(["rows" => $result, "count" => count($result), "total" => $totalCount]);
