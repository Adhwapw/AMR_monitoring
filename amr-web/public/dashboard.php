<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robots = get_all_robots($pdo);
$latestStatuses = get_latest_status_per_robot($pdo);
$latestTasks = get_latest_task_per_robot($pdo);

// Index status terkini per robot_id biar gampang dicocokin di cards
$statusByRobot = [];
foreach ($latestStatuses as $row) {
    $statusByRobot[$row["robot_id"]] = $row;
}

// Robot yang dipilih buat chart detail, default robot pertama
$selectedRobotId = isset($_GET["robot_id"]) ? (int)$_GET["robot_id"] : ($robots[0]["id"] ?? null);

$activeSession = get_active_session($pdo);
$activeSessionMinutes = $activeSession ? (time() - strtotime($activeSession["started_at"])) / 60 : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Robot AMR</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Dashboard Robot AMR</h1>

        <?php if ($activeSession): ?>
            <?php if ($activeSessionMinutes > 60): ?>
                <div class="alert alert-error">
                    ⚠ Sesi <strong><?= htmlspecialchars($activeSession["session_name"]) ?></strong> udah jalan
                    <?= round($activeSessionMinutes) ?> menit dan belum diselesaikan.
                    <a href="sessions.php">Cek di halaman Sesi Data</a>.
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    Sesi <strong><?= htmlspecialchars($activeSession["session_name"]) ?></strong> lagi berjalan
                    (<?= round($activeSessionMinutes) ?> menit). <a href="sessions.php">Kelola sesi</a>.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="cards">
            <?php if (empty($robots)): ?>
                <p class="info">Belum ada robot yang terdaftar.</p>
            <?php endif; ?>
            <?php foreach ($robots as $robot): ?>
                <?php
                    $status = $statusByRobot[$robot["id"]] ?? null;
                    $conn = get_latest_connection_event($pdo, $robot["id"]);
                    $connStatus = get_effective_connection_status($conn);
                    $connState = $connStatus["label"];
                    $badgeClass = $connStatus["class"];

                    $task = $latestTasks[$robot["id"]] ?? null;
                    $taskStatus = format_task_status($task["task_status"] ?? null);
                ?>
                <a href="dashboard.php?robot_id=<?= $robot["id"] ?>"
                   class="card <?= (int)$robot["id"] === (int)$selectedRobotId ? "card-active" : "" ?>">
                    <div class="card-header">
                        <span class="card-title"><?= htmlspecialchars($robot["vehicle_id"] ?? $robot["robot_id_str"]) ?></span>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($connState) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="metric">
                            <span class="metric-label">Status Task</span>
                            <span class="metric-value">
                                <span class="badge <?= $taskStatus["class"] ?>"><?= htmlspecialchars($taskStatus["label"]) ?></span>
                            </span>
                        </div>
                        <?php if ($task && $task["target_id"]): ?>
                            <div class="metric">
                                <span class="metric-label">Tujuan</span>
                                <span class="metric-value"><?= htmlspecialchars($task["target_id"]) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="metric">
                            <span class="metric-label">Baterai</span>
                            <span class="metric-value">
                                <?= $status && $status["battery_level"] !== null ? format_number($status["battery_level"] * 100, 1) . "%" : "-" ?>
                            </span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Posisi</span>
                            <span class="metric-value">
                                <?= $status ? "(" . format_number($status["pos_x"], 2) . ", " . format_number($status["pos_y"], 2) . ")" : "-" ?>
                            </span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Confidence Lokasi</span>
                            <span class="metric-value"><?= $status ? format_number($status["loc_confidence"], 2) : "-" ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Update Terakhir</span>
                            <span class="metric-value"><?= $status ? format_datetime($status["logged_at"]) : "-" ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($selectedRobotId): ?>
            <div class="charts">
                <div class="chart-box">
                    <h2>Level Baterai (%)</h2>
                    <canvas id="batteryChart" height="90"></canvas>
                </div>
                <div class="chart-box">
                    <h2>Kecepatan Motor (m/s)</h2>
                    <canvas id="motorChart" height="90"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const robotId = <?= json_encode($selectedRobotId) ?>;

        async function loadChart(type, canvasId, datasetsBuilder) {
            const res = await fetch(`api_chart_data.php?type=${type}&robot_id=${robotId}`);
            const data = await res.json();
            const ctx = document.getElementById(canvasId);
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: datasetsBuilder(data),
                },
                options: {
                    responsive: true,
                    animation: false,
                    scales: {
                        y: { beginAtZero: false },
                    },
                },
            });
        }

        if (robotId) {
            loadChart("battery", "batteryChart", (data) => [
                {
                    label: "Baterai (%)",
                    data: data.battery,
                    borderColor: "#2563eb",
                    backgroundColor: "rgba(37, 99, 235, 0.1)",
                    tension: 0.3,
                },
            ]);

            loadChart("motor", "motorChart", (data) => [
                {
                    label: "Motor Kiri",
                    data: data.motor_left,
                    borderColor: "#16a34a",
                    backgroundColor: "rgba(22, 163, 74, 0.1)",
                    tension: 0.3,
                },
                {
                    label: "Motor Kanan",
                    data: data.motor_right,
                    borderColor: "#dc2626",
                    backgroundColor: "rgba(220, 38, 38, 0.1)",
                    tension: 0.3,
                },
            ]);
        }
    </script>
</body>
</html>
