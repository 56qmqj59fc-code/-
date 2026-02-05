<?php
session_start();

// Удаляем все данные сессии
$_SESSION = [];

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя в каталог фильмов
header("Location: index.php");
exit;
