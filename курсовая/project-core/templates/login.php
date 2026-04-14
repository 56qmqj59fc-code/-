<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
<div class="container" style="max-width: 400px;">
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">🔑 Вход</h3>
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">Регистрация успешна! Войдите.</div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100 py-2">Войти</button>
            </form>
            <div class="mt-3 text-center">
                <a href="index.php?route=register" class="text-decoration-none">Нет аккаунта? Создать</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>