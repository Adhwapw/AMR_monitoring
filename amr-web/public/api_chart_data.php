<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json");

$pdo = get_db_connection();

$robotId = isset($_GET["robot_id"]) ? (int)$_GET["robot_id"] : null;
$type = $_GET["type"] ?? "battery";

if (!$robotId) {
    echo json_encode(["error" => "robot_id wajib diisi"]);
    exit;
}

if ($type === "motor") {
    $rows = get_motor_speed_series($pdo, $robotId);

    $labels = [];
    $leftSpeed = [];
    $rightSpeed = [];
    $grouped = [];

    foreach ($rows as $row) {
        $time = $row["logged_at"];
        $grouped[$time][$row["motor_name"]] = (float)$row["speed"];
    }

    foreach ($grouped as $time => $motors) {
        $labels[] = date("H:i:s", strtotime($time));
        $leftSpeed[] = $motors["motor_left"] ?? null;
        $rightSpeed[] = $motors["motor_right"] ?? null;
    }

    echo json_encode([
        "labels" => $labels,
        "motor_left" => $leftSpeed,
        "motor_right" => $rightSpeed,
    ]);
} else {
    $rows = get_battery_series($pdo, $robotId);

    $labels = [];
    $battery = [];

    foreach ($rows as $row) {
        $labels[] = date("H:i:s", strtotime($row["logged_at"]));
        $battery[] = $row["battery_level"] !== null ? round($row["battery_level"] * 100, 1) : null;
    }

    echo json_encode([
        "labels" => $labels,
        "battery" => $battery,
    ]);
}
