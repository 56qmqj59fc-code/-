<?php
class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            // Подключаем файл с настройками, который лежит в той же папке
            $config = require __DIR__ . '/config.php';

            $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['db'] . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], $options);
            } catch (PDOException $e) {
                die("Ошибка подключения к БД: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}

// Защитная функция h()
if (!function_exists('h')) {
    function h($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}