<?php
class Watchlist {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Получить список фильмов конкретного пользователя
    public function getUserList($user_id) {
        $sql = "SELECT movies.*, watchlist.watched 
                FROM watchlist 
                JOIN movies ON watchlist.movie_id = movies.id 
                WHERE watchlist.user_id = :user_id 
                ORDER BY watchlist.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    // Проверка: есть ли уже фильм в списке?
    public function exists($user_id, $movie_id) {
        $stmt = $this->db->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
        $stmt->execute([$user_id, $movie_id]);
        return $stmt->fetch() !== false;
    }
}