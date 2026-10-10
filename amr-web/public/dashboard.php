<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robots = get_all_robots($pdo);
$latestStatuses = get_latest_status_per_robot($pdo);
$latestTasks = get_latest_task_per_robot($pdo);

// Index status terkini per robot_id biar gampang dicocokin di daftar
$statusByRobot = [];
foreach ($latestStatuses as $row) {
    $statusByRobot[$row["robot_id"]] = $row;
}

// Robot yang dipilih buat chart detail, default robot pertama
$selectedRobotId = isset($_GET["robot_id"]) ? (int)$_GET["robot_id"] : ($robots[0]["id"] ?? null);

$batteryEstimates = [];
foreach ($robots as $robot) {
    $batteryEstimates[$robot["id"]] = estimate_battery_remaining($pdo, (int)$robot["id"]);
}

$activeSession = get_active_session($pdo);
$activeSessionMinutes = $activeSession ? (float)$activeSession["minutes_running"] : 0;

$stats = get_dashboard_stats($pdo);
$recentTrips = get_recent_trips($pdo, 5);

// Data buat gauge baterai robot yang lagi dipilih
$selectedStatus = $selectedRobotId ? ($statusByRobot[$selectedRobotId] ?? null) : null;
$selectedBatteryPct = $selectedStatus && $selectedStatus["battery_level"] !== null
    ? round($selectedStatus["battery_level"] * 100, 1)
    : null;
$gaugeColor = "#9aa5b1"; // abu-abu kalau data nggak ada
if ($selectedBatteryPct !== null) {
    if ($selectedBatteryPct < 20) {
        $gaugeColor = "#e46a76"; // merah
    } elseif ($selectedBatteryPct < 50) {
        $gaugeColor = "#fb9678"; // oranye
    } else {
        $gaugeColor = "#00c292"; // hijau
    }
}
// Gauge setengah lingkaran pakai teknik stroke-dasharray, radius 80, keliling setengah = pi*r
$gaugeRadius = 80;
$gaugeCircumference = M_PI * $gaugeRadius;
$gaugeFillLength = $selectedBatteryPct !== null ? ($selectedBatteryPct / 100) * $gaugeCircumference : 0;

