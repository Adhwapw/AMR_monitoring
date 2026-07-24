<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = get_db_connection();

$message = null;
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "start") {
        $name = trim($_POST["session_name"] ?? "");
        if ($name === "") {
            $message = "Nama sesi wajib diisi.";
            $messageType = "error";
        } elseif (get_active_session($pdo)) {
            $message = "Masih ada sesi yang aktif, selesaikan dulu sebelum mulai sesi baru.";
            $messageType = "error";
        } else {
            $robotId = isset($_POST["robot_id"]) && $_POST["robot_id"] !== "" ? (int)$_POST["robot_id"] : null;
            create_session(
                $pdo,
                $name,
                $robotId,
                trim($_POST["floor_condition"] ?? "") ?: null,
                trim($_POST["load_note"] ?? "") ?: null,
                trim($_POST["notes"] ?? "") ?: null
            );
            $message = "Sesi \"{$name}\" dimulai.";
        }
    } elseif ($action === "end") {
        $sessionId = (int)($_POST["session_id"] ?? 0);
        end_session($pdo, $sessionId);
        $message = "Sesi diselesaikan.";
    } elseif ($action === "delete") {
        $sessionId = (int)($_POST["session_id"] ?? 0);
        delete_session($pdo, $sessionId);
        $message = "Metadata sesi dihapus (data log tetap ada).";
    }
}

$robots = get_all_robots($pdo);
$activeSession = get_active_session($pdo);
$sessions = get_all_sessions($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sesi Pengambilan Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once __DIR__ . "/../includes/nav.php"; ?>
    <div class="container">
        <h1>Sesi Pengambilan Data</h1>
        <p class="info">
            Nandain rentang waktu logging dengan konteks eksperimen (kondisi lantai, beban, catatan),
            biar nanti gampang difilter/export per sesi tanpa perlu inget-inget tanggal manual.
        </p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($activeSession): ?>
            <?php
                $minutesRunning = (float)$activeSession["minutes_running"];
                $sessionWarningThreshold = 60; // menit
            ?>
            <?php if ($minutesRunning > $sessionWarningThreshold): ?>
                <div class="alert alert-error">
                    ⚠ Sesi <strong><?= htmlspecialchars($activeSession["session_name"]) ?></strong> udah jalan
                    <?= round($minutesRunning) ?> menit dan belum diselesaikan. Kalau eksperimennya udah kelar,
                    jangan lupa klik "Selesaikan Sesi" biar rentang waktunya nggak kepanjangan dan
                    nyampur sama data di luar eksperimen.
                </div>
            <?php endif; ?>
            <div class="preview-box session-active-box">
                <h2>Sesi sedang berjalan</h2>
                <div class="session-active-info">
                    <div>
                        <strong><?= htmlspecialchars($activeSession["session_name"]) ?></strong>
                        <span class="badge badge-ok">berjalan</span>
                    </div>
                    <div class="metric"><span class="metric-label">Robot</span>
                        <span class="metric-value"><?= htmlspecialchars($activeSession["vehicle_id"] ?? "Semua robot") ?></span></div>
                    <div class="metric"><span class="metric-label">Kondisi lantai</span>
                        <span class="metric-value"><?= htmlspecialchars($activeSession["floor_condition"] ?? "-") ?></span></div>
                    <div class="metric"><span class="metric-label">Beban</span>
                        <span class="metric-value"><?= htmlspecialchars($activeSession["load_note"] ?? "-") ?></span></div>
                    <div class="metric"><span class="metric-label">Mulai</span>
                        <span class="metric-value"><?= format_datetime($activeSession["started_at"]) ?></span></div>
                    <div class="metric"><span class="metric-label">Durasi berjalan</span>
                        <span class="metric-value"><?= round($minutesRunning) ?> menit</span></div>
                </div>
                <form method="post" class="delete-form" onsubmit="return confirm('Selesaikan sesi ini sekarang?');">
                    <input type="hidden" name="action" value="end">
                    <input type="hidden" name="session_id" value="<?= $activeSession["id"] ?>">
                    <button type="submit" class="btn-export">Selesaikan Sesi</button>
                </form>
            </div>
        <?php else: ?>
            <div class="preview-box">
                <h2>Mulai Sesi Baru</h2>
                <form method="post" class="filter-form session-form">
                    <input type="hidden" name="action" value="start">
                    <label>
                        Nama Sesi
                        <input type="text" name="session_name" placeholder="misal: Lantai licin - percobaan 1" required>
                    </label>
                    <label>
                        Robot
                        <select name="robot_id">
                            <option value="">Semua Robot</option>
                            <?php foreach ($robots as $robot): ?>
                                <option value="<?= $robot["id"] ?>"><?= htmlspecialchars($robot["vehicle_id"] ?? $robot["robot_id_str"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Kondisi Lantai
                        <input type="text" name="floor_condition" placeholder="misal: licin, kasar, normal">
                    </label>
                    <label>
                        Catatan Beban
                        <input type="text" name="load_note" placeholder="misal: kosong, penuh 20kg">
                    </label>
                    <label style="flex: 1 1 100%;">
                        Catatan Tambahan
                        <input type="text" name="notes" placeholder="opsional">
                    </label>
                    <button type="submit">Mulai Sesi</button>
                </form>
            </div>
        <?php endif; ?>

        <h2 class="section-title">Riwayat Sesi</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Sesi</th>
                    <th>Robot</th>
                    <th>Kondisi Lantai</th>
                    <th>Beban</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr><td colspan="7" class="empty">Belum ada sesi.</td></tr>
                <?php endif; ?>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?= htmlspecialchars($session["session_name"]) ?></td>
                        <td><?= htmlspecialchars($session["vehicle_id"] ?? "Semua robot") ?></td>
                        <td><?= htmlspecialchars($session["floor_condition"] ?? "-") ?></td>
                        <td><?= htmlspecialchars($session["load_note"] ?? "-") ?></td>
                        <td><?= format_datetime($session["started_at"]) ?></td>
                        <td>
                            <?php if ($session["ended_at"]): ?>
                                <?= format_datetime($session["ended_at"]) ?>
                            <?php else: ?>
                                <span class="badge badge-ok">berjalan</span>
                            <?php endif; ?>
                        </td>
                        <td class="session-actions">
                            <a href="index.php?session_id=<?= $session["id"] ?>" class="page-btn">Lihat Data</a>
                            <a href="trips.php?session_id=<?= $session["id"] ?>" class="page-btn">Lihat Trip</a>
                            <a href="export.php?type=combined&session_id=<?= $session["id"] ?>" class="page-btn">Export</a>
                            <form method="post" onsubmit="return confirm('Hapus metadata sesi ini? Data log yang udah kesimpen nggak ikut kehapus.');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="session_id" value="<?= $session["id"] ?>">
                                <button type="submit" class="btn-delete-row" title="Hapus metadata sesi">✕</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
