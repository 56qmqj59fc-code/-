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

/* ---------- МОИ ОЦЕНКИ ФИЛЬМОВ (Anti-IDOR) ---------- */
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- НАВИГАЦИЯ -->
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">🎬 Каталог кинофильмов</a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
    </div>
</nav>

<div class="container">

    <h2 class="mb-4">👤 Личный кабинет</h2>

    <!-- ===== МОИ ОЦЕНКИ ===== -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Мои оценки фильмов</h5>
        </div>
        <div class="card-body">

            <?php if (count($my_ratings) > 0): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Фильм</th>
                            <th>Оценка</th>
                            <th>Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($my_ratings as $rate): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($rate['poster_url']) ?>"
                                         style="height:50px;object-fit:cover"
                                         class="me-2 rounded">
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
                <p class="text-muted mb-0">
                    Вы ещё не оценили ни одного фильма.
                </p>
            <?php endif; ?>

        </div>
    </div>

    <!-- ===== СМЕНА ПАРОЛЯ ===== -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">🔐 Сменить пароль</h5>
        </div>
        <div class="card-body">

            <?php if (isset($_GET['password']) && $_GET['password'] === 'success'): ?>
                <div class="alert alert-success">
                    Пароль успешно изменён
                </div>
            <?php endif; ?>

            <form action="change_password.php" method="post">

                <input type="hidden" name="csrf_token"
                       value="<?= $_SESSION['csrf_token'] ?>">

                <div class="mb-3">
                    <label class="form-label">Текущий пароль</label>
                    <input type="password" name="old_password"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Новый пароль</label>
                    <input type="password" name="new_password"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Повтор нового пароля</label>
                    <input type="password" name="new_password_confirm"
                           class="form-control" required>
                </div>

                <button class="btn btn-warning">
                    Сменить пароль
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
