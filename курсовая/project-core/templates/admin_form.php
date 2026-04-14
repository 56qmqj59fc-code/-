<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Добавление фильма — КиноМир</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🎬 КиноМир: Админ-панель</a>
        <a href="index.php?route=admin" class="btn btn-outline-light btn-sm">Назад к списку</a>
    </div>
</nav>

<div class="card shadow-sm p-4 form-card">
    <h2 class="fw-bold mb-4">
        <?= isset($movie) ? '✏️ Редактировать фильм' : '✨ Добавить новый фильм' ?>
    </h2>
    
    <form action="index.php?route=admin_save_movie" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id" value="<?= $movie['id'] ?? '' ?>">
        <input type="hidden" name="old_poster" value="<?= $movie['poster_url'] ?? '' ?>">
        
        <div class="mb-3">
            <label class="form-label fw-bold">Название фильма</label>
            <input type="text" name="title" class="form-control" 
                   value="<?= htmlspecialchars($movie['title'] ?? '') ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Год выпуска</label>
                <input type="number" name="release_year" class="form-control" 
                       value="<?= $movie['release_year'] ?? '' ?>" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Постер</label>
                <input type="file" name="poster_file" class="form-control" accept="image/*" 
                       <?= isset($movie) ? '' : 'required' ?>>
                
                <?php if (isset($movie) && $movie['poster_url']): ?>
                    <div class="mt-2">
                        <small class="text-muted">Текущий файл: <?= $movie['poster_url'] ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Описание фильма</label>
            <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($movie['description'] ?? '') ?></textarea>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="index.php?route=admin" class="btn btn-light rounded-pill px-4">Отмена</a>
            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                <?= isset($movie) ? 'Сохранить изменения' : 'Добавить фильм' ?>
            </button>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>