<?php
session_start();
require '../db.php';

// 1. Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

// 3. CSRF
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: profile.php?password=error&msg=csrf');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$repeat = $_POST['new_password_confirm'] ?? '';

// 4. Проверки

if ($new !== $repeat) {
    header('Location: profile.php?password=error&msg=password_mismatch');
    exit;
}

// Условия пароля, как на странице восстановления
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $new)) {
    header('Location: profile.php?password=error&msg=password_rules');
    exit;
}

// 5. Получаем текущий пароль
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !password_verify($old, $user['password_hash'])) {
    header('Location: profile.php?password=error&msg=wrong_old');
    exit;
}

// 6. Сохраняем новый
$new_hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$stmt->execute([$new_hash, $user_id]);

// 7. Успех
header('Location: profile.php?password=success');
exit;
