-- Схема базы для MySQL (Timeweb).
-- Выполните этот SQL в phpMyAdmin вашей базы на Timeweb.
-- (Локально на SQLite таблица создаётся сама — этот файл не нужен.)

CREATE TABLE IF NOT EXISTS reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(80)  NOT NULL,
    contact    VARCHAR(120) NULL,
    body       TEXT         NOT NULL,
    status     VARCHAR(20)  NOT NULL DEFAULT 'pending',
    ip         VARCHAR(45)  NULL,
    created_at DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
