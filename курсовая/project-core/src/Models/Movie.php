<?php

class Movie {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Получение списка фильмов с пагинацией
     */
    public function getPaginatedList($limit, $offset) {
        // SQL-запрос с расчетом среднего рейтинга и лимитом для страниц
        $sql = "SELECT m.*, ROUND(AVG(r.rating), 1) as avg_rating 
                FROM movies m 
                LEFT JOIN ratings r ON m.id = r.movie_id 
                GROUP BY m.id 
                ORDER BY m.id DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        // Принудительно приводим к int для корректной работы LIMIT в PDO
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Поиск фильмов
     */
   public function searchAdvanced($title, $year) {
    // Если год не введен, ищем только по названию. Если введен — по обоим полям.
    $sql = "SELECT m.*, ROUND(AVG(r.rating), 1) as avg_rating 
            FROM movies m 
            LEFT JOIN ratings r ON m.id = r.movie_id 
            WHERE m.title LIKE ? " . (!empty($year) ? "AND m.release_year = ? " : "") . 
            "GROUP BY m.id 
            ORDER BY m.id DESC";
            
    $stmt = $this->db->prepare($sql);
    
    $params = ["%$title%"];
    if (!empty($year)) {
        $params[] = $year;
    }
    
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Добавляем метод проверки дубликата в избранном
public function isAlreadyInWatchlist($userId, $movieId) {
    $stmt = $this->db->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$userId, $movieId]);
    return $stmt->fetch() ? true : false;
}

    /**
     * Общее количество фильмов в базе (нужно для расчета страниц)
     */
    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Методы для админки
    public function createMovie($data) {
    $sql = "INSERT INTO movies (title, release_year, description, poster_url) VALUES (?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        $data['title'], 
        $data['release_year'], 
        $data['description'], 
        $data['poster_url']
    ]);
}

    public function update($id, $title, $year, $desc, $poster) {
        $sql = "UPDATE movies SET title = ?, release_year = ?, description = ?, poster_url = ? WHERE id = ?";
        return $this->db->prepare($sql)->execute([$title, $year, $desc, $poster, $id]);
    }

    public function delete($id) {
    // 1. Сначала удаляем все оценки этого фильма
    $stmt1 = $this->db->prepare("DELETE FROM ratings WHERE movie_id = ?");
    $stmt1->execute([$id]);

    // 2. Также удаляем его из списков избранного (если есть такая таблица)
    $stmt2 = $this->db->prepare("DELETE FROM watchlist WHERE movie_id = ?");
    $stmt2->execute([$id]);

    // 3. И только теперь удаляем сам фильм
    $stmt3 = $this->db->prepare("DELETE FROM movies WHERE id = ?");
    return $stmt3->execute([$id]);
    }

    public function hasUserRated($userId, $movieId) {
        $stmt = $this->db->prepare("SELECT id FROM ratings WHERE user_id = ? AND movie_id = ?");
        $stmt->execute([$userId, $movieId]);
        return $stmt->fetch() ? true : false;
    }

    public function addToWatchlist($userId, $movieId) {
        $stmt = $this->db->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
        $stmt->execute([$userId, $movieId]);
        if ($stmt->fetch()) return true;

        $sql = "INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)";
        return $this->db->prepare($sql)->execute([$userId, $movieId]);
    }

    public function getWatchlist($userId) {
        $sql = "SELECT m.*, w.is_watched 
                FROM movies m 
                JOIN watchlist w ON m.id = w.movie_id 
                WHERE w.user_id = ? 
                ORDER BY w.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function toggleWatched($userId, $movieId) {
        $sql = "UPDATE watchlist SET is_watched = 1 - is_watched 
                WHERE user_id = ? AND movie_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $movieId]);
    }

    public function getUserRatings($userId) {
        $sql = "SELECT m.title, m.release_year, r.rating, r.created_at 
                FROM ratings r
                JOIN movies m ON r.movie_id = m.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение абсолютно всех фильмов для админ-панели
     */
    public function getAllMovies() {
        // Выбираем все поля из таблицы movies, сортируем: новые вверху
        $sql = "SELECT * FROM movies ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAdminStats() {
    $stats = [];
    $stats['total_movies'] = $this->db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $stats['total_users'] = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $stats['total_ratings'] = $this->db->query("SELECT COUNT(*) FROM ratings")->fetchColumn();
    return $stats;
    }
}