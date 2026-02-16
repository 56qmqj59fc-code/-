<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем фильмы из списка пользователя
$stmt = $pdo->prepare("
    SELECT movies.* 
    FROM watchlist
    JOIN movies ON watchlist.movie_id = movies.id
    WHERE watchlist.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$movies = $stmt->fetchAll();

// Получение среднего рейтинга для всех фильмов в списке
$ratingsStmt = $pdo->query("
    SELECT movie_id, AVG(rating) as avg_rating
    FROM ratings
    GROUP BY movie_id
");
$ratingsData = $ratingsStmt->fetchAll(PDO::FETCH_KEY_PAIR); // movie_id => avg_rating
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Мой список</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Верхняя панель */
.top-panel {
    background-color: #000;
    color: #fff;
    border-bottom: 1px solid #222;
    padding: 15px 0;
    z-index: 1000;
}

/* Заголовок белый */
.top-panel h2 {
    color: #fff;
    margin: 0;
}

/* Кнопка назад */
.top-panel .btn {
    min-width: 150px;
}

/* Отступ для контента */
.body-padding {
    padding-top: 80px;
}

/* Ограничение описания по 3 строки */
.card-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Кнопка "Показать больше / Свернуть" серого цвета */
.show-more {
    color: #6c757d; /* серый */
    cursor: pointer;
    font-size: 0.9rem;
    user-select: none;
}

/* Рейтинг */
.rating {
    color: #ffc107;
    font-weight: bold;
}
</style>
</head>

<body class="bg-light">

<div class="container-fluid top-panel position-fixed top-0 w-100">
    <div class="container d-flex justify-content-between align-items-center">
        <h2 class="mb-0">🎥 Мой список к просмотру</h2>
        <a href="profile.php" class="btn btn-outline-light">← Назад в кабинет</a>
    </div>
</div>

<div class="container body-padding">

<?php if ($movies): ?>
<div class="row mt-3">
<?php foreach ($movies as $movie): 
    $avgRating = isset($ratingsData[$movie['id']]) ? round($ratingsData[$movie['id']], 1) : null;
?>
    <div class="col-12 col-sm-6 col-lg-4 mb-4">
        <div class="card shadow h-100">

            <img src="<?= htmlspecialchars($movie['poster_url']) ?>"
                 class="card-img-top"
                 style="height:300px; object-fit:cover;">

            <div class="card-body d-flex flex-column">
                <h5><?= htmlspecialchars($movie['title']) ?></h5>

                <?php if ($avgRating): ?>
                    <p class="rating mb-2">⭐ <?= $avgRating ?> / 5</p>
                <?php endif; ?>

                <p class="card-text" id="desc-<?= $movie['id'] ?>"><?= htmlspecialchars($movie['description']) ?></p>
                <?php if (strlen($movie['description']) > 200): ?>
                    <span class="show-more" id="toggle-<?= $movie['id'] ?>" onclick="toggleText(<?= $movie['id'] ?>)">Показать больше</span>
                <?php endif; ?>
            </div>

        </div>
    </div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p class="text-center mt-3">Список пуст</p>
<?php endif; ?>

</div>

<script>
// Показать/Свернуть описание
function toggleText(id) {
    const p = document.getElementById('desc-' + id);
    const btn = document.getElementById('toggle-' + id);
    if (p.style.webkitLineClamp === "unset" || p.style.display === "block") {
        // Свернуть
        p.style.display = '-webkit-box';
        p.style.webkitLineClamp = '3';
        btn.textContent = 'Показать больше';
    } else {
        // Развернуть
        p.style.display = 'block';
        p.style.webkitLineClamp = 'unset';
        btn.textContent = 'Свернуть';
    }
}
</script>

</body>
</html>
