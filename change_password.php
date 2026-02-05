<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require '../db.php';

// 1. Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Некорректный запрос');
}

// 3. CSRF
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('Ошибка безопасности (CSRF)');
}

$user_id = (int)$_SESSION['user_id'];
$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$repeat = $_POST['new_password_confirm'] ?? '';

// 4. Проверки
if ($new !== $repeat) {
    die('Пароли не совпадают');
}

if (strlen($new) < 8) {
    die('Пароль должен быть не короче 8 символов');
}

// 5. Получаем текущий пароль
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !password_verify($old, $user['password_hash'])) {
    die('Неверный текущий пароль');
}

// 6. Сохраняем новый
$new_hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "UPDATE users SET password_hash = ? WHERE id = ?"
);
$stmt->execute([$new_hash, $user_id]);

// 7. Успех
header('Location: profile.php?password=success');
exit;
