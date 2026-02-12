<?php
session_start();
require '../db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$step2 = false;
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Ошибка CSRF');
    }

    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Пользователь с таким email не найден';
    } else {
        $step2 = true;
        $user_id = $user['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 450px;">
<div class="card p-4 shadow-sm">

<h4 class="text-center mb-3">🔑 Восстановление пароля</h4>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if (!$step2): ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <button class="btn btn-primary w-100">
        Продолжить
    </button>
</form>

<?php else: ?>

<form action="reset_password.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">

    <div class="mb-3">
        <label>Новый пароль</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Повтор пароля</label>
        <input type="password" name="password_confirm" class="form-control" required>
    </div>

    <button class="btn btn-success w-100">
        Сохранить пароль
    </button>
</form>

<?php endif; ?>

</div>
</div>

</body>
</html>
