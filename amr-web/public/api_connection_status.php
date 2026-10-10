<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json");

$pdo = get_db_connection();
$statuses = get_connection_status($pdo);

$rows = array_map(function ($row) {
    $connStatus = get_effective_connection_status($row);
    return [
        "robot" => $row["vehicle_id"] ?? $row["robot_id_str"],
        "label" => $connStatus["label"],
        "class" => $connStatus["class"],
        "occurred_at" => format_datetime($row["occurred_at"]),
        "seconds_ago" => (int)$row["seconds_ago"],
        "message" => $row["message"] ?? "-",
    ];
}, $statuses);

echo json_encode(["rows" => $rows]);
