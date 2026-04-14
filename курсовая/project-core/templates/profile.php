<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Личный кабинет — КиноМир</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🎬 КиноМир</a>
        <a class="btn btn-outline-light btn-sm" href="index.php">На главную</a>
    </div>
</nav>

<div class="container py-4">

    <!-- PROFILE HEADER -->
    <div class="card shadow-sm border-0 profile-card p-4 mb-4">

        <div class="profile-header d-flex align-items-center justify-content-between flex-wrap gap-3">

            <div class="d-flex align-items-center profile-user">

                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'User') ?>&background=6f42c1&color=fff&size=128"
                     class="rounded-circle shadow-sm profile-avatar"
                     width="70" height="70" alt="Avatar">

                <div>
                    <h3 class="mb-1 fw-bold">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Пользователь') ?>
                    </h3>

                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        <span class="badge rounded-pill bg-<?= $_SESSION['role'] === 'admin' ? 'warning text-dark' : 'info text-white' ?> px-3">
                            <?= $_SESSION['role'] === 'admin' ? '🛡 Администратор' : '👤 Зритель' ?>
                        </span>

                        <a href="index.php?route=logout"
                           class="btn btn-outline-danger btn-sm rounded-pill px-3"
                           onclick="return confirm('Вы уверены, что хотите выйти?')">
                            Выйти
                        </a>

                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- ADMIN BLOCK -->
    <?php if ($_SESSION['role'] === 'admin'): ?>

        <div class="row g-3 mb-4 profile-section">

            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-white bg-primary p-3 text-center profile-stat">
                    <div class="display-6 fw-bold"><?= $adminStats['total_movies'] ?></div>
                    <div class="small opacity-75">Фильмов</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-white bg-success p-3 text-center profile-stat">
                    <div class="display-6 fw-bold"><?= $adminStats['total_users'] ?></div>
                    <div class="small opacity-75">Пользователей</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-white bg-warning p-3 text-center profile-stat">
                    <div class="display-6 fw-bold"><?= $adminStats['total_ratings'] ?></div>
                    <div class="small opacity-75">Оценок</div>
                </div>
            </div>

        </div>

        <div class="card border-0 shadow-sm p-4 profile-section">

            <h4 class="fw-bold mb-3">🛠 Быстрые действия</h4>

            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?route=admin" class="btn btn-dark rounded-pill px-4">Управление каталогом</a>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">На сайт</a>
            </div>

        </div>

    <?php else: ?>

        <div class="card shadow-sm p-4 profile-section">

            <h4 class="mb-3">🍿 Мои оценки</h4>

            <?php if (!empty($userRatings)): ?>

                <div class="table-responsive">
                    <table class="table table-hover profile-table">
                        <thead>
                            <tr>
                                <th>Фильм</th>
                                <th>Оценка</th>
                                <th>Дата</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($userRatings as $rating): ?>
                                <tr>
                                    <td><strong><?= h($rating['title']) ?></strong></td>
                                    <td class="text-warning">★ <?= $rating['rating'] ?></td>
                                    <td class="text-muted small">
                                        <?= date('d.m.Y', strtotime($rating['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <p class="text-muted">Вы еще не выставили ни одной оценки.</p>
                <a href="index.php" class="btn btn-primary btn-sm">Найти фильмы</a>
            <?php endif; ?>

            <!-- 🔐 СМЕНА ПАРОЛЯ -->
            <hr class="my-4">

            <h5 class="mb-3">🔐 Смена пароля</h5>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?route=change_password" class="mt-3">

                <div class="mb-2">
                    <input type="password" name="old_password" class="form-control" placeholder="Старый пароль" required>
                </div>

                <div class="mb-2">
                    <input type="password" name="new_password" class="form-control" placeholder="Новый пароль" required>
                </div>

                <div class="mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Повторите новый пароль" required>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    Изменить пароль
                </button>

            </form>

        </div>

    <?php endif; ?>

</div>

</body>
</html>