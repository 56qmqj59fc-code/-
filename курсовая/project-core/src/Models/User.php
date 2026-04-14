<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Регистрация: хэшируем пароль и сохраняем в базу
    public function register($email, $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'client')");
            return $stmt->execute([$email, $hash]);
        } catch (PDOException $e) {
            // Если email уже есть в базе, вернется false
            return false;
        }
    }

    // Поиск пользователя по Email (для входа)
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    // Смена пароля пользователя
    public function updatePassword($user_id, $new_password) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $user_id]);
    }
}