$selectedRobotName = null;
foreach ($robots as $r) {
    if ((int)$r["id"] === (int)$selectedRobotId) {
        $selectedRobotName = $r["vehicle_id"] ?? $r["robot_id_str"];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - AMR Logger</title>
    <link rel="stylesheet" href="style.css">
    <script src="vendor/chart.umd.min.js"></script>
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Dashboard</h1>
                <div class="breadcrumb"><a href="dashboard.php">Home</a> / Dashboard</div>
            </div>
        </div>

        <?php if ($activeSession): ?>
            <?php if ($activeSessionMinutes > 60): ?>
                <div class="alert alert-error">
                    Sesi <strong><?= htmlspecialchars($activeSession["session_name"]) ?></strong> udah jalan
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

        <div class="stat-row">
            <a class="stat-widget blue" href="connection_status.php">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5"><path d="M4 12a8 8 0 0 1 16 0"/><path d="M7.5 15.5a4 4 0 0 1 9 0"/><circle cx="12" cy="19" r="1"/></svg></div>
                <div class="stat-digit"><?= $stats["robots_online"] ?>/<?= $stats["robots_total"] ?></div>
                <div class="stat-text">Robot Online</div>
                <div class="stat-sub">dari total <?= $stats["robots_total"] ?> robot terdaftar</div>
            </a>
            <div class="stat-widget green">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5"><rect x="2" y="7" width="16" height="10" rx="2"/><path d="M22 10v4"/><rect x="4" y="9" width="10" height="6" rx="1" fill="#fff" opacity="0.6"/></svg></div>
                <div class="stat-digit"><?= $stats["avg_battery"] !== null ? round($stats["avg_battery"], 1) . "%" : "-" ?></div>
                <div class="stat-text">Rata-rata Baterai</div>
                <div class="stat-sub">status terakhir semua robot</div>
            </div>
            <a class="stat-widget orange" href="trips.php">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
                <div class="stat-digit"><?= $stats["trips_today"] ?></div>
                <div class="stat-text">Trip Hari Ini</div>
                <div class="stat-sub">klik buat lihat riwayat trip</div>
            </a>
            <a class="stat-widget purple" href="sessions.php">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                <div class="stat-digit" style="font-size: 19px; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= $activeSession ? htmlspecialchars($activeSession["session_name"]) : "-" ?></div>
                <div class="stat-text">Sesi Aktif</div>
                <div class="stat-sub"><?= $activeSession ? "berjalan " . round($activeSessionMinutes) . " menit" : "tidak ada sesi berjalan" ?></div>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Daftar Robot</h2>
                <span class="card-sub">klik robot buat lihat detail chart-nya</span>
            </div>
            <div class="card-body flush">
                <div class="robot-list">
                    <?php if (empty($robots)): ?>
                        <div class="empty" style="padding: 32px; text-align: center; color: var(--text-muted);">Belum ada robot yang terdaftar.</div>
                    <?php endif; ?>
                    <?php foreach ($robots as $robot): ?>
                        <?php
                            $status = $statusByRobot[$robot["id"]] ?? null;
                            $conn = get_latest_connection_event($pdo, $robot["id"]);
                            $connStatus = get_effective_connection_status($conn);
                            $task = $latestTasks[$robot["id"]] ?? null;
                            $taskStatus = format_task_status($task["task_status"] ?? null);
                            $estimate = $batteryEstimates[$robot["id"]] ?? null;
                            $battPct = $status && $status["battery_level"] !== null ? round($status["battery_level"] * 100, 1) : null;
                            $battBarColor = $battPct === null ? "#cbd2d9" : ($battPct < 20 ? "#e46a76" : ($battPct < 50 ? "#fb9678" : "#00c292"));
                            $avatarBg = $connStatus["label"] === "connected" ? "#00c292" : "#9aa5b1";
                            $robotName = $robot["vehicle_id"] ?? $robot["robot_id_str"];
                        ?>
                        <a href="dashboard.php?robot_id=<?= $robot["id"] ?>"
                           class="robot-list-item <?= (int)$robot["id"] === (int)$selectedRobotId ? "active" : "" ?>">
                            <div class="robot-avatar" style="background: <?= $avatarBg ?>;">
                                <?= htmlspecialchars(mb_substr($robotName, 0, 1)) ?>
                            </div>
                            <div class="robot-list-main">
                                <div class="robot-list-name"><?= htmlspecialchars($robotName) ?></div>
                                <div class="robot-list-sub">
                                    <span class="badge <?= $connStatus["class"] ?>"><?= htmlspecialchars($connStatus["label"]) ?></span>
                                    <span class="badge <?= $taskStatus["class"] ?>"><?= htmlspecialchars($taskStatus["label"]) ?></span>
                                    <?php if ($status && $status["is_stop"] !== null): ?>
                                        <span class="badge <?= $status["is_stop"] ? "badge-neutral" : "badge-info" ?>">
                                            <?= $status["is_stop"] ? "Diam" : "Bergerak" ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="robot-list-stats">
                                <div class="robot-list-stat">
                                    <div class="v"><?= $battPct !== null ? $battPct . "%" : "-" ?></div>
                                    <div class="l">Baterai</div>
                                    <div class="battery-bar" style="margin-top: 4px;">
                                        <span style="width: <?= $battPct ?? 0 ?>%; background: <?= $battBarColor ?>;"></span>
                                    </div>
                                </div>
                                <div class="robot-list-stat">
                                    <div class="v">
                                        <?php if (!$estimate): ?>
                                            -
                                        <?php elseif ($estimate["status"] === "charging"): ?>
                                            <span class="badge badge-ok">charging</span>
                                        <?php elseif ($estimate["status"] === "stable"): ?>
                                            <span class="estimate-muted">stabil</span>
                                        <?php else: ?>
                                            <?php $mins = $estimate["minutes_remaining"]; ?>
                                            ~<?= $mins >= 60 ? round($mins / 60, 1) . " jam" : round($mins) . " mnt" ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="l">Estimasi Sisa</div>
                                </div>
                                <div class="robot-list-stat">
                                    <div class="v" style="font-size: 12px;"><?= $status ? "(" . format_number($status["pos_x"], 1) . ", " . format_number($status["pos_y"], 1) . ")" : "-" ?></div>
                                    <div class="l">Posisi</div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedRobotId): ?>
            <div class="grid-2">
                <div class="card">
                    <div class="card-header">
                        <h2>Level Baterai <span class="card-sub">— <?= htmlspecialchars($selectedRobotName ?? "") ?></span></h2>
                    </div>
                    <div class="card-body">
                        <canvas id="batteryChart" height="110"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Gauge Baterai <span class="card-sub">— <?= htmlspecialchars($selectedRobotName ?? "") ?></span></h2>
                    </div>
                    <div class="card-body gauge-box">
                        <svg viewBox="0 0 200 110" class="battery-gauge">
                            <path d="M 20 100 A 80 80 0 0 1 180 100"
                                  fill="none" stroke="#eef0f3" stroke-width="16" stroke-linecap="round" />
                            <path d="M 20 100 A 80 80 0 0 1 180 100"
                                  fill="none" stroke="<?= $gaugeColor ?>" stroke-width="16" stroke-linecap="round"
                                  stroke-dasharray="<?= $gaugeFillLength ?> <?= $gaugeCircumference ?>" />
                            <text x="100" y="85" text-anchor="middle" font-size="32" font-weight="700" fill="#2b2f33">
                                <?= $selectedBatteryPct !== null ? $selectedBatteryPct . "%" : "-" ?>
                            </text>
                        </svg>
                        <p class="gauge-caption">
                            <?php if ($selectedBatteryPct === null): ?>
                                Belum ada data baterai
                            <?php elseif ($selectedBatteryPct < 20): ?>
                                Rendah, perlu segera di-charge
                            <?php elseif ($selectedBatteryPct < 50): ?>
                                Sedang
                            <?php else: ?>
                                Aman
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Laju Perubahan Baterai <span class="card-sub">(%/menit)</span></h2>
                    </div>
                    <div class="card-body">
                        <canvas id="dischargeChart" height="110"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Kecepatan Motor <span class="card-sub">(m/s)</span></h2>
                    </div>
                    <div class="card-body">
                        <canvas id="motorChart" height="110"></canvas>
                    </div>
                </div>
            </div>

            <?php $health = get_data_health_summary($pdo, $selectedRobotId); ?>
            <div class="card">
                <div class="card-header">
                    <h2>Kesehatan Data <span class="card-sub">— <?= htmlspecialchars($selectedRobotName ?? "") ?></span></h2>
                </div>
                <div class="card-body">
                    <div class="session-active-info">
                        <div class="metric">
                            <span class="metric-label">Total Baris Data</span>
                            <span class="metric-value"><?= number_format($health["total_rows"]) ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Rentang Data</span>
                            <span class="metric-value">
                                <?= $health["first_logged_at"] ? format_datetime($health["first_logged_at"]) : "-" ?>
                                &ndash;
                                <?= $health["last_logged_at"] ? format_datetime($health["last_logged_at"]) : "-" ?>
                            </span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Gap Terdeteksi</span>
                            <span class="metric-value">
                                <?php if ($health["gap_count"] > 0): ?>
                                    <span class="badge badge-warn"><?= $health["gap_count"] ?> gap</span>
                                <?php else: ?>
                                    <span class="badge badge-ok">tidak ada</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <p class="estimate-muted">
                        Gap dihitung dari selisih waktu antar baris berurutan yang lebih dari 10 detik
                        (biasanya karena robot/poller sempat disconnect). Cek halaman Status Koneksi buat detailnya.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Trip Terakhir</h2>
                <a href="trips.php" class="btn btn-outline" style="padding: 6px 14px;">Lihat Semua</a>
            </div>
            <div class="card-body flush">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Robot</th>
                                <th>Dari</th>
                                <th>Ke</th>
                                <th>Mulai</th>
                                <th>Durasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTrips)): ?>
                                <tr><td colspan="6" class="empty">Belum ada trip tersimpan.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($recentTrips as $trip): ?>
                                <?php
                                    $statusClass = match ($trip["status"]) {
                                        "selesai" => "badge-ok",
                                        "gagal" => "badge-error",
                                        "dibatalkan", "terputus" => "badge-warn",
                                        "berjalan" => "badge-info",
                                        default => "badge-neutral",
                                    };
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($trip["vehicle_id"] ?? $trip["robot_id_str"]) ?></td>
                                    <td><?= htmlspecialchars($trip["from_station"] ?? "-") ?></td>
                                    <td><?= htmlspecialchars($trip["to_station"]) ?></td>
                                    <td><?= format_datetime($trip["started_at"]) ?></td>
                                    <td><?= $trip["duration_seconds"] !== null ? $trip["duration_seconds"] . " dtk" : "-" ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($trip["status"]) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . "/../includes/layout_footer.php"; ?>

    <script>
        const robotId = <?= json_encode($selectedRobotId) ?>;
        const chartDefaults = {
            responsive: true,
            animation: false,
            plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                x: { ticks: { maxTicksLimit: 8, font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { font: { size: 10 } }, grid: { color: "#eef0f3" } },
            },
        };

        async function loadChart(type, canvasId, datasetsBuilder) {
            const res = await fetch(`api_chart_data.php?type=${type}&robot_id=${robotId}`);
            const data = await res.json();
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;
            new Chart(ctx, {
                type: "line",
                data: { labels: data.labels, datasets: datasetsBuilder(data) },
                options: chartDefaults,
            });
        }

        const motorPalette = [
            ["#03a9f3", "rgba(3, 169, 243, 0.1)"],
            ["#e46a76", "rgba(228, 106, 118, 0.1)"],
            ["#00c292", "rgba(0, 194, 146, 0.1)"],
            ["#ab8ce4", "rgba(171, 140, 228, 0.1)"],
            ["#fb9678", "rgba(251, 150, 120, 0.1)"],
        ];

        if (robotId) {
            loadChart("battery", "batteryChart", (data) => [
                {
                    label: "Level baterai (%)",
                    data: data.battery,
                    borderColor: "#00c292",
                    backgroundColor: "rgba(0, 194, 146, 0.12)",
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                },
            ]);

            loadChart("discharge_rate", "dischargeChart", (data) => [
                {
                    label: "Laju (%/menit)",
                    data: data.rate,
                    borderColor: "#e46a76",
                    backgroundColor: "rgba(228, 106, 118, 0.1)",
                    tension: 0.3,
                    pointRadius: 0,
                },
            ]);

            // Nama motor dinamis dari API (nggak hardcode kiri/kanan lagi)
            loadChart("motor", "motorChart", (data) => {
                const names = data.motor_names || [];
                return names.map((name, i) => {
                    const c = motorPalette[i % motorPalette.length];
                    return {
                        label: name,
                        data: (data.series || {})[name] || [],
                        borderColor: c[0],
                        backgroundColor: c[1],
                        tension: 0.3,
                        pointRadius: 0,
                    };
                });
            });
        }
    </script>
</body>
</html>
