<?php
/**
 * Приём отзыва из формы на сайте (AJAX).
 * Отвечает JSON: {"success": true|false, "message": "..."}
 */

require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

function json_out(bool $ok, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(false, 'Метод не поддерживается.', 405);
}

// Ловушка для ботов: скрытое поле должно оставаться пустым.
if (!empty($_POST['website'])) {
    // Делаем вид, что всё хорошо, но ничего не сохраняем.
    json_out(true, 'Спасибо! Ваш отзыв отправлен на модерацию.');
}

$name    = trim((string) ($_POST['name'] ?? ''));
$contact = trim((string) ($_POST['contact'] ?? ''));
$body    = trim((string) ($_POST['body'] ?? ''));

// Валидация
if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
    json_out(false, 'Укажите имя (от 2 до 80 символов).', 422);
}
if (mb_strlen($body) < 10 || mb_strlen($body) > 1500) {
    json_out(false, 'Отзыв должен быть от 10 до 1500 символов.', 422);
}
if (mb_strlen($contact) > 120) {
    json_out(false, 'Слишком длинное контактное поле.', 422);
}

// Простой анти-флуд: не чаще одного отзыва в 30 секунд с одной сессии.
app_session_start();
$now  = time();
$last = $_SESSION['last_review_ts'] ?? 0;
if ($now - $last < 30) {
    json_out(false, 'Вы недавно отправили отзыв. Попробуйте чуть позже.', 429);
}

try {
    add_review($name, $contact, $body);
    $_SESSION['last_review_ts'] = $now;
} catch (Throwable $ex) {
    json_out(false, 'Техническая ошибка при сохранении. Попробуйте позже.', 500);
}

json_out(true, 'Спасибо! Ваш отзыв отправлен на модерацию и появится на сайте после проверки.');
