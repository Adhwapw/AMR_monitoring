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
    $grouped = [];
    $motorNames = [];

    foreach ($rows as $row) {
        $time = $row["logged_at"];
        $name = $row["motor_name"] ?? "motor";
        $grouped[$time][$name] = (float)$row["speed"];
        $motorNames[$name] = true;
    }
    $motorNames = array_keys($motorNames);
    sort($motorNames);

    $series = [];
    foreach ($grouped as $time => $motors) {
        $labels[] = date("H:i:s", strtotime($time));
        foreach ($motorNames as $name) {
            $series[$name][] = $motors[$name] ?? null;
        }
    }

    echo json_encode([
        "labels" => $labels,
        "motor_names" => $motorNames,
        "series" => $series,
    ]);
} elseif ($type === "discharge_rate") {
    $rows = get_discharge_rate_series($pdo, $robotId);

    $labels = [];
    $rate = [];

    foreach ($rows as $row) {
        $labels[] = date("H:i:s", strtotime($row["logged_at"]));
        $rate[] = $row["rate_per_min"];
    }

    echo json_encode([
        "labels" => $labels,
        "rate" => $rate,
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
