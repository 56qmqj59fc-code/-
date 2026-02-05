<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Неверный запрос');
}

$title = trim($_POST['title']);
$description = trim($_POST['description']);
$year = (int)$_POST['year'];

$file = $_FILES['poster'];

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$uploadDir = 'uploads/';

if ($file['error'] !== UPLOAD_ERR_OK) {
    die('Ошибка загрузки файла');
}

if (!in_array($file['type'], $allowedTypes)) {
    die('Можно загружать только изображения');
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newName = uniqid('poster_') . '.' . $ext;
$path = $uploadDir . $newName;

if (!move_uploaded_file($file['tmp_name'], $path)) {
    die('Не удалось сохранить файл');
}

// Сохраняем фильм
$stmt = $pdo->prepare("
    INSERT INTO movies (title, description, year, poster_url)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$title, $description, $year, $path]);

header('Location: index.php');
exit;
