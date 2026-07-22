<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();
$robots = get_all_robots($pdo);

$robotId = isset($_GET["robot_id"]) && $_GET["robot_id"] !== "" ? (int)$_GET["robot_id"] : null;
$startDate = $_GET["start_date"] ?? null;
$endDate = $_GET["end_date"] ?? null;

$showPreview = isset($_GET["preview"]);
$previewCounts = null;
$deleteResult = null;
$deleteError = null;

if ($showPreview) {
    $previewCounts = preview_delete_counts($pdo, $robotId, $startDate, $endDate);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $postRobotId = isset($_POST["robot_id"]) && $_POST["robot_id"] !== "" ? (int)$_POST["robot_id"] : null;
    $postStartDate = $_POST["start_date"] ?? null;
    $postEndDate = $_POST["end_date"] ?? null;
    $confirmText = $_POST["confirm_text"] ?? "";

    if ($confirmText !== "HAPUS") {
        $deleteError = "Konfirmasi tidak sesuai. Ketik persis \"HAPUS\" (huruf besar semua) buat lanjut.";
    } else {
        $deleteResult = delete_logs($pdo, $postRobotId, $postStartDate, $postEndDate);
    }

    // Refresh preview biar keliatan sisa datanya
    $robotId = $postRobotId;
    $startDate = $postStartDate;
    $endDate = $postEndDate;
    $previewCounts = preview_delete_counts($pdo, $robotId, $startDate, $endDate);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data - Robot AMR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Kelola / Hapus Data</h1>
        <p class="info">
            Halaman ini buat hapus data dalam jumlah besar berdasarkan robot dan/atau rentang waktu.
            Data yang sudah dihapus tidak bisa dikembalikan, jadi pastiin dulu lewat tombol Preview sebelum benar-benar menghapus.
        </p>

        <?php if ($deleteResult): ?>
            <div class="alert alert-success">
                <strong>Berhasil dihapus:</strong>
                <ul>
                    <?php foreach ($deleteResult as $table => $count): ?>
                        <li><?= htmlspecialchars($table) ?>: <?= $count ?> baris</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($deleteError): ?>
            <div class="alert alert-error"><?= htmlspecialchars($deleteError) ?></div>
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
            <button type="submit" name="preview" value="1">Preview</button>
        </form>

        <p class="warning-text">
            Kosongkan semua filter (Robot: Semua Robot, Dari &amp; Sampai dikosongin) kalau mau hapus <strong>seluruh data log</strong>.
            Data master robot di tabel <code>robots</code> tidak ikut kehapus.
        </p>

        <?php if ($previewCounts !== null): ?>
            <div class="preview-box">
                <h2>Jumlah data yang cocok dengan filter di atas</h2>
                <table>
                    <thead>
                        <tr><th>Tabel</th><th>Jumlah Baris</th></tr>
                    </thead>
                    <tbody>
                        <?php $totalPreview = 0; ?>
                        <?php foreach ($previewCounts as $table => $count): ?>
                            <?php $totalPreview += $count; ?>
                            <tr>
                                <td><?= htmlspecialchars($table) ?></td>
                                <td><?= $count ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($totalPreview > 0): ?>
                    <form method="post" class="delete-form" onsubmit="return confirm('Yakin mau hapus data ini? Tindakan ini permanen.');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="robot_id" value="<?= htmlspecialchars((string)($robotId ?? "")) ?>">
                        <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate ?? "") ?>">
                        <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate ?? "") ?>">

                        <label>
                            Ketik <strong>HAPUS</strong> buat konfirmasi
                            <input type="text" name="confirm_text" placeholder="HAPUS" required autocomplete="off">
                        </label>
                        <button type="submit" class="btn-delete-bulk">Hapus <?= $totalPreview ?> Baris Sekarang</button>
                    </form>
                <?php else: ?>
                    <p class="info">Tidak ada data yang cocok dengan filter ini.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
