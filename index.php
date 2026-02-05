<?php
session_start();
require '../db.php';

// Получаем фильмы + средний рейтинг + кол-во оценок
$sql = "
SELECT 
    movies.*,
    ROUND(AVG(ratings.rating), 1) AS avg_rating,
    COUNT(ratings.id) AS votes
FROM movies
LEFT JOIN ratings ON movies.id = ratings.movie_id
GROUP BY movies.id
ORDER BY movies.id DESC
";

$stmt = $pdo->query($sql);
$movies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Каталог кинофильмов</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Обрезка текста до 3 строк */
.card-text {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    transition: all 0.3s;
}
.card-text.expanded {
    -webkit-line-clamp: unset;
}
.read-more-btn {
    font-size: 0.9rem;
    color: gray;
    text-decoration: none;
    cursor: pointer;
}
</style>
</head>
<body>
<div class="container mt-4">

<!-- Навигация -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🎬 Каталог кинофильмов</h1>
    <div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php" class="btn btn-outline-primary me-2">Личный кабинет</a>
        <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
    <?php else: ?>
        <a href="login.php" class="btn btn-outline-primary me-2">Войти</a>
        <a href="register.php" class="btn btn-outline-success">Регистрация</a>
    <?php endif; ?>
    </div>
</div>

<div class="row">
<?php foreach ($movies as $movie): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">

            <img src="<?= htmlspecialchars($movie['poster_url']) ?>"
                 class="card-img-top"
                 style="height: 300px; object-fit: cover;"
                 alt="<?= htmlspecialchars($movie['title']) ?>">

            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?> (<?= $movie['release_year'] ?>)</h5>

                <p class="card-text" id="desc-<?= $movie['id'] ?>">
                    <?= htmlspecialchars($movie['description']) ?>
                </p>
                <a class="read-more-btn" onclick="toggleDesc(<?= $movie['id'] ?>)">читать далее</a>

                <p class="fw-bold mb-2 mt-2">
                    ⭐ Рейтинг:
                    <?= $movie['votes'] > 0 ? $movie['avg_rating'] . ' / 5' : 'нет оценок' ?>
                    (<?= $movie['votes'] ?>)
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="rate_movie.php" method="post" class="d-flex">
                        <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">

                        <select name="rating" class="form-select me-2" required>
                            <option value="">Оцените</option>
                            <option value="1">1 ⭐</option>
                            <option value="2">2 ⭐</option>
                            <option value="3">3 ⭐</option>
                            <option value="4">4 ⭐</option>
                            <option value="5">5 ⭐</option>
                        </select>

                        <button type="submit" class="btn btn-success">
                            Оценить
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary w-100 mt-2">
                        Войдите, чтобы оценить
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
// JS для раскрытия описания
function toggleDesc(id) {
    const desc = document.getElementById('desc-' + id);
    desc.classList.toggle('expanded');
}
</script>

</body>
</html>
