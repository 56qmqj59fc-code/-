<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Вы не авторизованы");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Некорректный запрос");
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Ошибка CSRF");
}

$user_id = $_SESSION['user_id'];
$movie_id = (int)($_POST['movie_id'] ?? 0);

if ($movie_id <= 0) die("Ошибка фильма");

/* Получаем текущий статус */
$stmt = $pdo->prepare("SELECT watched FROM watchlist WHERE user_id=? AND movie_id=?");
$stmt->execute([$user_id, $movie_id]);
$watch = $stmt->fetch();

if (!$watch) die("Фильм не найден в вашем списке");

/* Переключаем watched */
$new_status = $watch['watched'] ? 0 : 1;
$stmt = $pdo->prepare("UPDATE watchlist SET watched=? WHERE user_id=? AND movie_id=?");
$stmt->execute([$new_status, $user_id, $movie_id]);

header("Location: watchlist.php");
exit;
