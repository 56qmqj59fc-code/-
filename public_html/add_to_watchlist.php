<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Вы не авторизованы";
    exit;
}

// CSRF защита
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    echo "Ошибка CSRF";
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;

if ($movie_id <= 0) {
    http_response_code(400);
    echo "Неверный фильм";
    exit;
}

// Проверка, есть ли уже в списке
$stmt = $pdo->prepare("SELECT * FROM watchlist WHERE user_id=? AND movie_id=?");
$stmt->execute([$user_id, $movie_id]);
$exists = $stmt->fetch();

if ($exists) {
    echo "Фильм уже в списке";
} else {
    $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $movie_id]);
    echo "Фильм добавлен в список";
}
