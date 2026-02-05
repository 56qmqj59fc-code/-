<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$movie_id = (int)($_GET['movie_id'] ?? 0);
if ($movie_id <= 0) {
    die('Некорректный фильм');
}

/* Получаем фильм */
$stmt = $pdo->prepare("SELECT title FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    die('Фильм не найден');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оценка фильма</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4" style="max-width:500px">

<h2>🎬 <?= htmlspecialchars($movie['title']) ?></h2>

<form action="rate_movie.php" method="post">
    <input type="hidden" name="movie_id" value="<?= $movie_id ?>">

    <label class="form-label mt-3">Ваша оценка</label>
    <select name="rating" class="form-select" required>
        <option value="">Выберите</option>
        <option value="1">1 ⭐</option>
        <option value="2">2 ⭐</option>
        <option value="3">3 ⭐</option>
        <option value="4">4 ⭐</option>
        <option value="5">5 ⭐</option>
    </select>

    <button class="btn btn-success mt-3 w-100">Сохранить оценку</button>
</form>

<a href="index.php" class="btn btn-outline-secondary mt-3 w-100">
    ← Назад
</a>

</div>
</body>
</html>
