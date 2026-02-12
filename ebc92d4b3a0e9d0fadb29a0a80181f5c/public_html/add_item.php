<?php
require '../db.php';
require 'check_admin.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    $year  = (int)$_POST['release_year'];
    $duration = trim($_POST['duration']); // можно в формате "120 мин"

    // Проверяем обязательные поля
    if (empty($title) || empty($year) || empty($duration)) {
        $message = '<div class="alert alert-danger">Заполните все обязательные поля</div>';
    } elseif (!isset($_FILES['poster_file']) || $_FILES['poster_file']['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">Ошибка загрузки постера</div>';
    } else {

        // Настройки загрузки
        $uploadDir = 'uploads/'; // папка для постеров
        $file = $_FILES['poster_file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // Проверка типа файла
        if (!in_array($file['type'], $allowedTypes)) {
            $message = '<div class="alert alert-danger">Можно загружать только JPG, PNG, GIF</div>';
        } else {

            // Генерация уникального имени файла
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = uniqid('img_') . '.' . $ext;
            $destination = $uploadDir . $newName;

            // Перемещение файла в папку uploads
            if (move_uploaded_file($file['tmp_name'], $destination)) {

                // Сохраняем фильм в базу данных
                $sql = "INSERT INTO movies (title, description, poster_url, release_year, duration)
                        VALUES (:t, :d, :p, :y, :dur)";
                $stmt = $pdo->prepare($sql);

                try {
                    $stmt->execute([
                        ':t' => $title,
                        ':d' => $desc,
                        ':p' => $destination,
                        ':y' => $year,
                        ':dur' => $duration
                    ]);
                    $message = '<div class="alert alert-success">Фильм успешно добавлен!</div>';
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Ошибка БД: ' . $e->getMessage() . '</div>';
                }

            } else {
                $message = '<div class="alert alert-danger">Не удалось сохранить файл</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить фильм</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1 class="mb-4">Добавление фильма</h1>

    <a href="admin_panel.php" class="btn btn-secondary mb-3">← В админку</a>

    <?= $message ?>

    <form method="POST" class="card p-4" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Название фильма</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Описание</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Год выпуска</label>
            <input type="number" name="release_year" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Длительность фильма (например, 120 мин)</label>
            <input type="text" name="duration" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Постер фильма (файл)</label>
            <input type="file" name="poster_file" class="form-control" required>
        </div>

        <button class="btn btn-success">Сохранить</button>
    </form>

</div>
</body>
</html>
