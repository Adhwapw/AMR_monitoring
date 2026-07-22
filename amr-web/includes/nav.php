<?php
// =========================================================
// includes/nav.php
// Partial navigasi, di-include di tiap halaman public/
// Otomatis nandain menu aktif berdasarkan nama file saat ini
// =========================================================

$currentPage = basename($_SERVER["PHP_SELF"]);

$navItems = [
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
    [
        "href" => "sessions.php",
        "label" => "Sesi Data",
        "icon" => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
    ],
    [
        "href" => "manage_data.php",
        "label" => "Kelola Data",
        "icon" => '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
    ],
];
?>
<header class="topbar">
    <div class="topbar-inner">
        <a href="dashboard.php" class="brand">
            <span class="brand-dot"></span>
            AMR Logger
        </a>
        <nav class="main-nav">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= $item["href"] ?>"
                   class="nav-link <?= $currentPage === $item["href"] ? "active" : "" ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <?= $item["icon"] ?>
                    </svg>
                    <span><?= $item["label"] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
