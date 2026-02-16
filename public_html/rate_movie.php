// rate_movie.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die('Ошибка: пользователь не авторизован');
}

// CSRF
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Ошибка CSRF');
}

$user_id = (int)$_SESSION['user_id'];
$movie_id = (int)$_POST['movie_id'];
$rating = (int)$_POST['rating'];

// Проверяем, оценивал ли пользователь фильм
$stmt = $pdo->prepare("SELECT id FROM ratings WHERE user_id = ? AND movie_id = ?");
$stmt->execute([$user_id, $movie_id]);
if ($stmt->fetch()) {
    echo "Вы уже оценили этот фильм";
    exit;
}

// Сохраняем оценку
$stmt = $pdo->prepare("INSERT INTO ratings (user_id, movie_id, rating, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$user_id, $movie_id, $rating]);

echo "Оценка сохранена";
