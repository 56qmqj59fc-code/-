<?php
session_start();
require '../db.php';

/* =========================
   CSRF TOKEN
========================= */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   SEARCH
========================= */
$search_title = trim($_GET['title'] ?? '');
$search_year = trim($_GET['year'] ?? '');

/* =========================
   PAGINATION
========================= */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 6;
$offset = ($page - 1) * $limit;

/* =========================
   MAIN QUERY WITH SEARCH
========================= */
$where = "WHERE 1=1";
$params = [];

if ($search_title !== '') {
    $where .= " AND movies.title LIKE ?";
    $params[] = "%$search_title%";
}

if ($search_year !== '') {
    $where .= " AND movies.release_year = ?";
    $params[] = $search_year;
}

$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM movies $where");
$total_stmt->execute($params);
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "
SELECT 
    movies.*,
    ROUND(AVG(ratings.rating), 1) AS avg_rating,
    COUNT(ratings.id) AS votes
FROM movies
LEFT JOIN ratings ON movies.id = ratings.movie_id
$where
GROUP BY movies.id
ORDER BY movies.id DESC
LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

/* =========================
   USER RATINGS FOR DISPLAY
========================= */
$user_ratings = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT movie_id FROM ratings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_ratings = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Каталог кинофильмов</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f5f6fa;
}
/* ===== Карточки ===== */
.card {
    border-radius: 16px;
    overflow: hidden;
}
.movie-poster {
    width: 100%;
    height: 300px;
    object-fit: cover;
}
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
    cursor: pointer;
}
/* ===== Адаптация ===== */
@media (max-width: 768px) {
    h1 { font-size: 1.5rem; text-align:center; }
    .header-flex { flex-direction: column; gap: 15px; }
    .header-buttons a { width: 100%; margin-bottom: 8px; }
    .search-form { background:white; padding:15px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.05);}
    .movie-poster { height: 240px; }
    .pagination { flex-wrap: wrap; }
}
@media (max-width: 480px) { .movie-poster { height: 210px; } }
</style>
</head>

<body>
<div class="container mt-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 header-flex">
    <h1>🎬 Каталог кинофильмов</h1>

    <div class="header-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="btn btn-outline-primary me-2">Личный кабинет</a>
            <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-primary me-2">Войти</a>
            <a href="register.php" class="btn btn-outline-success">Регистрация</a>
        <?php endif; ?>
    </div>
</div>

<!-- SEARCH FORM -->
<form method="GET" class="row g-2 mb-4 search-form">
    <div class="col-12 col-md-5">
        <input type="text" name="title" placeholder="Название фильма"
               class="form-control"
               value="<?= htmlspecialchars($search_title) ?>">
    </div>
    <div class="col-12 col-md-3">
        <input type="number" name="year" placeholder="Год выпуска"
               class="form-control"
               value="<?= htmlspecialchars($search_year) ?>">
    </div>
    <div class="col-6 col-md-2">
        <button class="btn btn-primary w-100">Поиск</button>
    </div>
    <div class="col-6 col-md-2">
        <a href="index.php" class="btn btn-secondary w-100">Сбросить</a>
    </div>
</form>

<!-- MOVIES -->
<div class="row">
<?php foreach ($movies as $movie): ?>
    <div class="col-12 col-sm-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">

            <img src="<?= htmlspecialchars($movie['poster_url']) ?>"
                 class="movie-poster"
                 alt="<?= htmlspecialchars($movie['title']) ?>">

            <div class="card-body">

                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>

                <p class="mb-1"><strong>Год:</strong> <?= $movie['release_year'] ?></p>
                <p class="mb-1"><strong>Длительность:</strong> <?= htmlspecialchars($movie['duration']) ?></p>

                <p class="card-text" id="desc-<?= $movie['id'] ?>">
                    <?= htmlspecialchars($movie['description']) ?>
                </p>
                <span class="read-more-btn"
                      onclick="toggleDesc(<?= $movie['id'] ?>)">читать далее</span>

                <p class="fw-bold mt-2">
                    ⭐ <?= $movie['votes'] > 0 ? $movie['avg_rating'] . ' / 5' : 'нет оценок' ?>
                    (<?= $movie['votes'] ?>)
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <?php if (in_array($movie['id'], $user_ratings)): ?>
                        <div class="alert alert-secondary text-center mb-2 p-1">
                            Вы уже оценили этот фильм
                        </div>
                    <?php endif; ?>

                    <form action="rate_movie.php" method="POST" class="mb-2 add-watchlist">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                        <div class="d-flex">
                            <select name="rating" class="form-select me-2" required>
                                <option value="">Оцените</option>
                                <option value="1">1 ⭐</option>
                                <option value="2">2 ⭐</option>
                                <option value="3">3 ⭐</option>
                                <option value="4">4 ⭐</option>
                                <option value="5">5 ⭐</option>
                            </select>
                            <button type="submit" class="btn btn-success">Оценить</button>
                        </div>
                    </form>

                    <form class="add-watchlist" data-movie-id="<?= $movie['id'] ?>">
                        <button type="submit" class="btn btn-outline-primary w-100 mt-2">
                            В список к просмотру
                        </button>
                    </form>
                    <div class="watchlist-msg text-success mt-1" id="msg-<?= $movie['id'] ?>"></div>

                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary w-100">
                        Войдите, чтобы оценить
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- PAGINATION -->
<nav>
  <ul class="pagination justify-content-center mt-4">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
        <a class="page-link"
           href="?page=<?= $i ?>&title=<?= urlencode($search_title) ?>&year=<?= urlencode($search_year) ?>">
           <?= $i ?>
        </a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function toggleDesc(id) {
    document.getElementById('desc-' + id).classList.toggle('expanded');
}

$(document).ready(function(){
    $('.add-watchlist').on('submit', function(e){
        e.preventDefault();
        const movieId = $(this).data('movie-id');

        $.ajax({
            url: 'add_to_watchlist.php',
            method: 'POST',
            data: {
                movie_id: movieId,
                csrf_token: '<?= $_SESSION['csrf_token'] ?>'
            },
            success: function(response){
                $('#msg-' + movieId).text(response).fadeIn().delay(2000).fadeOut();
            }
        });
    });
});
</script>

</body>
</html>
