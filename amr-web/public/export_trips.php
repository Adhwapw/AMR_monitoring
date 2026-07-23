<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robots = get_all_robots($pdo);
$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : ($robots[0]["id"] ?? null);
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;
$sessionSuffix = "";

if (isset($_GET["session_id"]) && $_GET["session_id"] !== "") {
    $session = get_session_by_id($pdo, (int)$_GET["session_id"]);
    if ($session) {
        $startDate = $session["started_at"];
        $endDate = $session["ended_at"] ?? date("Y-m-d H:i:s");
        if ($session["robot_id"]) {
            $robotId = (int)$session["robot_id"];
        }
        $sessionSuffix = "_" . preg_replace("/[^a-zA-Z0-9_-]+/", "_", $session["session_name"]);
    }
}

if (!$robotId) {
    die("Robot belum dipilih.");
}

$trips = get_trips_for_robot($pdo, $robotId, $startDate, $endDate);

$filename = "robot_trips{$sessionSuffix}_" . date("Ymd_His") . ".csv";
$columns = [
    "from_station", "to_station", "started_at", "completed_at",
    "duration_seconds", "distance_m", "battery_start", "battery_end", "status",
];

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");

$output = fopen("php://output", "w");
fputcsv($output, $columns);

foreach ($trips as $trip) {
    $line = [];
    foreach ($columns as $col) {
        $line[] = $trip[$col] ?? "";
    }
    fputcsv($output, $line);
}

fclose($output);
exit;
