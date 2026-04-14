<?php
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // 1. Регистрация
    public function renderRegister() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $passConfirm = $_POST['password_confirm'] ?? '';

            if (empty($email) || empty($pass)) {
                $error = "Заполните все поля!";
            } elseif ($pass !== $passConfirm) {
                $error = "Пароли не совпадают!";
            } else {
                $userModel = new User($this->db);
                if ($userModel->register($email, $pass)) {
                    header('Location: index.php?route=login&success=1');
                    exit;
                } else {
                    $error = "Этот Email уже зарегистрирован.";
                }
            }
        }
        require_once __DIR__ . '/../../templates/register.php';
    }

    // 2. Вход в систему
    public function renderLogin() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';

            $userModel = new User($this->db);
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($pass, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                header('Location: index.php?route=home');
                exit;
            } else {
                $error = "Неверный логин или пароль.";
            }
        }
        require_once __DIR__ . '/../../templates/login.php';
    }

    // 3. Выход
    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // 5. Смена пароля (логика обработки формы из профиля)
    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old_pass = $_POST['old_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            $userModel = new User($this->db);
            
            // Получаем хеш текущего пароля из базы
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!password_verify($old_pass, $user['password_hash'])) {
                $error = "Старый пароль введен неверно!";
            } elseif ($new_pass !== $confirm_pass) {
                $error = "Новые пароли не совпадают!";
            } elseif (strlen($new_pass) < 6) {
                $error = "Новый пароль должен быть не менее 6 символов!";
            } else {
                if ($userModel->updatePassword($_SESSION['user_id'], $new_pass)) {
                    $success = "Пароль успешно изменен!";
                } else {
                    $error = "Ошибка при обновлении пароля.";
                }
            }
        }
        // После обработки формы снова показываем страницу профиля, но уже с сообщением
        require_once __DIR__ . '/../../templates/profile.php';
    }

    public function renderProfile() {
    // 1. Проверка авторизации (чтобы посторонние не зашли)
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?route=login');
        exit;
    }

    $userRatings = [];
    $adminStats = [];
    $movieModel = new Movie($this->db);

    // 2. Логика разделения данных
    if ($_SESSION['role'] === 'admin') {
        // Если зашел админ — считаем общую статистику сайта
        $adminStats = [
            'total_movies'  => $this->db->query("SELECT COUNT(*) FROM movies")->fetchColumn(),
            'total_users'   => $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
            'total_ratings' => $this->db->query("SELECT COUNT(*) FROM ratings")->fetchColumn()
        ];
    } else {
        // Если обычный юзер — достаем только его личные оценки
        $userRatings = $movieModel->getUserRatings($_SESSION['user_id']);
    }

    // 3. Подключаем файл шаблона
    require_once __DIR__ . '/../../templates/profile.php';
}
    
    /**
     * Отображение админ-панели
     * Метод, который запрашивает index.php на строке 83
     */
    public function renderAdminPanel() {
        // 1. Проверка: вошел ли пользователь?
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        // 2. Проверка: является ли он админом?
        if ($_SESSION['role'] !== 'admin') {
            die("Доступ запрещен. У вас нет прав администратора.");
        }

        // 3. Если всё ок, загружаем данные для админки (список всех фильмов)
        $movieModel = new Movie($this->db);
        $movies = $movieModel->getAllMovies(); // Убедись, что этот метод есть в модели Movie

        // 4. Подключаем шаблон админ-панели
        require_once __DIR__ . '/../../templates/admin_panel.php';
    }
}