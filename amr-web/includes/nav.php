<?php
// =========================================================
// includes/nav.php
// Shell layout ala ElaAdmin: sidebar gelap + topbar putih.
// Di-include tepat setelah <body>, lalu halaman membuka
// <div class="container"> sendiri dan menutupnya dengan
// includes/layout_footer.php sebelum </body>.
// Otomatis nandain menu aktif berdasarkan nama file saat ini.
// =========================================================

$currentPage = basename($_SERVER["PHP_SELF"] ?? "");

// Ringkasan koneksi buat pill di topbar (aman kalau $pdo belum ada)
$navOnlineCount = 0;
$navRobotTotal = 0;
if (isset($pdo)) {
    try {
        foreach (get_connection_status($pdo) as $cs) {
            $navRobotTotal++;
            if (get_effective_connection_status($cs)["label"] === "connected") {
                $navOnlineCount++;
            }
        }
    } catch (Throwable $e) {
        // topbar tetap tampil walau query gagal
    }
}
$navConnClass = $navRobotTotal === 0 ? "" : ($navOnlineCount === $navRobotTotal ? "ok" : ($navOnlineCount > 0 ? "warn" : "err"));

$navSections = [
    "Monitoring" => [
        [
            "href" => "dashboard.php",
            "label" => "Dashboard",
            "icon" => '<path d="M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z"/>',
        ],
        [
            "href" => "index.php",
            "label" => "Status & Lokasi",
            "icon" => '<circle cx="12" cy="10" r="3"/><path d="M12 21s7-6.5 7-11a7 7 0 1 0-14 0c0 4.5 7 11 7 11z"/>',
        ],
        [
            "href" => "motor.php",
            "label" => "Data Motor",
            "icon" => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/>',
        ],
        [
            "href" => "connection_status.php",
            "label" => "Status Koneksi",
            "icon" => '<path d="M4 12a8 8 0 0 1 16 0"/><path d="M7.5 15.5a4 4 0 0 1 9 0"/><circle cx="12" cy="19" r="1"/>',
        ],
    ],
    "Data" => [
        [
            "href" => "sessions.php",
            "label" => "Sesi Data",
            "icon" => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
        ],
        [
            "href" => "stations.php",
            "label" => "Peta Titik",
            "icon" => '<path d="M20.5 10.5c0 6-8.5 11-8.5 11s-8.5-5-8.5-11a8.5 8.5 0 0 1 17 0z"/><circle cx="12" cy="10.5" r="2.5"/>',
        ],
        [
            "href" => "trips.php",
            "label" => "Riwayat Trip",
            "icon" => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        ],
        [
            "href" => "manage_data.php",
            "label" => "Kelola Data",
            "icon" => '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
        ],
    ],
];
?>
<aside class="left-panel">
    <a href="dashboard.php" class="sidebar-logo">
        <span class="logo-dot"></span>
        <span class="logo-text">AMR Logger<small>Robot Monitoring</small></span>
    </a>
    <nav class="sidebar-menu">
        <?php foreach ($navSections as $section => $items): ?>
            <div class="menu-section"><?= htmlspecialchars($section) ?></div>
            <?php foreach ($items as $item): ?>
                <a href="<?= $item["href"] ?>"
                   class="side-link <?= $currentPage === $item["href"] ? "active" : "" ?>">
                    <svg class="side-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <?= $item["icon"] ?>
                    </svg>
                    <span><?= $item["label"] ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="right-panel">
    <header class="top-header">
        <div class="topbar-left">
            <button class="hamburger" id="sidebarToggle" aria-label="Buka/tutup menu">&#9776;</button>
            <span class="live-clock" id="liveClock"></span>
        </div>
        <div class="topbar-right">
            <div class="conn-pill" title="Jumlah robot yang sedang connected">
                <span class="conn-dot <?= $navConnClass ?>"></span>
                <span><?= $navOnlineCount ?>/<?= $navRobotTotal ?> robot online</span>
            </div>
            <div class="user-chip">
                <div class="user-avatar">OP</div>
                <div class="user-meta">
                    <div class="user-name">Operator</div>
                    <div class="user-role">AMR Monitoring</div>
                </div>
            </div>
        </div>
    </header>
    <div class="content">
