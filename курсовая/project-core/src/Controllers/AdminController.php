<?php
require_once __DIR__ . '/../Models/Movie.php';

if (!class_exists('AdminController')) {
    class AdminController {
        private $db;
        private $movieModel;

        public function __construct($db) {
            $this->db = $db;
            $this->movieModel = new Movie($db);
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                die("Ошибка 403: Доступ запрещен.");
            }
        }

        public function renderPanel() {
            $movies = $this->movieModel->getList();
            require_once __DIR__ . '/../../templates/admin_panel.php';
        }

        public function renderForm() {
            $movie = null;
            if (isset($_GET['id'])) {
                $movie = $this->movieModel->getById($_GET['id']);
            }
            require_once __DIR__ . '/../../templates/admin_form.php';
        }

        public function saveMovie() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? null;
                $title = $_POST['title'];
                $year = $_POST['year'];
                $desc = $_POST['description'];
                $poster = $_POST['poster_url'];

                if ($id) {
                    $this->movieModel->update($id, $title, $year, $desc, $poster);
                } else {
                    $this->movieModel->add($title, $year, $desc, $poster);
                }
                header('Location: index.php?route=admin');
                exit;
            }
        }

        public function deleteMovie() {
            if (isset($_GET['id'])) {
                $this->movieModel->delete($_GET['id']);
                header('Location: index.php?route=admin');
                exit;
            }
        }
    }
}