<?php
// Исправлено: добавлены подчеркивания для корректного пути
require_once __DIR__ . '/../Models/Movie.php';

class MovieController {
    private $db;
    private $movieModel;

    public function __construct($db) {
        $this->db = $db;
        $this->movieModel = new Movie($db);
    }

    /**
     * Главная страница: Поиск + Пагинация
     */
    public function renderHome() {
        $searchTitle = $_GET['search_title'] ?? '';
        $searchYear = $_GET['search_year'] ?? '';
        
        $limit = 6;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        if (!empty($searchTitle) || !empty($searchYear)) {
            $allFoundMovies = $this->movieModel->searchAdvanced($searchTitle, $searchYear);
            $totalMovies = count($allFoundMovies);
            $totalPages = ceil($totalMovies / $limit);
            $movies = array_slice($allFoundMovies, $offset, $limit);
        } else {
            $movies = $this->movieModel->getPaginatedList($limit, $offset);
            $totalMovies = $this->movieModel->getTotalCount();
            $totalPages = ceil($totalMovies / $limit);
        }

        require_once __DIR__ . '/../../templates/main_page.php';
    }

    /**
     * Добавление оценки
     */
    public function addRating() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $movieId = $_POST['movie_id'] ?? null;
        $rating = $_POST['rating'] ?? null;

        if ($movieId && $rating) {
            if ($this->movieModel->hasUserRated($userId, $movieId)) {
                header("Location: index.php?route=home&error=already_rated");
            } else {
                $stmt = $this->db->prepare("INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $movieId, (int)$rating]);
                header("Location: index.php?route=home&success=rated");
            }
        } else {
            header("Location: index.php");
        }
        exit;
    }

    /**
     * Добавление в избранное
     */
    public function addToWatchlist() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $movieId = $_GET['id'] ?? null;

        if ($movieId) {
            if ($this->movieModel->isAlreadyInWatchlist($userId, $movieId)) {
                header("Location: index.php?route=home&error=already_in_watchlist");
            } else {
                $this->movieModel->addToWatchlist($userId, $movieId);
                header("Location: index.php?route=home&success=added_to_watchlist");
            }
        } else {
            header("Location: index.php");
        }
        exit;
    }

    /**
     * ПОКАЗ ФОРМЫ (добавить / редактировать)
     */
    public function showForm() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        $movie = null;

        if (isset($_GET['id'])) {
            $movie = $this->movieModel->getById($_GET['id']);
        }

        require __DIR__ . '/../../templates/admin_form.php';
    }

    /**
     * СОХРАНЕНИЕ фильма (Создание или Обновление)
     */
    public function saveMovie() {
        // 1. Проверка прав доступа
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            // Берем старый путь к постеру из скрытого поля (на случай, если файл не меняли)
            $posterUrl = $_POST['old_poster'] ?? '';

            // 2. Логика загрузки нового файла (если он выбран)
            if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['poster_file']['tmp_name'];
                $fileName = $_FILES['poster_file']['name'];
                
                // Генерируем уникальное имя, чтобы не было дублей
                $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
                
                // Путь к папке uploads от корня сервера
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
                
                // Проверяем, существует ли папка, если нет — создаем
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $posterUrl = 'uploads/' . $newFileName;
                } else {
                    die("Ошибка: Не удалось переместить файл в папку uploads. Проверьте права доступа (777).");
                }
            }

            // 3. Собираем данные для модели
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'release_year' => (int)($_POST['release_year'] ?? 0),
                'poster_url' => $posterUrl
            ];

            // 4. Выбор действия: Обновить существующий или Создать новый
            if ($id) {
                $success = $this->movieModel->update(
                    $id,
                    $data['title'],
                    $data['release_year'],
                    $data['description'],
                    $data['poster_url']
                );
            } else {
                $success = $this->movieModel->createMovie($data);
            }

            // 5. Проверка результата сохранения
            if ($success) {
                header('Location: index.php?route=admin&success=1');
            } else {
                // Если база данных вернула false, выводим ошибку для отладки
                echo "<h3>Ошибка при сохранении в базу данных!</h3>";
                echo "<pre>";
                print_r($this->db->errorInfo());
                echo "</pre>";
                exit;
            }
            exit;
        }
    }

    /**
     * УДАЛЕНИЕ фильма
     */
    public function deleteMovie() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        $id = $_GET['id'] ?? null;

        if ($id) {
            $this->movieModel->delete($id);
        }

        header('Location: index.php?route=admin');
        exit;
    }
}