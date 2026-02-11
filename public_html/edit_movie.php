<?php
require '../db.php';
require 'check_admin.php'; // запускает session_start() и CSRF

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем фильм
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) die("Фильм не найден");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF защита
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF ошибка");
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $release_year = (int)$_POST['release_year'];
    $duration = trim($_POST['duration']);

    // Работа с постером
    $poster_path = $movie['poster_url'];
    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poster_file'];
        $allowedTypes = ['image/jpeg','image/png','image/gif'];

        if (!in_array($file['type'], $allowedTypes)) {
            $message = 'Можно загружать только JPG, PNG, GIF';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'uploads/' . uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $newName)) {
                $poster_path = $newName;
            } else {
                $message = 'Не удалось сохранить новый постер';
            }
        }
    }

    if (empty($message)) {
        $stmt = $pdo->prepare("
            UPDATE movies 
            SET title=?, description=?, release_year=?, duration=?, poster_url=?
            WHERE id=?
        ");
        $stmt->execute([$title, $description, $release_year, $duration, $poster_path, $id]);
        header("Location: admin_panel.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Редактировать фильм</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
<h1>Редактирование фильма</h1>

<?php if ($message): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card p-4">

    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="mb-3">
        <label>Название</label>
        <input type="text" name="title" 
               value="<?= htmlspecialchars($movie['title']) ?>" 
               class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Описание</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($movie['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Год выпуска</label>
        <input type="number" name="release_year" 
               value="<?= $movie['release_year'] ?>" 
               class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Длительность</label>
        <input type="text" name="duration" 
               value="<?= htmlspecialchars($movie['duration']) ?>" 
               class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Постер (если хотите заменить)</label>
        <input type="file" name="poster_file" class="form-control">
        <?php if ($movie['poster_url']): ?>
            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" 
                 style="height:80px; margin-top:5px;">
        <?php endif; ?>
    </div>

    <button class="btn btn-success">Обновить</button>
</form>

<br>
<a href="admin_panel.php" class="btn btn-secondary">← В панель администратора</a>

</div>
</body>
</html>
