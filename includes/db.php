<?php
/**
 * Подключение к базе данных через PDO и создание таблицы отзывов.
 * Один и тот же код работает и с SQLite (локально), и с MySQL (Timeweb).
 */

require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (DB_DRIVER === 'mysql') {
        $dsn = 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS, $options);
    } else {
        // SQLite
        $dir = dirname(SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA journal_mode = WAL;');
    }

    init_schema($pdo);
    return $pdo;
}

/**
 * Создаёт таблицу отзывов, если её ещё нет.
 * status: 'pending' — на модерации, 'approved' — опубликован.
 */
function init_schema(PDO $pdo): void
{
    if (DB_DRIVER === 'mysql') {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS reviews (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                name       VARCHAR(80)  NOT NULL,
                contact    VARCHAR(120) NULL,
                body       TEXT         NOT NULL,
                status     VARCHAR(20)  NOT NULL DEFAULT 'pending',
                ip         VARCHAR(45)  NULL,
                created_at DATETIME     NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } else {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS reviews (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                name       TEXT NOT NULL,
                contact    TEXT,
                body       TEXT NOT NULL,
                status     TEXT NOT NULL DEFAULT 'pending',
                ip         TEXT,
                created_at TEXT NOT NULL
            )"
        );
    }
}
