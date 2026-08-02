<?php
/**
 * Настройки проекта.
 *
 * Локально (Mac): оставьте DB_DRIVER = 'sqlite' — база создастся сама в data/reviews.sqlite,
 * ничего устанавливать не нужно.
 *
 * На Timeweb: поставьте DB_DRIVER = 'mysql' и впишите данные базы из панели Timeweb.
 */

// --- База данных -----------------------------------------------------------
// 'sqlite' — для локального запуска;  'mysql' — для хостинга Timeweb.
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite');

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
