<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT movies.* 
    FROM watchlist
    JOIN movies ON watchlist.movie_id = movies.id
    WHERE watchlist.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$movies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Мой список</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

<h2 class="text-center mb-4">🎥 Мой список к просмотру</h2>

<?php if ($movies): ?>
<div class="row">
<?php foreach ($movies as $movie): ?>
    <div class="col-12 col-sm-6 col-lg-4 mb-4">
        <div class="card shadow h-100">

            <img src="<?= htmlspecialchars($movie['poster_url']) ?>"
                 class="card-img-top"
                 style="height:300px; object-fit:cover;">

            <div class="card-body d-flex flex-column">
                <h5><?= htmlspecialchars($movie['title']) ?></h5>
                <p class="small"><?= htmlspecialchars($movie['description']) ?></p>
            </div>

        </div>
    </div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p class="text-center">Список пуст</p>
<?php endif; ?>

<div class="text-center mt-4">
    <a href="profile.php" class="btn btn-primary">← Назад в кабинет</a>
</div>

</div>
</body>
</html>
