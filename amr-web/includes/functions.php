<?php
// =========================================================
// includes/functions.php
// Fungsi-fungsi bantu buat query data dan format tampilan
// =========================================================

function get_all_robots(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, robot_id_str, vehicle_id, model, current_ip, is_active FROM robots ORDER BY id");
    return $stmt->fetchAll();
}

function get_status_logs(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate, int $limit = 50, int $offset = 0): array
{
    $sql = "SELECT s.*, r.vehicle_id, r.robot_id_str
            FROM robot_status_logs s
            JOIN robots r ON r.id = s.robot_id
            WHERE 1=1";
    $params = [];

    if ($robotId) {
        $sql .= " AND s.robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND s.logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND s.logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }

    $sql .= " ORDER BY s.logged_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function count_status_logs(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate): int
{
    $sql = "SELECT COUNT(*) AS total FROM robot_status_logs s WHERE 1=1";
    $params = [];
    if ($robotId) {
        $sql .= " AND s.robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND s.logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND s.logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return (int)$stmt->fetch()["total"];
}

function get_motor_logs(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate, int $limit = 50, int $offset = 0): array
{
    $sql = "SELECT m.*, r.vehicle_id, r.robot_id_str
            FROM robot_motor_logs m
            JOIN robots r ON r.id = m.robot_id
            WHERE 1=1";
    $params = [];

    if ($robotId) {
        $sql .= " AND m.robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND m.logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND m.logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }

    $sql .= " ORDER BY m.logged_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function count_motor_logs(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate): int
{
    $sql = "SELECT COUNT(*) AS total FROM robot_motor_logs m WHERE 1=1";
    $params = [];
    if ($robotId) {
        $sql .= " AND m.robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND m.logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND m.logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return (int)$stmt->fetch()["total"];
}

function build_pagination_query(array $currentParams, int $page): string
{
    $params = $currentParams;
    $params["page"] = $page;
    return http_build_query($params);
}

// --- Fungsi buat fitur hapus data ---
// Semua tabel log yang dihitung/dihapus (robots master TIDAK ikut dihapus)
const LOG_TABLES_WITH_LOGGED_AT = ["robot_status_logs", "robot_motor_logs", "robot_imu_logs", "robot_control_lock_logs"];

function preview_delete_counts(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate): array
{
    $counts = [];
    foreach (LOG_TABLES_WITH_LOGGED_AT as $table) {
        $sql = "SELECT COUNT(*) AS total FROM {$table} WHERE 1=1";
        $params = [];
        if ($robotId) {
            $sql .= " AND robot_id = :robot_id";
            $params[":robot_id"] = $robotId;
        }
        if ($startDate) {
            $sql .= " AND logged_at >= :start_date";
            $params[":start_date"] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND logged_at <= :end_date";
            $params[":end_date"] = $endDate;
        }
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $counts[$table] = (int)$stmt->fetch()["total"];
    }

    // connection_logs pakai kolom occurred_at, bukan logged_at
    $sql = "SELECT COUNT(*) AS total FROM connection_logs WHERE 1=1";
    $params = [];
    if ($robotId) {
        $sql .= " AND robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND occurred_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND occurred_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $counts["connection_logs"] = (int)$stmt->fetch()["total"];

    return $counts;
}

function delete_logs(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate): array
{
    $deleted = [];

    foreach (LOG_TABLES_WITH_LOGGED_AT as $table) {
        $sql = "DELETE FROM {$table} WHERE 1=1";
        $params = [];
        if ($robotId) {
            $sql .= " AND robot_id = :robot_id";
            $params[":robot_id"] = $robotId;
        }
        if ($startDate) {
            $sql .= " AND logged_at >= :start_date";
            $params[":start_date"] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND logged_at <= :end_date";
            $params[":end_date"] = $endDate;
        }
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $deleted[$table] = $stmt->rowCount();
    }

    $sql = "DELETE FROM connection_logs WHERE 1=1";
    $params = [];
    if ($robotId) {
        $sql .= " AND robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND occurred_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND occurred_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $deleted["connection_logs"] = $stmt->rowCount();

    return $deleted;
}

function delete_single_status_log(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare("DELETE FROM robot_status_logs WHERE id = :id");
    $stmt->execute([":id" => $id]);
    return $stmt->rowCount();
}

function delete_single_motor_log(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare("DELETE FROM robot_motor_logs WHERE id = :id");
    $stmt->execute([":id" => $id]);
    return $stmt->rowCount();
}

// =========================================================
// Deteksi trip otomatis dari data robot_task_logs
// Trip = perjalanan dari satu target_id ke target_id lain,
// dideteksi dari perubahan task_status (RUNNING -> COMPLETED/FAILED/CANCELED)
// =========================================================

function get_task_logs_for_trips(PDO $pdo, int $robotId, ?string $startDate, ?string $endDate): array
{
    $sql = "SELECT logged_at, task_status, task_type, target_id, finished_path, unfinished_path
            FROM robot_task_logs
            WHERE robot_id = :robot_id";
    $params = [":robot_id" => $robotId];
    if ($startDate) {
        $sql .= " AND logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $sql .= " ORDER BY logged_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_status_logs_for_trips(PDO $pdo, int $robotId, ?string $startDate, ?string $endDate): array
{
    $sql = "SELECT logged_at, battery_level, pos_x, pos_y, last_station, current_station
            FROM robot_status_logs
            WHERE robot_id = :robot_id";
    $params = [":robot_id" => $robotId];
    if ($startDate) {
        $sql .= " AND logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $sql .= " ORDER BY logged_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Cari baris status_logs terdekat SEBELUM atau PAS di timestamp tertentu.
// $statusLogs harus udah terurut ascending by logged_at.
function find_status_at_or_before(array $statusLogs, string $timestamp): ?array
{
    $result = null;
    foreach ($statusLogs as $row) {
        if ($row["logged_at"] <= $timestamp) {
            $result = $row;
        } else {
            break;
        }
    }
    return $result;
}

function determine_final_destination(?int $taskType, ?string $targetId, ?array $unfinishedPath): ?string
{
    // Task type 3 = path navigation lewat banyak titik perantara.
    // target_id di sini cuma nunjukin titik perantara yang lagi dituju SAAT INI,
    // bukan tujuan akhir. Tujuan akhir yang bener itu elemen TERAKHIR di unfinished_path.
    if ($taskType === 3) {
        if (!empty($unfinishedPath)) {
            return end($unfinishedPath);
        }
        // unfinished_path udah kosong (hampir/udah nyampe), fallback ke target_id terakhir
        return $targetId;
    }

    // Task type 1/2 (navigasi langsung), target_id memang tujuan akhirnya
    return $targetId;
}

function compute_trips(array $taskLogs, array $statusLogs, array $stationsById): array
{
    $trips = [];
    $current = null; // trip yang lagi "terbuka"

    foreach ($taskLogs as $row) {
        $status = $row["task_status"] !== null ? (int)$row["task_status"] : null;
        $taskType = $row["task_type"] !== null ? (int)$row["task_type"] : null;
        $targetId = $row["target_id"];
        $unfinishedPath = $row["unfinished_path"] ? json_decode($row["unfinished_path"], true) : null;

        $finalDestination = determine_final_destination($taskType, $targetId, $unfinishedPath);

        // Task lagi RUNNING menuju tujuan akhir tertentu
        if ($status === 2 && $finalDestination) {
            if ($current === null) {
                // Trip baru mulai
                $statusAtStart = find_status_at_or_before($statusLogs, $row["logged_at"]);
                $current = [
                    "to_station" => $finalDestination,
                    "from_station" => $statusAtStart["last_station"] ?? $statusAtStart["current_station"] ?? null,
                    "started_at" => $row["logged_at"],
                    "completed_at" => null,
                    "status" => "berjalan",
                    "battery_start" => $statusAtStart["battery_level"] ?? null,
                ];
            } elseif ($current["to_station"] !== $finalDestination) {
                // Tujuan akhir BENERAN berubah (bukan cuma titik perantara path yang lagi dilewatin)
                // -> baru dianggap trip lama terputus, trip baru mulai
                $current["completed_at"] = $row["logged_at"];
                $current["status"] = "terputus";
                $trips[] = $current;

                $statusAtStart = find_status_at_or_before($statusLogs, $row["logged_at"]);
                $current = [
                    "to_station" => $finalDestination,
                    "from_station" => $current["to_station"],
                    "started_at" => $row["logged_at"],
                    "completed_at" => null,
                    "status" => "berjalan",
                    "battery_start" => $statusAtStart["battery_level"] ?? null,
                ];
            }
            // Kalau to_station sama (masih di path menuju tujuan yang sama), nggak ngapa-ngapain,
            // biarin trip yang lagi terbuka terus jalan meskipun target_id perantaranya berubah-ubah.
            continue;
        }

        // Task selesai/gagal/dibatalkan -> tutup trip yang lagi terbuka
        if ($current !== null && in_array($status, [4, 5, 6], true)) {
            $statusAtEnd = find_status_at_or_before($statusLogs, $row["logged_at"]);
            $current["completed_at"] = $row["logged_at"];
            $current["status"] = match ($status) {
                4 => "selesai",
                5 => "gagal",
                6 => "dibatalkan",
                default => "selesai",
            };
            $current["battery_end"] = $statusAtEnd["battery_level"] ?? null;
            $trips[] = $current;
            $current = null;
        }
    }

    // Kalau masih ada trip yang belum ketutup pas data habis (robot masih jalan pas rentang waktu selesai)
    if ($current !== null) {
        $current["status"] = "belum selesai (data terputus)";
        $trips[] = $current;
    }

    // Lengkapi tiap trip dengan jarak garis lurus (kalau koordinat station tersedia) dan durasi
    foreach ($trips as &$trip) {
        $fromStation = $trip["from_station"] ? ($stationsById[$trip["from_station"]] ?? null) : null;
        $toStation = $stationsById[$trip["to_station"]] ?? null;

        $trip["distance_m"] = null;
        if ($fromStation && $toStation) {
            $trip["distance_m"] = calculate_distance(
                (float)$fromStation["x"], (float)$fromStation["y"],
                (float)$toStation["x"], (float)$toStation["y"]
            );
        }

        $trip["duration_seconds"] = null;
        if ($trip["completed_at"]) {
            $trip["duration_seconds"] = strtotime($trip["completed_at"]) - strtotime($trip["started_at"]);
        }
    }
    unset($trip);

    // Urutan terbaru dulu, biar enak diliat di tabel
    return array_reverse($trips);
}

function get_trips_for_robot(PDO $pdo, int $robotId, ?string $startDate = null, ?string $endDate = null): array
{
    $taskLogs = get_task_logs_for_trips($pdo, $robotId, $startDate, $endDate);
    $statusLogs = get_status_logs_for_trips($pdo, $robotId, $startDate, $endDate);

    $stations = get_stations_for_robot($pdo, $robotId);
    $stationsById = [];
    foreach ($stations as $s) {
        $stationsById[$s["station_id"]] = $s;
    }

    return compute_trips($taskLogs, $statusLogs, $stationsById);
}

// Hitung ulang trip dari data mentah (robot_task_logs dkk), lalu simpan
// hasilnya secara permanen ke tabel robot_trips. Trip lama di rentang
// waktu yang sama dihapus dulu biar nggak dobel kalau di-sync berkali-kali.
function sync_trips_for_robot(PDO $pdo, int $robotId, ?string $startDate = null, ?string $endDate = null): int
{
    $trips = get_trips_for_robot($pdo, $robotId, $startDate, $endDate);

    $deleteSql = "DELETE FROM robot_trips WHERE robot_id = :robot_id";
    $deleteParams = [":robot_id" => $robotId];
    if ($startDate) {
        $deleteSql .= " AND started_at >= :start_date";
        $deleteParams[":start_date"] = $startDate;
    }
    if ($endDate) {
        $deleteSql .= " AND started_at <= :end_date";
        $deleteParams[":end_date"] = $endDate;
    }
    $stmt = $pdo->prepare($deleteSql);
    $stmt->execute($deleteParams);

    $insertStmt = $pdo->prepare(
        "INSERT INTO robot_trips (
            robot_id, from_station, to_station, started_at, completed_at,
            duration_seconds, distance_m, battery_start, battery_end, status
        ) VALUES (:robot_id, :from_station, :to_station, :started_at, :completed_at,
            :duration_seconds, :distance_m, :battery_start, :battery_end, :status)"
    );

    $count = 0;
    foreach ($trips as $trip) {
        $insertStmt->execute([
            ":robot_id" => $robotId,
            ":from_station" => $trip["from_station"],
            ":to_station" => $trip["to_station"],
            ":started_at" => $trip["started_at"],
            ":completed_at" => $trip["completed_at"],
            ":duration_seconds" => $trip["duration_seconds"],
            ":distance_m" => $trip["distance_m"],
            ":battery_start" => $trip["battery_start"],
            ":battery_end" => $trip["battery_end"] ?? null,
            ":status" => $trip["status"],
        ]);
        $count++;
    }

    return $count;
}

function get_trips_from_table(PDO $pdo, int $robotId, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "SELECT * FROM robot_trips WHERE robot_id = :robot_id";
    $params = [":robot_id" => $robotId];
    if ($startDate) {
        $sql .= " AND started_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND started_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $sql .= " ORDER BY started_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_last_trip_sync_info(PDO $pdo, int $robotId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT MAX(synced_at) AS last_synced_at, COUNT(*) AS total_trips
         FROM robot_trips WHERE robot_id = :robot_id"
    );
    $stmt->execute([":robot_id" => $robotId]);
    $row = $stmt->fetch();
    return ($row && $row["last_synced_at"]) ? $row : null;
}

function get_stations_for_robot(PDO $pdo, ?int $robotId = null): array
{
    $sql = "SELECT s.*, r.vehicle_id, r.robot_id_str
            FROM robot_stations s
            JOIN robots r ON r.id = s.robot_id
            WHERE 1=1";
    $params = [];
    if ($robotId) {
        $sql .= " AND s.robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    $sql .= " ORDER BY r.vehicle_id, s.station_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function calculate_distance(float $x1, float $y1, float $x2, float $y2): float
{
    return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
}

function get_all_sessions(PDO $pdo): array
{
    $sql = "SELECT s.*, r.vehicle_id, r.robot_id_str
            FROM data_sessions s
            LEFT JOIN robots r ON r.id = s.robot_id
            ORDER BY s.started_at DESC";
    return $pdo->query($sql)->fetchAll();
}

function get_active_session(PDO $pdo): ?array
{
    $stmt = $pdo->query(
        "SELECT s.*, r.vehicle_id, r.robot_id_str,
                TIMESTAMPDIFF(SECOND, s.started_at, NOW()) / 60 AS minutes_running
         FROM data_sessions s
         LEFT JOIN robots r ON r.id = s.robot_id
         WHERE s.ended_at IS NULL
         ORDER BY s.started_at DESC LIMIT 1"
    );
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_session_by_id(PDO $pdo, int $sessionId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT s.*, r.vehicle_id, r.robot_id_str
         FROM data_sessions s
         LEFT JOIN robots r ON r.id = s.robot_id
         WHERE s.id = :id"
    );
    $stmt->execute([":id" => $sessionId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function create_session(PDO $pdo, string $name, ?int $robotId, ?string $floorCondition, ?string $loadNote, ?string $notes): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO data_sessions (session_name, robot_id, floor_condition, load_note, notes, started_at)
         VALUES (:name, :robot_id, :floor, :load, :notes, :started_at)"
    );
    $stmt->execute([
        ":name" => $name,
        ":robot_id" => $robotId,
        ":floor" => $floorCondition,
        ":load" => $loadNote,
        ":notes" => $notes,
        ":started_at" => date("Y-m-d H:i:s.v"),
    ]);
    return (int)$pdo->lastInsertId();
}

function end_session(PDO $pdo, int $sessionId): void
{
    $stmt = $pdo->prepare("UPDATE data_sessions SET ended_at = :ended_at WHERE id = :id AND ended_at IS NULL");
    $stmt->execute([":ended_at" => date("Y-m-d H:i:s.v"), ":id" => $sessionId]);
}

function delete_session(PDO $pdo, int $sessionId): void
{
    // Cuma hapus metadata sesinya, data log yang udah kesimpen di tabel lain nggak ikut kehapus
    $stmt = $pdo->prepare("DELETE FROM data_sessions WHERE id = :id");
    $stmt->execute([":id" => $sessionId]);
}

function count_status_logs_in_session(PDO $pdo, array $session): int
{
    $sql = "SELECT COUNT(*) FROM robot_status_logs WHERE logged_at >= :started_at";
    $params = [":started_at" => $session["started_at"]];
    if ($session["ended_at"]) {
        $sql .= " AND logged_at <= :ended_at";
        $params[":ended_at"] = $session["ended_at"];
    }
    if ($session["robot_id"]) {
        $sql .= " AND robot_id = :robot_id";
        $params[":robot_id"] = $session["robot_id"];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}
function get_connection_status(PDO $pdo): array
{
    // Ambil event koneksi terakhir per robot, buat lihat status terkini
    $sql = "SELECT c.robot_id, r.vehicle_id, r.robot_id_str, c.event_type, c.message, c.occurred_at,
                   TIMESTAMPDIFF(SECOND, c.occurred_at, NOW()) AS seconds_ago
            FROM connection_logs c
            JOIN robots r ON r.id = c.robot_id
            INNER JOIN (
                SELECT robot_id, MAX(occurred_at) AS max_occurred
                FROM connection_logs
                GROUP BY robot_id
            ) latest ON latest.robot_id = c.robot_id AND latest.max_occurred = c.occurred_at
            ORDER BY c.occurred_at DESC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function get_latest_task_per_robot(PDO $pdo): array
{
    $sql = "SELECT t.*, r.vehicle_id, r.robot_id_str
            FROM robot_task_logs t
            JOIN robots r ON r.id = t.robot_id
            INNER JOIN (
                SELECT robot_id, MAX(logged_at) AS max_logged
                FROM robot_task_logs
                GROUP BY robot_id
            ) latest ON latest.robot_id = t.robot_id AND latest.max_logged = t.logged_at
            ORDER BY r.vehicle_id";
    $rows = $pdo->query($sql)->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $result[$row["robot_id"]] = $row;
    }
    return $result;
}

function format_task_status(?int $status): array
{
    return match ($status) {
        0 => ["label" => "Idle", "class" => "badge-neutral"],
        1 => ["label" => "Menunggu", "class" => "badge-neutral"],
        2 => ["label" => "Bergerak", "class" => "badge-ok"],
        3 => ["label" => "Ditunda", "class" => "badge-warn"],
        4 => ["label" => "Selesai", "class" => "badge-ok"],
        5 => ["label" => "Gagal", "class" => "badge-error"],
        6 => ["label" => "Dibatalkan", "class" => "badge-warn"],
        default => ["label" => "Belum ada data", "class" => "badge-neutral"],
    };
}

function format_task_type(?int $type): string
{
    return match ($type) {
        0 => "Tanpa navigasi",
        1 => "Navigasi bebas ke titik",
        2 => "Navigasi bebas ke site",
        3 => "Navigasi path ke site",
        7 => "Rotasi translasi",
        100 => "Lainnya",
        default => "-",
    };
}

function get_latest_status_per_robot(PDO $pdo): array
{
    $sql = "SELECT s.*, r.vehicle_id, r.robot_id_str
            FROM robot_status_logs s
            JOIN robots r ON r.id = s.robot_id
            INNER JOIN (
                SELECT robot_id, MAX(logged_at) AS max_logged
                FROM robot_status_logs
                GROUP BY robot_id
            ) latest ON latest.robot_id = s.robot_id AND latest.max_logged = s.logged_at
            ORDER BY r.vehicle_id";
    return $pdo->query($sql)->fetchAll();
}

function get_latest_connection_event(PDO $pdo, int $robotId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT event_type, occurred_at, TIMESTAMPDIFF(SECOND, occurred_at, NOW()) AS seconds_ago
         FROM connection_logs
         WHERE robot_id = :robot_id ORDER BY occurred_at DESC LIMIT 1"
    );
    $stmt->execute([":robot_id" => $robotId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Kalau nggak ada event baru dalam sekian detik ini, robot dianggap disconnected
// walaupun event terakhir yang kesimpen statusnya "connected"
const CONNECTION_STALE_SECONDS = 15;

function get_effective_connection_status(?array $event): array
{
    if (!$event) {
        return ["label" => "belum ada data", "class" => "badge-neutral"];
    }

    // Selisih waktu dihitung di MySQL (TIMESTAMPDIFF), bukan di PHP, biar nggak
    // ketemu masalah beda timezone antara server PHP dan waktu yang disimpen poller.
    $secondsAgo = (int)$event["seconds_ago"];

    if ($event["event_type"] === "connected" && $secondsAgo > CONNECTION_STALE_SECONDS) {
        return ["label" => "disconnected", "class" => "badge-error"];
    }

    return match ($event["event_type"]) {
        "connected" => ["label" => "connected", "class" => "badge-ok"],
        "timeout", "disconnected" => ["label" => $event["event_type"], "class" => "badge-warn"],
        "parse_error" => ["label" => "parse_error", "class" => "badge-error"],
        default => ["label" => $event["event_type"], "class" => "badge-neutral"],
    };
}

function get_battery_series(PDO $pdo, int $robotId, int $limitPoints = 50): array
{
    $stmt = $pdo->prepare(
        "SELECT logged_at, battery_level, pos_x, pos_y
         FROM robot_status_logs
         WHERE robot_id = :robot_id
         ORDER BY logged_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue(":robot_id", $robotId, PDO::PARAM_INT);
    $stmt->bindValue(":limit", $limitPoints, PDO::PARAM_INT);
    $stmt->execute();
    return array_reverse($stmt->fetchAll());
}

function get_motor_speed_series(PDO $pdo, int $robotId, int $limitPoints = 50): array
{
    $stmt = $pdo->prepare(
        "SELECT logged_at, motor_name, speed
         FROM robot_motor_logs
         WHERE robot_id = :robot_id
         ORDER BY logged_at DESC
         LIMIT :limit"
    );
    // limit dikali 2 karena tiap waktu ada 2 baris (motor kiri & kanan)
    $stmt->bindValue(":robot_id", $robotId, PDO::PARAM_INT);
    $stmt->bindValue(":limit", $limitPoints * 2, PDO::PARAM_INT);
    $stmt->execute();
    return array_reverse($stmt->fetchAll());
}
function get_combined_export_data(PDO $pdo, ?int $robotId, ?string $startDate, ?string $endDate, int $limit = 100000): array
{
    // 1. Ambil status log sebagai timeline dasar (satu baris = satu siklus polling)
    $sql = "SELECT s.robot_id, s.logged_at, r.vehicle_id, r.robot_id_str,
                   s.battery_level, s.battery_temp, s.charging, s.voltage, s.`current` AS battery_current,
                   s.pos_x, s.pos_y, s.angle, s.loc_confidence, s.current_station, s.last_station,
                   s.vx, s.vy, s.w, s.is_stop
            FROM robot_status_logs s
            JOIN robots r ON r.id = s.robot_id
            WHERE 1=1";
    $params = [];
    if ($robotId) {
        $sql .= " AND s.robot_id = :robot_id";
        $params[":robot_id"] = $robotId;
    }
    if ($startDate) {
        $sql .= " AND s.logged_at >= :start_date";
        $params[":start_date"] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND s.logged_at <= :end_date";
        $params[":end_date"] = $endDate;
    }
    $sql .= " ORDER BY s.logged_at ASC LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();
    $statusRows = $stmt->fetchAll();

    if (empty($statusRows)) {
        return ["rows" => [], "motor_names" => []];
    }

    // Batas waktu aktual dari status log yang kepake, buat query imu & motor biar konsisten
    $minTime = $statusRows[0]["logged_at"];
    $maxTime = $statusRows[count($statusRows) - 1]["logged_at"];

    // 2. Ambil IMU log di rentang yang sama, index by "robot_id|logged_at"
    $imuStmt = $pdo->prepare(
        "SELECT robot_id, logged_at, yaw, roll, pitch, acc_x, acc_y, acc_z
         FROM robot_imu_logs
         WHERE logged_at BETWEEN :min_time AND :max_time" . ($robotId ? " AND robot_id = :robot_id" : "")
    );
    $imuStmt->bindValue(":min_time", $minTime);
    $imuStmt->bindValue(":max_time", $maxTime);
    if ($robotId) {
        $imuStmt->bindValue(":robot_id", $robotId, PDO::PARAM_INT);
    }
    $imuStmt->execute();
    $imuByKey = [];
    foreach ($imuStmt->fetchAll() as $row) {
        $key = $row["robot_id"] . "|" . $row["logged_at"];
        $imuByKey[$key] = $row;
    }

    // 3. Ambil motor log di rentang yang sama, index by "robot_id|logged_at" -> motor_name -> field
    $motorStmt = $pdo->prepare(
        "SELECT robot_id, logged_at, motor_name, speed, position, `current` AS motor_current, error_code, err
         FROM robot_motor_logs
         WHERE logged_at BETWEEN :min_time AND :max_time" . ($robotId ? " AND robot_id = :robot_id" : "")
    );
    $motorStmt->bindValue(":min_time", $minTime);
    $motorStmt->bindValue(":max_time", $maxTime);
    if ($robotId) {
        $motorStmt->bindValue(":robot_id", $robotId, PDO::PARAM_INT);
    }
    $motorStmt->execute();
    $motorByKey = [];
    $motorNames = [];
    foreach ($motorStmt->fetchAll() as $row) {
        $key = $row["robot_id"] . "|" . $row["logged_at"];
        $name = $row["motor_name"];
        $motorNames[$name] = true;
        $motorByKey[$key][$name] = $row;
    }
    $motorNames = array_keys($motorNames);
    sort($motorNames);

    // 4. Gabungin semuanya per baris status
    $combined = [];
    foreach ($statusRows as $row) {
        $key = $row["robot_id"] . "|" . $row["logged_at"];

        $imu = $imuByKey[$key] ?? [];
        $row["imu_yaw"] = $imu["yaw"] ?? "";
        $row["imu_roll"] = $imu["roll"] ?? "";
        $row["imu_pitch"] = $imu["pitch"] ?? "";
        $row["imu_acc_x"] = $imu["acc_x"] ?? "";
        $row["imu_acc_y"] = $imu["acc_y"] ?? "";
        $row["imu_acc_z"] = $imu["acc_z"] ?? "";

        $motors = $motorByKey[$key] ?? [];
        foreach ($motorNames as $name) {
            $m = $motors[$name] ?? null;
            $row["speed_{$name}"] = $m["speed"] ?? "";
            $row["position_{$name}"] = $m["position"] ?? "";
            $row["current_{$name}"] = $m["motor_current"] ?? "";
            $row["err_{$name}"] = $m ? ($m["err"] ? "1" : "0") : "";
        }

        $combined[] = $row;
    }

    return ["rows" => $combined, "motor_names" => $motorNames];
}
function estimate_battery_remaining(PDO $pdo, int $robotId, int $lookbackPoints = 20): ?array
{
    $stmt = $pdo->prepare(
        "SELECT logged_at, battery_level, charging
         FROM robot_status_logs
         WHERE robot_id = :robot_id AND battery_level IS NOT NULL
         ORDER BY logged_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue(":robot_id", $robotId, PDO::PARAM_INT);
    $stmt->bindValue(":limit", $lookbackPoints, PDO::PARAM_INT);
    $stmt->execute();
    $rows = array_reverse($stmt->fetchAll());

    if (count($rows) < 3) {
        return null; // data belum cukup buat estimasi yang masuk akal
    }

    // Kalau lagi charging, estimasi "sisa waktu habis" nggak relevan
    if ($rows[count($rows) - 1]["charging"]) {
        return ["status" => "charging"];
    }

    // Regresi linear sederhana: level baterai (%) terhadap waktu (menit sejak titik pertama)
    $t0 = strtotime($rows[0]["logged_at"]);
    $n = count($rows);
    $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;

    foreach ($rows as $row) {
        $x = (strtotime($row["logged_at"]) - $t0) / 60; // menit
        $y = (float)$row["battery_level"] * 100; // persen
        $sumX += $x;
        $sumY += $y;
        $sumXY += $x * $y;
        $sumXX += $x * $x;
    }

    $denominator = ($n * $sumXX - $sumX * $sumX);
    if ($denominator == 0) {
        return null;
    }

    $slope = ($n * $sumXY - $sumX * $sumY) / $denominator; // persen per menit
    $currentLevel = (float)$rows[$n - 1]["battery_level"] * 100;

    if ($slope >= -0.01) {
        // Baterai stabil/naik (bukan lagi charging tapi nggak turun signifikan), nggak ada estimasi masuk akal
        return ["status" => "stable", "rate_per_min" => round($slope, 3)];
    }

    $minutesRemaining = $currentLevel / abs($slope);

    return [
        "status" => "discharging",
        "rate_per_min" => round($slope, 3),
        "current_level" => round($currentLevel, 1),
        "minutes_remaining" => round($minutesRemaining),
        "sample_points" => $n,
    ];
}

function get_discharge_rate_series(PDO $pdo, int $robotId, int $limitPoints = 50): array
{
    $rows = get_battery_series($pdo, $robotId, $limitPoints);
    $result = [];

    for ($i = 1; $i < count($rows); $i++) {
        $prev = $rows[$i - 1];
        $curr = $rows[$i];
        if ($prev["battery_level"] === null || $curr["battery_level"] === null) {
            continue;
        }
        $minutesDiff = (strtotime($curr["logged_at"]) - strtotime($prev["logged_at"])) / 60;
        if ($minutesDiff <= 0) {
            continue;
        }
        $levelDiff = ((float)$curr["battery_level"] - (float)$prev["battery_level"]) * 100;
        $result[] = [
            "logged_at" => $curr["logged_at"],
            "rate_per_min" => round($levelDiff / $minutesDiff, 4),
        ];
    }

    return $result;
}
function format_datetime(?string $value): string
{
    if (!$value) {
        return "-";
    }
    return date("d/m/Y H:i:s", strtotime($value));
}

function format_number($value, int $decimals = 3): string
{
    if ($value === null) {
        return "-";
    }
    return number_format((float)$value, $decimals);
}
