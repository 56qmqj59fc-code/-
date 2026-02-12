<?php
session_start();
require '../db.php';
require 'check_admin.php';

// Создание CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Получаем все фильмы
$stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
$movies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админ-панель</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Отступы между кнопками действий */
.action-buttons a,
.action-buttons form {
    display: inline-block;
    margin-right: 5px; /* расстояние между кнопками */
    margin-bottom: 3px; /* небольшой нижний отступ */
}

.action-buttons form button {
    margin-right: 0; /* у кнопки внутри формы свой margin не нужен */
}
</style>
</head>
<body class="p-4">

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>⚙️ Админ-панель</h1>
        <div>
            <a href="add_item.php" class="btn btn-success me-2">➕ Добавить фильм</a>
            <a href="logout.php" class="btn btn-secondary">Выйти</a>
        </div>
    </div>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Постер</th>
                <th>Название</th>
                <th>Год</th>
                <th>Длительность</th>
                <th width="180">Действия</th>
            </tr>
        </thead>
        <tbody>

        <?php if (count($movies) > 0): ?>
            <?php foreach ($movies as $movie): ?>
                <tr>
                    <td><?= $movie['id'] ?></td>

                    <td>
                        <?php if (!empty($movie['poster_url'])): ?>
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>"
                                 style="height:80px; object-fit:cover;">
                        <?php else: ?>
                            Нет постера
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($movie['title']) ?></td>
                    <td><?= $movie['release_year'] ?></td>
                    <td><?= htmlspecialchars($movie['duration']) ?></td>

                    <td class="action-buttons">

                        <!-- Кнопка редактирования -->
                        <a href="edit_movie.php?id=<?= $movie['id'] ?>"
                           class="btn btn-warning btn-sm">
                           ✏️ Редактировать
                        </a>

                        <!-- Кнопка удаления (POST!) -->
                        <form action="delete_movie.php"
                              method="POST"
                              onsubmit="return confirm('Вы уверены, что хотите удалить фильм?');">

                            <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <button type="submit" class="btn btn-danger btn-sm">
                                🗑️ Удалить
                            </button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Фильмов пока нет</td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>
