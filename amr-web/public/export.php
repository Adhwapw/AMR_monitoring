<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$type = $_GET["type"] ?? "status";
$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : null;
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;
$sessionSuffix = "";

if (isset($_GET["session_id"]) && $_GET["session_id"] !== "") {
    $session = get_session_by_id($pdo, (int)$_GET["session_id"]);
    if ($session) {
        $startDate = $session["started_at"];
        $endDate = $session["ended_at"] ?? date("Y-m-d H:i:s");
        if (!$robotId && $session["robot_id"]) {
            $robotId = (int)$session["robot_id"];
        }
        $sessionSuffix = "_" . preg_replace("/[^a-zA-Z0-9_-]+/", "_", $session["session_name"]);
    }
}

// Export nggak dibatasin 200 baris kayak tampilan web, biar dataset ML-nya lengkap
$limit = 100000;

if ($type === "combined") {
    $result = get_combined_export_data($pdo, $robotId, $startDate, $endDate, $limit);
    $rows = $result["rows"];
    $motorNames = $result["motor_names"];
    $filename = "robot_combined_logs{$sessionSuffix}_" . date("Ymd_His") . ".csv";

    $columns = [
        "logged_at", "vehicle_id", "battery_level", "battery_temp", "charging", "voltage", "battery_current",
        "pos_x", "pos_y", "angle", "loc_confidence", "current_station", "last_station",
        "vx", "vy", "w", "is_stop",
        "imu_yaw", "imu_roll", "imu_pitch", "imu_acc_x", "imu_acc_y", "imu_acc_z",
    ];
    foreach ($motorNames as $name) {
        $columns[] = "speed_{$name}";
        $columns[] = "position_{$name}";
        $columns[] = "current_{$name}";
        $columns[] = "err_{$name}";
    }

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}\"");

    $output = fopen("php://output", "w");
    fputcsv($output, $columns);
    foreach ($rows as $row) {
        $line = [];
        foreach ($columns as $col) {
            $line[] = $row[$col] ?? "";
        }
        fputcsv($output, $line);
    }
    fclose($output);
    exit;
}

if ($type === "motor") {
    $rows = get_motor_logs($pdo, $robotId, $startDate, $endDate, $limit);
    $filename = "robot_motor_logs{$sessionSuffix}_" . date("Ymd_His") . ".csv";
    $columns = [
        "logged_at", "vehicle_id", "motor_name", "motor_type", "can_router", "can_id",
        "position", "speed", "current", "voltage", "stop", "error_code", "err", "emc",
        "temperature", "encoder", "passive", "calib",
    ];
} else {
    $rows = get_status_logs($pdo, $robotId, $startDate, $endDate, $limit);
    $filename = "robot_status_logs{$sessionSuffix}_" . date("Ymd_His") . ".csv";
    $columns = [
        "logged_at", "vehicle_id", "battery_level", "battery_temp", "charging", "voltage", "current",
        "battery_cycle", "pos_x", "pos_y", "angle", "loc_confidence", "current_station",
        "last_station", "loc_method", "vx", "vy", "w", "is_stop",
    ];
}

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");

$output = fopen("php://output", "w");
fputcsv($output, $columns);

foreach ($rows as $row) {
    $line = [];
    foreach ($columns as $col) {
        $line[] = $row[$col] ?? "";
    }
    fputcsv($output, $line);
}

fclose($output);
exit;
