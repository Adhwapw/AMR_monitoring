<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robots = get_all_robots($pdo);
$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : ($robots[0]["id"] ?? null);
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;
$sessionInfo = null;

if (isset($_GET["session_id"]) && $_GET["session_id"] !== "") {
    $sessionInfo = get_session_by_id($pdo, (int)$_GET["session_id"]);
    if ($sessionInfo) {
        $startDate = $sessionInfo["started_at"];
        $endDate = $sessionInfo["ended_at"] ?? date("Y-m-d H:i:s");
        if ($sessionInfo["robot_id"]) {
            $robotId = (int)$sessionInfo["robot_id"];
        }
    }
}

$trips = $robotId ? get_trips_for_robot($pdo, $robotId, $startDate, $endDate) : [];

// Ringkasan rata-rata durasi per pasangan rute (from -> to), cuma dari trip yang "selesai"
$routeSummary = [];
foreach ($trips as $trip) {
    if ($trip["status"] !== "selesai" || $trip["duration_seconds"] === null) {
        continue;
    }
    $key = ($trip["from_station"] ?? "?") . " -> " . $trip["to_station"];
    if (!isset($routeSummary[$key])) {
        $routeSummary[$key] = ["count" => 0, "total_duration" => 0, "total_distance" => 0, "distance_count" => 0];
    }
    $routeSummary[$key]["count"]++;
    $routeSummary[$key]["total_duration"] += $trip["duration_seconds"];
    if ($trip["distance_m"] !== null) {
        $routeSummary[$key]["total_distance"] += $trip["distance_m"];
        $routeSummary[$key]["distance_count"]++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Trip Robot AMR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Riwayat Trip (A &rarr; B)</h1>
        <p class="info">
            Trip dideteksi otomatis dari perubahan status task robot (RUNNING sampai COMPLETED/FAILED/CANCELED),
            nggak perlu diolah manual dari data mentah lagi.
        </p>

        <?php if ($sessionInfo): ?>
            <div class="alert alert-success">
                Menampilkan trip dari sesi <strong><?= htmlspecialchars($sessionInfo["session_name"]) ?></strong>.
                <a href="trips.php">Hapus filter sesi</a>
            </div>
        <?php endif; ?>

        <form method="get" class="filter-form">
            <label>
                Robot
                <select name="robot_id">
                    <?php foreach ($robots as $robot): ?>
                        <option value="<?= $robot["id"] ?>" <?= (int)$robotId === (int)$robot["id"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($robot["vehicle_id"] ?? $robot["robot_id_str"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Dari
                <input type="datetime-local" name="start_date" value="<?= htmlspecialchars($startDate ?? "") ?>">
            </label>
            <label>
                Sampai
                <input type="datetime-local" name="end_date" value="<?= htmlspecialchars($endDate ?? "") ?>">
            </label>
            <button type="submit">Filter</button>
            <a href="export_trips.php?<?= http_build_query($_GET) ?>" class="btn-export">Export CSV</a>
        </form>

        <?php if (!empty($routeSummary)): ?>
            <div class="preview-box">
                <h2>Ringkasan Rata-rata per Rute (trip yang selesai)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Rute</th>
                            <th>Jumlah Trip</th>
                            <th>Rata-rata Durasi</th>
                            <th>Jarak Garis Lurus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routeSummary as $route => $summary): ?>
                            <tr>
                                <td><?= htmlspecialchars($route) ?></td>
                                <td><?= $summary["count"] ?></td>
                                <td><?= round($summary["total_duration"] / $summary["count"]) ?> detik</td>
                                <td>
                                    <?= $summary["distance_count"] > 0
                                        ? format_number($summary["total_distance"] / $summary["distance_count"], 2) . " m"
                                        : "-" ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h2 class="section-title">Detail Semua Trip</h2>
        <table>
            <thead>
                <tr>
                    <th>Dari</th>
                    <th>Ke</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Durasi</th>
                    <th>Jarak Garis Lurus</th>
                    <th>Baterai Awal</th>
                    <th>Baterai Akhir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($trips)): ?>
                    <tr><td colspan="9" class="empty">Belum ada trip terdeteksi di rentang waktu ini.</td></tr>
                <?php endif; ?>
                <?php foreach ($trips as $trip): ?>
                    <?php
                        $statusClass = match ($trip["status"]) {
                            "selesai" => "badge-ok",
                            "gagal" => "badge-error",
                            "dibatalkan", "terputus" => "badge-warn",
                            "berjalan" => "badge-ok",
                            default => "badge-neutral",
                        };
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($trip["from_station"] ?? "-") ?></td>
                        <td><?= htmlspecialchars($trip["to_station"]) ?></td>
                        <td><?= format_datetime($trip["started_at"]) ?></td>
                        <td><?= $trip["completed_at"] ? format_datetime($trip["completed_at"]) : "-" ?></td>
                        <td><?= $trip["duration_seconds"] !== null ? $trip["duration_seconds"] . " detik" : "-" ?></td>
                        <td><?= $trip["distance_m"] !== null ? format_number($trip["distance_m"], 2) . " m" : "-" ?></td>
                        <td><?= $trip["battery_start"] !== null ? format_number($trip["battery_start"] * 100, 1) . "%" : "-" ?></td>
                        <td><?= isset($trip["battery_end"]) && $trip["battery_end"] !== null ? format_number($trip["battery_end"] * 100, 1) . "%" : "-" ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($trip["status"]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
