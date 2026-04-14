<?php
/**
 * ГЛАВНЫЙ ТОЧКА ВХОДА (ROUTER)
 */

// 1. Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Запуск сессии для авторизации пользователей
session_start();

// 3. Определение пути к ядру проекта (на уровень выше public_html)
$corePath = dirname(__DIR__) . '/project-core';

// 4. Подключение необходимых компонентов системы
require_once $corePath . '/config/Database.php'; // Класс БД (сам подключит config.php)
require_once $corePath . '/src/Models/Movie.php';
require_once $corePath . '/src/Controllers/MovieController.php';
require_once $corePath . '/src/Controllers/AuthController.php';
require_once $corePath . '/src/Controllers/WatchlistController.php';

/**
 * 5. ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
 * Метод getConnection() теперь вызывается без аргументов, 
 * так как настройки подтягиваются внутри самого класса Database.
 */
$db = Database::getConnection();

// 6. Определение текущего маршрута (по умолчанию — главная страница)
$route = $_GET['route'] ?? 'home';

// 7. РОУТЕР: Распределение запросов по контроллерам
switch ($route) {
    
    // --- ГЛАВНАЯ СТРАНИЦА И ПОИСК ---
    case 'home':
        (new MovieController($db))->renderHome();
        break;

    // --- АВТОРИЗАЦИЯ, РЕГИСТРАЦИЯ И ПРОФИЛЬ ---
    case 'login':
        (new AuthController($db))->renderLogin();
        break;

    case 'register':
        (new AuthController($db))->renderRegister();
        break;

    case 'logout':
        (new AuthController($db))->logout();
        break;

    case 'profile':
        (new AuthController($db))->renderProfile();
        break;
        
    case 'change_password':
    (new AuthController($db))->changePassword();
    break;

    // --- ИЗБРАННОЕ И СПИСОК ПРОСМОТРА (WATCHLIST) ---
    case 'watchlist':
        (new WatchlistController($db))->renderWatchlist();
        break;

    case 'add_watchlist':
        (new MovieController($db))->addToWatchlist();
        break;

    case 'remove_watchlist':
        (new WatchlistController($db))->remove();
        break;

    case 'toggle_watched':
        // Смена статуса: "Просмотрено" / "Не просмотрено"
        (new WatchlistController($db))->toggleWatched();
        break;

    // --- СИСТЕМА РЕЙТИНГА ---
    case 'add_rating':
        (new MovieController($db))->addRating();
        break;

    // --- АДМИНИСТРИРОВАНИЕ ---
    case 'admin':
        (new AuthController($db))->renderAdminPanel();
        break;
    
    // Показ формы (пустой для добавления или с данными для редактирования)
    case 'admin_form':
        (new MovieController($db))->showForm();
        break;
    
    // Само действие сохранения (обработка POST-данных и загрузка файла)
    case 'admin_save_movie':
        (new MovieController($db))->saveMovie();
        break;
    
    // Удаление фильма
    case 'admin_delete':
        (new MovieController($db))->deleteMovie();
        break;

    // --- СТРАНИЦА 404 (ЕСЛИ МАРШРУТ НЕ НАЙДЕН) ---
    default:
        http_response_code(404);
        echo "<div style='text-align:center; padding-top:100px; font-family: sans-serif; color: #333;'>
                <h1 style='font-size: 80px; margin-bottom: 10px;'>404</h1>
                <p style='font-size: 20px;'>Упс! Страница не найдена.</p>
                <a href='index.php' style='color: #007bff; text-decoration: none; font-weight: bold;'>Вернуться на главную</a>
              </div>";
        break;
}