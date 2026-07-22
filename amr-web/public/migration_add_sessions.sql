-- =========================================================
-- migration_add_sessions.sql
-- Tambahan tabel data_sessions buat database robot_amr_logging
-- yang udah kebentuk sebelumnya. Jalanin ini aja, nggak perlu
-- drop/import ulang schema utama.
-- =========================================================

USE robot_amr_logging;

CREATE TABLE IF NOT EXISTS data_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(150) NOT NULL,
    robot_id INT NULL,
    floor_condition VARCHAR(100) NULL,
    load_note VARCHAR(150) NULL,
    notes TEXT NULL,
    started_at DATETIME(3) NOT NULL,
    ended_at DATETIME(3) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_session_robot
        FOREIGN KEY (robot_id) REFERENCES robots(id)
        ON DELETE SET NULL,
    KEY idx_session_time (started_at, ended_at)
) ENGINE=InnoDB;
