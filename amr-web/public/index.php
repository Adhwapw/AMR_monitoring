<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : null;
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;
$activeSessionInfo = null;

if (isset($_GET["session_id"]) && $_GET["session_id"] !== "") {
    $activeSessionInfo = get_session_by_id($pdo, (int)$_GET["session_id"]);
    if ($activeSessionInfo) {
        $startDate = $activeSessionInfo["started_at"];
        $endDate = $activeSessionInfo["ended_at"] ?? date("Y-m-d H:i:s");
        if (!$robotId && $activeSessionInfo["robot_id"]) {
            $robotId = (int)$activeSessionInfo["robot_id"];
        }
    }
}

$page = max(1, (int)($_GET["page"] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$robots = get_all_robots($pdo);
$statusLogs = get_status_logs($pdo, $robotId, $startDate, $endDate, $perPage, $offset);
$totalCount = count_status_logs($pdo, $robotId, $startDate, $endDate);
$totalPages = max(1, (int)ceil($totalCount / $perPage));

$filterParams = $_GET;
unset($filterParams["page"]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Status Robot AMR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Data Status Robot AMR</h1>

        <?php if ($activeSessionInfo): ?>
            <div class="alert alert-success">
                Menampilkan data sesi <strong><?= htmlspecialchars($activeSessionInfo["session_name"]) ?></strong>
                (<?= format_datetime($activeSessionInfo["started_at"]) ?> &ndash;
                <?= $activeSessionInfo["ended_at"] ? format_datetime($activeSessionInfo["ended_at"]) : "masih berjalan" ?>).
                <a href="index.php">Hapus filter sesi</a>
            </div>
        <?php endif; ?>

        <form method="get" class="filter-form">
            <label>
                Robot
                <select name="robot_id">
                    <option value="">Semua Robot</option>
                    <?php foreach ($robots as $robot): ?>
                        <option value="<?= $robot["id"] ?>" <?= $robotId === (int)$robot["id"] ? "selected" : "" ?>>
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
            <a href="export.php?type=status&<?= http_build_query($filterParams) ?>" class="btn-export">Export CSV</a>
            <a href="export.php?type=combined&<?= http_build_query($filterParams) ?>" class="btn-export btn-export-combined">Export Gabungan (ML)</a>
        </form>

        <p class="info">
            Menampilkan <span id="rowCount"><?= count($statusLogs) ?></span> dari <?= $totalCount ?> baris.
            <?php if ($page === 1): ?>
                <span class="live-indicator" id="liveIndicator">● live, update tiap 5 detik</span>
            <?php else: ?>
                <span class="live-indicator live-paused">⏸ live update nonaktif di halaman selain 1</span>
            <?php endif; ?>
        </p>

        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Robot</th>
                    <th>Baterai (%)</th>
                    <th>Charging</th>
                    <th>Posisi X</th>
                    <th>Posisi Y</th>
                    <th>Angle</th>
                    <th>Confidence</th>
                    <th>Station</th>
                    <th>Vx (m/s)</th>
                    <th>Status Gerak</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="statusTableBody">
                <?php if (empty($statusLogs)): ?>
                    <tr><td colspan="12" class="empty">Belum ada data.</td></tr>
                <?php endif; ?>
                <?php foreach ($statusLogs as $row): ?>
                    <tr data-id="<?= $row["id"] ?>">
                        <td><?= format_datetime($row["logged_at"]) ?></td>
                        <td><?= htmlspecialchars($row["vehicle_id"] ?? $row["robot_id_str"]) ?></td>
                        <td><?= $row["battery_level"] !== null ? format_number($row["battery_level"] * 100, 1) : "-" ?></td>
                        <td><?= $row["charging"] ? "Ya" : "Tidak" ?></td>
                        <td><?= format_number($row["pos_x"]) ?></td>
                        <td><?= format_number($row["pos_y"]) ?></td>
                        <td><?= format_number($row["angle"]) ?></td>
                        <td><?= format_number($row["loc_confidence"], 2) ?></td>
                        <td><?= htmlspecialchars($row["current_station"] ?? "-") ?></td>
                        <td><?= format_number($row["vx"], 2) ?></td>
                        <td><?= $row["is_stop"] === null ? "-" : ($row["is_stop"] ? "Diam" : "Bergerak") ?></td>
                        <td><button class="btn-delete-row" data-id="<?= $row["id"] ?>" data-type="status" title="Hapus baris ini">✕</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <a href="?<?= http_build_query(array_merge($filterParams, ["page" => max(1, $page - 1)])) ?>"
               class="page-btn <?= $page <= 1 ? "disabled" : "" ?>">‹ Sebelumnya</a>
            <span class="page-info">Halaman <?= $page ?> dari <?= $totalPages ?></span>
            <a href="?<?= http_build_query(array_merge($filterParams, ["page" => min($totalPages, $page + 1)])) ?>"
               class="page-btn <?= $page >= $totalPages ? "disabled" : "" ?>">Berikutnya ›</a>
        </div>

        <div class="danger-zone-link">
            <a href="manage_data.php">Kelola / hapus data dalam jumlah besar &rarr;</a>
        </div>
    </div>

    <script>
        const queryParams = new URLSearchParams(window.location.search);
        const currentPage = <?= json_encode($page) ?>;

        function escapeHtml(str) {
            const div = document.createElement("div");
            div.textContent = str ?? "";
            return div.innerHTML;
        }

        function rowHtml(row) {
            return `
                <tr data-id="${row.id}">
                    <td>${escapeHtml(row.logged_at)}</td>
                    <td>${escapeHtml(row.vehicle_id)}</td>
                    <td>${escapeHtml(row.battery_level)}</td>
                    <td>${escapeHtml(row.charging)}</td>
                    <td>${escapeHtml(row.pos_x)}</td>
                    <td>${escapeHtml(row.pos_y)}</td>
                    <td>${escapeHtml(row.angle)}</td>
                    <td>${escapeHtml(row.loc_confidence)}</td>
                    <td>${escapeHtml(row.current_station)}</td>
                    <td>${escapeHtml(row.vx)}</td>
                    <td>${escapeHtml(row.is_stop)}</td>
                    <td><button class="btn-delete-row" data-id="${row.id}" data-type="status" title="Hapus baris ini">✕</button></td>
                </tr>
            `;
        }

        async function refreshStatusTable() {
            try {
                const res = await fetch(`api_status_logs.php?${queryParams.toString()}`);
                const data = await res.json();

                const tbody = document.getElementById("statusTableBody");
                document.getElementById("rowCount").textContent = data.count;

                if (data.rows.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="12" class="empty">Belum ada data.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.rows.map(rowHtml).join("");
                attachDeleteHandlers();

                const indicator = document.getElementById("liveIndicator");
                if (indicator) indicator.classList.remove("live-error");
            } catch (err) {
                const indicator = document.getElementById("liveIndicator");
                if (indicator) {
                    indicator.textContent = "● gagal update, coba lagi sebentar";
                    indicator.classList.add("live-error");
                }
            }
        }

        function attachDeleteHandlers() {
            document.querySelectorAll(".btn-delete-row").forEach((btn) => {
                btn.onclick = async () => {
                    if (!confirm("Hapus baris data ini? Tindakan ini tidak bisa dibatalkan.")) return;
                    const id = btn.dataset.id;
                    const type = btn.dataset.type;
                    try {
                        const res = await fetch("delete_log.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `id=${id}&type=${type}`,
                        });
                        const result = await res.json();
                        if (result.success) {
                            btn.closest("tr").remove();
                        } else {
                            alert("Gagal menghapus: " + (result.error ?? "Error tidak diketahui"));
                        }
                    } catch (err) {
                        alert("Gagal menghapus, cek koneksi ke server.");
                    }
                };
            });
        }

        attachDeleteHandlers();

        // Auto-refresh cuma jalan di halaman 1, biar nggak bikin bingung
        // (data baru masuk terus tapi user lagi liat halaman 3 misalnya)
        if (currentPage === 1) {
            setInterval(refreshStatusTable, 5000);
        }
    </script>
</body>
</html>
