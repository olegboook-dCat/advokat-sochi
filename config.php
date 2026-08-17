<?php
/**
 * Настройки проекта.
 *
 * Драйвер базы выбирается АВТОМАТИЧЕСКИ:
 *   - локально под `php -S` (встроенный сервер) → SQLite (ничего ставить не нужно);
 *   - на хостинге (Apache/nginx на Timeweb)      → MySQL.
 * Поэтому один и тот же config.php работает и локально, и на Timeweb —
 * переключать вручную ничего не надо. Нужно лишь заполнить доступы MySQL ниже.
 */

// --- База данных -----------------------------------------------------------
// cli-server = встроенный сервер `php -S` (локальная разработка) → SQLite; иначе MySQL.
define('DB_DRIVER', php_sapi_name() === 'cli-server' ? 'sqlite' : 'mysql');

// Путь к файлу SQLite (используется только при DB_DRIVER = 'sqlite')
define('SQLITE_PATH', __DIR__ . '/data/reviews.sqlite');

// Параметры MySQL (используются только при DB_DRIVER = 'mysql')
define('MYSQL_HOST', getenv('DB_HOST') ?: 'localhost');
define('MYSQL_NAME', getenv('DB_NAME') ?: 'имя_базы');       // ← из панели Timeweb
define('MYSQL_USER', getenv('DB_USER') ?: 'пользователь');    // ← из панели Timeweb
define('MYSQL_PASS', getenv('DB_PASS') ?: 'пароль');          // ← из панели Timeweb

// --- Доступ в панель модерации --------------------------------------------
// Логин администратора (адвоката)
define('ADMIN_LOGIN', 'admin');

// Хэш пароля. НЕ храните пароль в открытом виде.
// Значение по умолчанию соответствует паролю: changeme
// Сгенерировать новый хэш: php -r "echo password_hash('ВАШ_ПАРОЛЬ', PASSWORD_DEFAULT), PHP_EOL;"
define('ADMIN_PASS_HASH', '$2y$12$Iep8BwwY2fKBDHP222JH/ewZaqQgsITya8tGmvwBP4JxXR343COFC');

// --- Прочее ----------------------------------------------------------------
// Секрет для CSRF-токенов (замените на свою случайную строку)
define('APP_SECRET', 'change-this-to-a-long-random-string');

// Показывать примеры-отзывы, пока в базе нет одобренных (true/false)
define('SHOW_SAMPLE_REVIEWS', true);
