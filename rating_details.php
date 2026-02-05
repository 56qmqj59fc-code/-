<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Доступ запрещён");
}

$rating_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Anti-IDOR: проверяем владельца
$stmt = $pdo->prepare("
    SELECT ratings.rating, movies.title
    FROM ratings
    JOIN movies ON ratings.movie_id = movies.id
    WHERE ratings.id = ? AND ratings.user_id = ?
");
$stmt->execute([$rating_id, $user_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Оценка не найдена или у вас нет прав");
}
?>

<h2><?= $data['title'] ?></h2>
<p>Ваша оценка: <b><?= $data['rating'] ?>/10</b></p>
