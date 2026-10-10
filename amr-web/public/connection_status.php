<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();
$statuses = get_connection_status($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Koneksi - AMR Logger</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Status Koneksi</h1>
                <div class="breadcrumb"><a href="dashboard.php">Home</a> / Status Koneksi</div>
            </div>
        </div>

        <p class="info">
            <span class="live-indicator" id="liveIndicator">● live, update tiap 10 detik</span>
        </p>

        <div class="card">
            <div class="card-body flush">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Robot</th>
                                <th>Event Terakhir</th>
                                <th>Waktu</th>
                                <th>Selisih</th>
                                <th>Pesan</th>
                            </tr>
                        </thead>
                        <tbody id="connTableBody">
                            <?php if (empty($statuses)): ?>
                                <tr><td colspan="5" class="empty">Belum ada data koneksi.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($statuses as $row): ?>
                                <?php $connStatus = get_effective_connection_status($row); ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["vehicle_id"] ?? $row["robot_id_str"]) ?></td>
                                    <td><span class="badge <?= $connStatus["class"] ?>"><?= htmlspecialchars($connStatus["label"]) ?></span></td>
                                    <td><?= format_datetime($row["occurred_at"]) ?></td>
                                    <td><?= (int)$row["seconds_ago"] ?> dtk lalu</td>
                                    <td><?= htmlspecialchars($row["message"] ?? "-") ?></td>
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
        function escapeHtml(str) {
            const div = document.createElement("div");
            div.textContent = str ?? "";
            return div.innerHTML;
        }

        function agoText(sec) {
            if (sec < 60) return sec + " dtk lalu";
            if (sec < 3600) return Math.floor(sec / 60) + " mnt lalu";
            return Math.floor(sec / 3600) + " jam lalu";
        }

        async function refreshConnTable() {
            try {
                const res = await fetch("api_connection_status.php");
                const data = await res.json();
                const tbody = document.getElementById("connTableBody");

                if (data.rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty">Belum ada data koneksi.</td></tr>';
                    return;
                }

                tbody.innerHTML = data.rows.map((row) => `
                    <tr>
                        <td>${escapeHtml(row.robot)}</td>
                        <td><span class="badge ${escapeHtml(row.class)}">${escapeHtml(row.label)}</span></td>
                        <td>${escapeHtml(row.occurred_at)}</td>
                        <td>${agoText(row.seconds_ago)}</td>
                        <td>${escapeHtml(row.message)}</td>
                    </tr>
                `).join("");

                const indicator = document.getElementById("liveIndicator");
                if (indicator) {
                    indicator.textContent = "● live, update tiap 10 detik";
                    indicator.classList.remove("live-error");
                }
            } catch (err) {
                const indicator = document.getElementById("liveIndicator");
                if (indicator) {
                    indicator.textContent = "● gagal update, coba lagi sebentar";
                    indicator.classList.add("live-error");
                }
            }
        }

        setInterval(refreshConnTable, 10000);
    </script>
</body>
</html>
