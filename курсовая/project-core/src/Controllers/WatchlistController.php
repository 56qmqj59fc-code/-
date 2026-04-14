<?php
require_once __DIR__ . '/../Models/Movie.php';

class WatchlistController {
    private $db;
    private $movieModel;

    public function __construct($db) {
        $this->db = $db;
        $this->movieModel = new Movie($db);
    }

    /**
     * Отображение страницы избранного
     * Метод, который вызывается в index.php на строке 60
     */
    public function renderWatchlist() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        // Получаем список фильмов из модели
        $watchlistMovies = $this->movieModel->getWatchlist($userId);

        require_once __DIR__ . '/../../templates/watchlist.php';
    }

    /**
     * Удаление фильма из избранного
     */
    public function remove() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $movieId = $_GET['id'] ?? null;

        if ($movieId) {
            $stmt = $this->db->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$userId, $movieId]);
        }

        header('Location: index.php?route=watchlist');
        exit;
    }

    /**
     * Переключение статуса "Просмотрено"
     */
    public function toggleWatched() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $movieId = $_GET['id'] ?? null;

        if ($movieId) {
            // Вызываем метод модели, который мы добавили ранее
            $this->movieModel->toggleWatched($userId, $movieId);
        }

        header('Location: index.php?route=watchlist');
        exit;
    }
}