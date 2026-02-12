<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* CSRF токен */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* Получаем фильмы пользователя */
$stmt = $pdo->prepare("
    SELECT movies.id, movies.title, movies.description, movies.poster_url, movies.release_year, 
           COALESCE(watchlist.watched,0) as watched
    FROM watchlist
    JOIN movies ON watchlist.movie_id = movies.id
    WHERE watchlist.user_id = ?
    ORDER BY watched ASC, watchlist.id DESC
");
$stmt->execute([$user_id]);
$movies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Мой список к просмотру</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-img-top { height: 250px; object-fit: cover; }
.card-watched { opacity: 0.6; filter: grayscale(50%); }
</style>
</head>
<body class="bg-light">

<div class="container mt-4">
<h1 class="mb-4">🎬 Мой список к просмотру</h1>

<?php if($movies): ?>
<div class="row row-cols-1 row-cols-md-3 g-4">
<?php foreach($movies as $movie): ?>
    <div class="col">
        <div class="card h-100 shadow-sm <?= $movie['watched'] ? 'card-watched' : '' ?>">
            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($movie['description']) ?></p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <small>Год: <?= $movie['release_year'] ?></small>
                <form method="POST" action="toggle_watched.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                    <button type="submit" class="btn btn-sm <?= $movie['watched'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                        <?= $movie['watched'] ? 'Просмотрено ✅' : 'Отметить как просмотренное' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p class="text-muted">Ваш список к просмотру пока пуст.</p>
<?php endif; ?>

<br>
<a href="index.php" class="btn btn-primary">← На главную</a>
</div>
</body>
</html>
