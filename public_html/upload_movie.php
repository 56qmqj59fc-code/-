<?php
session_start();
require '../db.php';
require 'check_admin.php'; // только админ может загружать фильмы

// CSRF токен
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Ошибка CSRF");
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $release_year = (int)($_POST['release_year'] ?? 0);

    if (empty($title) || $release_year <= 0) {
        $message = "Введите корректное название и год выпуска";
    }

    // Работа с постером
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poster'];
        $allowedTypes = ['image/jpeg','image/png','image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            $message = "Можно загружать только JPG, PNG, GIF";
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'uploads/' . uniqid('poster_') . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $newName)) {
                $message = "Не удалось сохранить файл";
            }
        }
    } else {
        $message = "Выберите файл постера";
    }

    // Если ошибок нет, сохраняем фильм
    if (empty($message)) {
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, description, release_year, poster_url)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $release_year, $newName]);
        header("Location: admin_panel.php");
        exit;
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
<body class="bg-light">

<div class="container mt-4" style="max-width:600px">

<h2>➕ Добавить новый фильм</h2>

<?php if($message): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="mb-3">
        <label>Название</label>
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
        <label>Постер</label>
        <input type="file" name="poster" class="form-control" accept="image/*" required>
    </div>

    <button class="btn btn-success w-100">Добавить фильм</button>
</form>

<br>
<a href="admin_panel.php" class="btn btn-secondary">← В админ-панель</a>

</div>
</body>
</html>
