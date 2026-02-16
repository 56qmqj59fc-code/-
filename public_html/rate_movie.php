<?php
session_start();
require '../db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    die("Вы не авторизованы");
}

// Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Некорректный запрос");
}

// Проверка CSRF
if (
    !isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Некорректные данные (CSRF)");
}

// Проверка данных
$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
$rating   = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$user_id  = $_SESSION['user_id'];

if ($movie_id <= 0 || $rating < 1 || $rating > 5) {
    die("Некорректные данные");
}

// Проверяем, голосовал ли пользователь раньше
$stmt = $pdo->prepare("SELECT id FROM ratings WHERE user_id = ? AND movie_id = ?");
$stmt->execute([$user_id, $movie_id]);
$existing = $stmt->fetch();

if ($existing) {
    // Обновляем оценку
    $stmt = $pdo->prepare("UPDATE ratings SET rating = ? WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$rating, $user_id, $movie_id]);
} else {
    // Добавляем новую
    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $movie_id, $rating]);
}

header("Location: index.php");
exit;
