<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Некорректный запрос');
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('Ошибка CSRF');
}

$user_id = (int)($_POST['user_id'] ?? 0);
$pass = $_POST['password'] ?? '';
$pass2 = $_POST['password_confirm'] ?? '';

if ($user_id <= 0) {
    die('Ошибка пользователя');
}

if (strlen($pass) < 8) {
    die('Пароль должен быть не короче 8 символов');
}

if ($pass !== $pass2) {
    die('Пароли не совпадают');
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "UPDATE users SET password_hash = ? WHERE id = ?"
);
$stmt->execute([$hash, $user_id]);


header('Location: login.php');
exit;
