<?php
/**
 * Панель модерации отзывов.
 * Вход по логину/паролю из config.php. Действия защищены CSRF-токеном.
 */

require_once __DIR__ . '/../includes/helpers.php';
app_session_start();

// --- Выход -----------------------------------------------------------------
if (($_GET['action'] ?? '') === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

$error = '';

// --- Вход ------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['do_login'])) {
    $login = trim((string) ($_POST['login'] ?? ''));
    $pass  = (string) ($_POST['password'] ?? '');

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Сессия истекла. Обновите страницу и попробуйте снова.';
    } elseif ($login === ADMIN_LOGIN && password_verify($pass, ADMIN_PASS_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль.';
    }
}

// --- Действия модерации ----------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['do'])) {
    require_admin();
    if (!csrf_check($_POST['csrf'] ?? null)) {
        http_response_code(400);
        exit('Ошибка проверки безопасности (CSRF).');
    }
    $id  = (int) ($_POST['id'] ?? 0);
    $tab = $_POST['tab'] ?? 'pending';
    switch ($_POST['do']) {
        case 'approve': set_review_status($id, 'approved'); break;
        case 'hide':    set_review_status($id, 'pending');  break;
        case 'delete':  delete_review($id);                 break;
    }
    header('Location: index.php?tab=' . urlencode($tab));
    exit;
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Модерация отзывов — Адвокат Васильев А. А.</title>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
<?php if (!is_admin()): ?>

  <!-- Форма входа -->
  <main class="login">
    <h1>Модерация отзывов</h1>
    <p class="hint">Вход для администратора сайта</p>
    <?php if ($error): ?><div class="msg msg--err"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="index.php">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
      <label for="login">Логин</label>
      <input type="text" id="login" name="login" autocomplete="username" required autofocus />
      <label for="password">Пароль</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required />
      <button class="btn btn--ok" type="submit" name="do_login" value="1">Войти</button>
    </form>
  </main>

<?php else: ?>

  <?php
    $tab = $_GET['tab'] ?? 'pending';
    $valid = ['pending', 'approved', 'all'];
    if (!in_array($tab, $valid, true)) { $tab = 'pending'; }
    $list = reviews_by_status($tab === 'all' ? null : $tab);
    $cntPending  = reviews_count('pending');
    $cntApproved = reviews_count('approved');
  ?>

  <div class="wrap">
    <div class="admin-top">
      <div>
        <h1>Модерация отзывов</h1>
        <div class="sub">Одобряйте отзывы — они сразу появляются на сайте</div>
      </div>
      <a class="logout" href="index.php?action=logout">Выйти</a>
    </div>

    <nav class="tabs">
      <a class="tab <?= $tab === 'pending' ? 'is-active' : '' ?>" href="index.php?tab=pending">
        На модерации<?php if ($cntPending): ?><span class="badge"><?= $cntPending ?></span><?php endif; ?>
      </a>
      <a class="tab <?= $tab === 'approved' ? 'is-active' : '' ?>" href="index.php?tab=approved">
        Опубликованные<?php if ($cntApproved): ?><span class="badge"><?= $cntApproved ?></span><?php endif; ?>
      </a>
      <a class="tab <?= $tab === 'all' ? 'is-active' : '' ?>" href="index.php?tab=all">Все</a>
    </nav>

    <?php if (empty($list)): ?>
      <p class="empty">Здесь пока пусто.</p>
    <?php else: ?>
      <?php foreach ($list as $r): ?>
        <?php $isApproved = $r['status'] === 'approved'; ?>
        <article class="rev">
          <div class="rev__head">
            <span class="rev__name"><?= e($r['name']) ?></span>
            <span class="rev__status rev__status--<?= $isApproved ? 'approved' : 'pending' ?>">
              <?= $isApproved ? 'Опубликован' : 'На модерации' ?>
            </span>
          </div>
          <div class="rev__meta"><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></div>
          <p class="rev__body"><?= e($r['body']) ?></p>
          <?php if (!empty($r['contact'])): ?>
            <p class="rev__contact">Контакт: <?= e($r['contact']) ?></p>
          <?php endif; ?>
          <div class="rev__actions">
            <?php if (!$isApproved): ?>
              <form method="POST" action="index.php">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                <input type="hidden" name="tab" value="<?= e($tab) ?>" />
                <button class="btn btn--ok" type="submit" name="do" value="approve">Одобрить</button>
              </form>
            <?php else: ?>
              <form method="POST" action="index.php">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                <input type="hidden" name="tab" value="<?= e($tab) ?>" />
                <button class="btn" type="submit" name="do" value="hide">Скрыть с сайта</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="index.php" onsubmit="return confirm('Удалить отзыв безвозвратно?');">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
              <input type="hidden" name="tab" value="<?= e($tab) ?>" />
              <button class="btn btn--danger" type="submit" name="do" value="delete">Удалить</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

<?php endif; ?>
</body>
</html>
