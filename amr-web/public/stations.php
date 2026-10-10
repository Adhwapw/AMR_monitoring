<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$robots = get_all_robots($pdo);
$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : ($robots[0]["id"] ?? null);
$stations = get_stations_for_robot($pdo, $robotId);

$distanceResult = null;
if (isset($_GET["from"]) && isset($_GET["to"]) && $_GET["from"] !== "" && $_GET["to"] !== "") {
    $from = null;
    $to = null;
    foreach ($stations as $s) {
        if ($s["station_id"] === $_GET["from"]) $from = $s;
        if ($s["station_id"] === $_GET["to"]) $to = $s;
    }
    if ($from && $to) {
        $distanceResult = [
            "from" => $from,
            "to" => $to,
            "distance" => calculate_distance((float)$from["x"], (float)$from["y"], (float)$to["x"], (float)$to["y"]),
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peta Titik Robot AMR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Peta Titik</h1>
                <div class="breadcrumb"><a href="dashboard.php">Home</a> / Peta Titik</div>
            </div>
        </div>
        <p class="info">
            Koordinat titik/station dari peta yang lagi dimuat robot (API 1301). Data ini relatif statis,
            cuma di-sync sekali pas poller start, jadi kalau map robot berubah, poller perlu direstart biar ke-sync ulang.
        </p>

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body">
                <form method="get" class="filter-form">
                    <label>
                        Robot
                        <select name="robot_id" onchange="this.form.submit()">
                            <?php foreach ($robots as $robot): ?>
                                <option value="<?= $robot["id"] ?>" <?= (int)$robotId === (int)$robot["id"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($robot["vehicle_id"] ?? $robot["robot_id_str"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </form>
            </div>
        </div>

        <?php if (!empty($stations)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Kalkulator Jarak Antar Titik</h2>
                </div>
                <div class="card-body">
                    <form method="get" class="filter-form">
                        <input type="hidden" name="robot_id" value="<?= $robotId ?>">
                        <label>
                            Dari
                            <select name="from">
                                <?php foreach ($stations as $s): ?>
                                    <option value="<?= htmlspecialchars($s["station_id"]) ?>" <?= ($_GET["from"] ?? "") === $s["station_id"] ? "selected" : "" ?>>
                                        <?= htmlspecialchars($s["station_id"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Ke
                            <select name="to">
                                <?php foreach ($stations as $s): ?>
                                    <option value="<?= htmlspecialchars($s["station_id"]) ?>" <?= ($_GET["to"] ?? "") === $s["station_id"] ? "selected" : "" ?>>
                                        <?= htmlspecialchars($s["station_id"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button type="submit">Hitung Jarak</button>
                    </form>

                    <?php if ($distanceResult): ?>
                        <div class="alert alert-success" style="margin-top: 16px; margin-bottom: 0;">
                            Jarak garis lurus dari <strong><?= htmlspecialchars($distanceResult["from"]["station_id"]) ?></strong>
                            ke <strong><?= htmlspecialchars($distanceResult["to"]["station_id"]) ?></strong>:
                            <strong><?= format_number($distanceResult["distance"], 2) ?> meter</strong>.
                            <br><span class="estimate-muted">
                                Ini jarak garis lurus (euclidean), bukan jarak jalur navigasi aktual yang mungkin
                                berbelok/menghindar rintangan. Berguna sebagai baseline kasar, bukan estimasi presisi.
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Daftar Titik</h2>
            </div>
            <div class="card-body">
                <input
                    type="text"
                    id="stationSearch"
                    class="station-search-input"
                    placeholder="Cari ID titik, tipe, atau keterangan..."
                    onkeyup="filterStations()"
                >
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Titik</th>
                                <th>Tipe</th>
                                <th>X</th>
                                <th>Y</th>
                                <th>Orientasi (rad)</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="stationTableBody">
                            <?php if (empty($stations)): ?>
                                <tr><td colspan="6" class="empty">Belum ada data station. Pastikan poller udah sempat sync (restart poller kalau perlu).</td></tr>
                            <?php endif; ?>
                            <?php foreach ($stations as $s): ?>
                                <tr data-search="<?= htmlspecialchars(strtolower($s["station_id"] . " " . ($s["station_type"] ?? "") . " " . ($s["description"] ?? ""))) ?>">
                                    <td><?= htmlspecialchars($s["station_id"]) ?></td>
                                    <td><?= htmlspecialchars($s["station_type"] ?? "-") ?></td>
                                    <td><?= format_number($s["x"], 2) ?></td>
                                    <td><?= format_number($s["y"], 2) ?></td>
                                    <td><?= format_number($s["r"], 2) ?></td>
                                    <td><?= htmlspecialchars($s["description"] ?? "-") ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="info" id="stationSearchInfo" style="margin-top: 8px;"></p>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . "/../includes/layout_footer.php"; ?>

    <script>
        function filterStations() {
            const query = document.getElementById("stationSearch").value.toLowerCase().trim();
            const rows = document.querySelectorAll("#stationTableBody tr[data-search]");
            let visibleCount = 0;

            rows.forEach(row => {
                const match = row.dataset.search.includes(query);
                row.style.display = match ? "" : "none";
                if (match) visibleCount++;
            });

            const info = document.getElementById("stationSearchInfo");
            info.textContent = query ? `Menampilkan ${visibleCount} dari ${rows.length} titik.` : "";
        }
    </script>
</body>
</html>
