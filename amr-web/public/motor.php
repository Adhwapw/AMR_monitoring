<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : null;
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;
$page = max(1, (int)($_GET["page"] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$robots = get_all_robots($pdo);
$motorLogs = get_motor_logs($pdo, $robotId, $startDate, $endDate, $perPage, $offset);
$totalCount = count_motor_logs($pdo, $robotId, $startDate, $endDate);
$totalPages = max(1, (int)ceil($totalCount / $perPage));

$filterParams = $_GET;
unset($filterParams["page"]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Motor Robot AMR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Data Motor Robot AMR</h1>

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
            <a href="export.php?type=motor&<?= http_build_query($filterParams) ?>" class="btn-export">Export CSV</a>
        </form>

        <p class="info">
            Menampilkan <span id="rowCount"><?= count($motorLogs) ?></span> dari <?= $totalCount ?> baris.
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
                    <th>Motor</th>
                    <th>Speed</th>
                    <th>Position</th>
                    <th>Current</th>
                    <th>Temp</th>
                    <th>Error</th>
                    <th>Stop</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="motorTableBody">
                <?php if (empty($motorLogs)): ?>
                    <tr><td colspan="10" class="empty">Belum ada data.</td></tr>
                <?php endif; ?>
                <?php foreach ($motorLogs as $row): ?>
                    <tr class="<?= $row["err"] ? "row-error" : "" ?>" data-id="<?= $row["id"] ?>">
                        <td><?= format_datetime($row["logged_at"]) ?></td>
                        <td><?= htmlspecialchars($row["vehicle_id"] ?? $row["robot_id_str"]) ?></td>
                        <td><?= htmlspecialchars($row["motor_name"] ?? "-") ?></td>
                        <td><?= format_number($row["speed"]) ?></td>
                        <td><?= format_number($row["position"]) ?></td>
                        <td><?= format_number($row["current"], 2) ?></td>
                        <td><?= format_number($row["temperature"], 1) ?></td>
                        <td><?= $row["err"] ? "Ya (" . $row["error_code"] . ")" : "Tidak" ?></td>
                        <td><?= $row["stop"] ? "Ya" : "Tidak" ?></td>
                        <td><button class="btn-delete-row" data-id="<?= $row["id"] ?>" data-type="motor" title="Hapus baris ini">✕</button></td>
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
                <tr class="${row.err_flag ? "row-error" : ""}" data-id="${row.id}">
                    <td>${escapeHtml(row.logged_at)}</td>
                    <td>${escapeHtml(row.vehicle_id)}</td>
                    <td>${escapeHtml(row.motor_name)}</td>
                    <td>${escapeHtml(row.speed)}</td>
                    <td>${escapeHtml(row.position)}</td>
                    <td>${escapeHtml(row.current)}</td>
                    <td>${escapeHtml(row.temperature)}</td>
                    <td>${escapeHtml(row.err)}</td>
                    <td>${escapeHtml(row.stop)}</td>
                    <td><button class="btn-delete-row" data-id="${row.id}" data-type="motor" title="Hapus baris ini">✕</button></td>
                </tr>
            `;
        }

        async function refreshMotorTable() {
            try {
                const res = await fetch(`api_motor_logs.php?${queryParams.toString()}`);
                const data = await res.json();

                const tbody = document.getElementById("motorTableBody");
                document.getElementById("rowCount").textContent = data.count;

                if (data.rows.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="10" class="empty">Belum ada data.</td></tr>`;
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

        if (currentPage === 1) {
            setInterval(refreshMotorTable, 5000);
        }
    </script>
</body>
</html>
