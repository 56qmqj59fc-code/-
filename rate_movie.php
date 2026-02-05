<?php
session_start();
require '../db.php';

// 1. Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему. <a href='login.php'>Войти</a>");
}

$user_id  = $_SESSION['user_id'];
$movie_id = (int)($_GET['id'] ?? 0);

// 2. Проверка данных
if ($movie_id <= 0) {
    die("Некорректные данные.");
}

// 3. Проверяем фильм существует ли
$stmt = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
if (!$stmt->fetch()) {
    die("Фильм не найден.");
}

// 4. Проверка — не голосовал ли уже
$stmt = $pdo->prepare("SELECT id FROM ratings WHERE user_id = ? AND movie_id = ?");
$stmt->execute([$user_id, $movie_id]);
if ($stmt->fetch()) {
    die("Вы уже оценивали этот фильм.");
}

// 5. Сохраняем оценку
// Для простоты: при клике на "Оценить" ставим 5 ⭐ (можно потом доработать выбор рейтинга)
$rating = 5; 

$stmt = $pdo->prepare("INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $movie_id, $rating]);

// 6. Возврат на главную страницу
header("Location: index.php");
exit;
