<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🎬 КиноМир — Твой гид в мире кино</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🎬 КиноМир</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-center gap-2">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a class="nav-link nav-link-admin px-3" href="index.php?route=admin">🛠 АДМИН-ПАНЕЛЬ</a>
                    <?php endif; ?>
                    <a class="nav-link text-white" href="index.php?route=profile">👤 Профиль</a>
                    <?php if($_SESSION['role'] !== 'admin'): ?>
                        <a class="nav-link text-white" href="index.php?route=watchlist">⭐ Избранное</a>
                    <?php endif; ?>
                    <a class="nav-link text-danger ms-2" href="index.php?route=logout">Выйти</a>
                <?php else: ?>
                    <a class="nav-link text-white" href="index.php?route=login">Вход</a>
                    <a class="nav-link btn btn-primary btn-sm px-3" href="index.php?route=register">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <?php if (($_GET['error'] ?? '') === 'already_rated'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            Вы уже оценивали этот фильм!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (($_GET['error'] ?? '') === 'already_in_watchlist'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            Этот фильм уже есть в вашем списке просмотров!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (($_GET['success'] ?? '') === 'rated'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Спасибо! Ваша оценка учтена.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (($_GET['success'] ?? '') === 'added_to_watchlist'): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">Фильм успешно добавлен в избранное!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 shadow-sm border-0 mb-4">
        <form method="GET" action="index.php" class="row g-2">
            <input type="hidden" name="route" value="home">
            <div class="col-md-5">
                <input type="text" name="search_title" class="form-control" placeholder="Название фильма..." value="<?= htmlspecialchars($_GET['search_title'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <input type="number" name="search_year" class="form-control" placeholder="Год" value="<?= htmlspecialchars($_GET['search_year'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Найти</button>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-outline-secondary w-100">Сбросить</a>
            </div>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm movie-card">
                        <img src="<?= htmlspecialchars($movie['poster_url']) ?>" class="card-img-top" alt="Постер">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($movie['title']) ?></h5>
                                <span class="badge bg-warning text-dark">⭐ <?= $movie['avg_rating'] ?: '0.0' ?></span>
                            </div>
                            <p class="text-muted small mb-2">Год выпуска: <?= $movie['release_year'] ?></p>
                            
                            <div class="description-container mb-3">
                                <p class="card-text text-secondary small description-text" id="desc-<?= $movie['id'] ?>">
                                    <?= htmlspecialchars($movie['description']) ?>
                                </p>
                                <span class="toggle-btn" onclick="toggleDescription(<?= $movie['id'] ?>, this)">показать больше</span>
                            </div>

                            <?php if(isset($_SESSION['user_id'])): ?>
                                <form action="index.php?route=add_rating" method="POST" class="mt-auto pt-2">
                                    <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                                    <div class="input-group input-group-sm">
                                        <select name="rating" class="form-select border-light shadow-none" required>
                                            <option value="" disabled selected>⭐ Оценить</option>
                                            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                            <option value="4">⭐⭐⭐⭐ (4)</option>
                                            <option value="3">⭐⭐⭐ (3)</option>
                                            <option value="2">⭐⭐ (2)</option>
                                            <option value="1">⭐ (1)</option>
                                        </select>
                                        <button class="btn btn-primary" type="submit">ОК</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="mt-auto pt-2 text-center">
                                    <a href="index.php?route=login" class="btn btn-outline-secondary btn-sm w-100">
                                        🔒 Войдите, чтобы оценить
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-white border-0 pb-3">
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin'): ?>
                                <div class="d-grid">
                                    <a href="index.php?route=add_watchlist&id=<?= $movie['id'] ?>" class="btn btn-outline-danger btn-sm rounded-pill">❤️ В избранное</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="lead text-muted">По вашему запросу ничего не найдено.</p>
                <a href="index.php" class="btn btn-primary">Вернуться к списку</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($totalPages) && $totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link shadow-none" href="index.php?page=<?= $i ?><?= !empty($searchTitle) ? '&search_title='.$searchTitle : '' ?><?= !empty($searchYear) ? '&search_year='.$searchYear : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5 shadow-lg">
    <div class="container">
        <p class="mb-0 small text-muted">&copy; 2026 КиноМир. Все права защищены.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>