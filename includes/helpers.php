<?php
/**
 * Вспомогательные функции: безопасность, CSRF, авторизация, работа с отзывами.
 */

require_once __DIR__ . '/db.php';

/** Безопасный старт сессии с адекватными флагами cookie. */
function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Экранирование для вывода в HTML. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** CSRF-токен для текущей сессии. */
function csrf_token(): string
{
    app_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Проверка CSRF-токена из формы. */
function csrf_check(?string $token): bool
{
    app_session_start();
    return !empty($_SESSION['csrf']) && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}

/** Авторизован ли администратор. */
function is_admin(): bool
{
    app_session_start();
    return !empty($_SESSION['admin']);
}

/** Требовать авторизацию — иначе редирект на вход. */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

/** IP-адрес отправителя (для простого анти-спама). */
function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/**
 * Добавить отзыв (со статусом «на модерации»).
 * Возвращает id новой записи.
 */
function add_review(string $name, ?string $contact, string $body): int
{
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO reviews (name, contact, body, status, ip, created_at)
         VALUES (:name, :contact, :body, :status, :ip, :created_at)'
    );
    $stmt->execute([
        ':name'       => $name,
        ':contact'    => $contact !== '' ? $contact : null,
        ':body'       => $body,
        ':status'     => 'pending',
        ':ip'         => client_ip(),
        ':created_at' => date('Y-m-d H:i:s'),
    ]);
    return (int) $pdo->lastInsertId();
}

/** Список одобренных отзывов для показа на сайте. */
function approved_reviews(int $limit = 30): array
{
    $pdo = db();
    $stmt = $pdo->prepare(
        "SELECT name, contact, body, created_at
         FROM reviews WHERE status = 'approved'
         ORDER BY created_at DESC, id DESC
         LIMIT :lim"
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Отзывы для панели модерации (по статусу или все). */
function reviews_by_status(?string $status = null): array
{
    $pdo = db();
    if ($status === null) {
        return $pdo->query(
            'SELECT * FROM reviews ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }
    $stmt = $pdo->prepare(
        'SELECT * FROM reviews WHERE status = :s ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([':s' => $status]);
    return $stmt->fetchAll();
}

/** Счётчик отзывов по статусу (для бейджей в панели). */
function reviews_count(string $status): int
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE status = :s');
    $stmt->execute([':s' => $status]);
    return (int) $stmt->fetchColumn();
}

/** Сменить статус отзыва (approve / hide). */
function set_review_status(int $id, string $status): void
{
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE reviews SET status = :s WHERE id = :id');
    $stmt->execute([':s' => $status, ':id' => $id]);
}

/** Удалить отзыв. */
function delete_review(int $id): void
{
    $pdo = db();
    $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = :id');
    $stmt->execute([':id' => $id]);
}
