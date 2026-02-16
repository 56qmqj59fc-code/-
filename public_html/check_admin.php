<?php
// Middleware для защиты админки

session_start();

// Проверяем авторизацию и роль
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("ДОСТУП ЗАПРЕЩЕН. У вас нет прав администратора. <a href='login.php'>Войти</a>");
}

// Генерируем CSRF токен один раз на сессию
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
