<?php
session_start();
require '../db.php';

/* ---------- ПРОВЕРКА АВТОРИЗАЦИИ ---------- */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ---------- CSRF ТОКЕН ---------- */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = (int)$_SESSION['user_id'];

/* ---------- МОИ ОЦЕНКИ ФИЛЬМОВ ---------- */
$sql = "
SELECT 
    ratings.id,
    ratings.rating,
    ratings.created_at,
    movies.title,
    movies.poster_url
FROM ratings
JOIN movies ON ratings.movie_id = movies.id
WHERE ratings.user_id = ?
ORDER BY ratings.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_ratings = $stmt->fetchAll();

/* ---------- ОБРАБОТКА СООБЩЕНИЙ ОШИБОК И УСПЕХА ---------- */
$errorMsg = '';
$successMsg = '';
if (isset($_GET['password'])) {
    if ($_GET['password'] === 'success') {
        $successMsg = 'Пароль успешно изменён';
    } elseif ($_GET['password'] === 'error') {
        switch ($_GET['msg'] ?? '') {
            case 'password_mismatch': $errorMsg = 'Пароли не совпадают'; break;
            case 'password_rules': $errorMsg = 'Пароль должен содержать минимум 8 символов, 1 заглавную букву, 1 цифру и 1 спецсимвол'; break;
            case 'wrong_old': $errorMsg = 'Неверный текущий пароль'; break;
            case 'csrf': $errorMsg = 'Ошибка безопасности. Попробуйте снова'; break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Личный кабинет</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.card-shadow { box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.1); }

/* ===== Поля пароля с кнопкой показать/скрыть ===== */
.password-container { position: relative; }
.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
}

/* ===== Отступ сверху для фиксированной navbar ===== */
body { padding-top: 70px; }
.navbar { z-index: 1030; }
</style>
</head>

<body class="bg-light">

<!-- НАВИГАЦИЯ -->
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="index.php">🎬 Каталог кинофильмов</a>
        <div>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">👤 Личный кабинет</h2>

    <!-- Кнопки -->
    <div class="mb-4 d-flex gap-2 flex-wrap">
        <a href="index.php" class="btn btn-primary">📽 Каталог фильмов</a>
        <a href="watchlist.php" class="btn btn-warning">📝 Мой список к просмотру</a>
    </div>

    <!-- МОИ ОЦЕНКИ -->
    <div class="card mb-4 card-shadow">
        <div class="card-header bg-white"><h5 class="mb-0">Мои оценки фильмов</h5></div>
        <div class="card-body">
            <?php if(count($my_ratings) > 0): ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>Фильм</th><th>Оценка</th><th>Дата</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($my_ratings as $rate): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($rate['poster_url']) ?>" style="height:50px;object-fit:cover" class="me-2 rounded">
                                <?= htmlspecialchars($rate['title']) ?>
                            </td>
                            <td>⭐ <?= $rate['rating'] ?> / 5</td>
                            <td><?= date('d.m.Y H:i', strtotime($rate['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted mb-0">Вы ещё не оценили ни одного фильма.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- СМЕНА ПАРОЛЯ -->
    <div class="card card-shadow mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🔐 Сменить пароль</h5></div>
        <div class="card-body">

            <?php if($successMsg): ?>
                <div class="alert alert-success"><?= $successMsg ?></div>
            <?php endif; ?>
            <?php if($errorMsg): ?>
                <div class="alert alert-danger"><?= $errorMsg ?></div>
            <?php endif; ?>

            <form action="change_password.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="mb-3 password-container">
                    <label>Текущий пароль</label>
                    <input type="password" name="old_password" class="form-control" required>
                    <span class="toggle-password" onclick="togglePassword(this)">👁</span>
                </div>

                <div class="mb-3 password-container">
                    <label>Новый пароль</label>
                    <input type="password" name="new_password" class="form-control" required>
                    <span class="toggle-password" onclick="togglePassword(this)">👁</span>
                </div>

                <div class="mb-3 password-container">
                    <label>Повтор нового пароля</label>
                    <input type="password" name="new_password_confirm" class="form-control" required>
                    <span class="toggle-password" onclick="togglePassword(this)">👁</span>
                </div>

                <button class="btn btn-warning w-100">Сменить пароль</button>
            </form>

        </div>
    </div>

</div>

<script>
function togglePassword(el){
    const input = el.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
