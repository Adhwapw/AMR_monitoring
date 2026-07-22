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
        "SELECT s.*, r.vehicle_id, r.robot_id_str
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
    $sql = "SELECT c.robot_id, r.vehicle_id, r.robot_id_str, c.event_type, c.message, c.occurred_at
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
        "SELECT event_type, occurred_at FROM connection_logs
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

    $secondsAgo = time() - strtotime($event["occurred_at"]);

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
