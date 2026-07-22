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
    <title>Status Koneksi Robot AMR</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="10">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Status Koneksi Robot AMR</h1>

        <p class="info">Halaman ini refresh otomatis tiap 10 detik.</p>

        <table>
            <thead>
                <tr>
                    <th>Robot</th>
                    <th>Event Terakhir</th>
                    <th>Waktu</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($statuses)): ?>
                    <tr><td colspan="4" class="empty">Belum ada data koneksi.</td></tr>
                <?php endif; ?>
                <?php foreach ($statuses as $row): ?>
                    <?php
                        $connStatus = get_effective_connection_status([
                            "event_type" => $row["event_type"],
                            "occurred_at" => $row["occurred_at"],
                        ]);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row["vehicle_id"] ?? $row["robot_id_str"]) ?></td>
                        <td><span class="badge <?= $connStatus["class"] ?>"><?= htmlspecialchars($connStatus["label"]) ?></span></td>
                        <td><?= format_datetime($row["occurred_at"]) ?></td>
                        <td><?= htmlspecialchars($row["message"] ?? "-") ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
