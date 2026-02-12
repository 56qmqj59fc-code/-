<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require '../db.php';

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin_panel.php");
        } else {
            header("Location: index.php");
        }
        exit;

    } else {
        $errorMsg = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">

    <!-- ВАЖНО ДЛЯ МОБИЛЬНЫХ -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Вход</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* ===== Мобильная адаптация ===== */
@media (max-width: 768px) {

    body {
        padding: 15px;
    }

    .card {
        border-radius: 15px;
    }

    .card-header h4 {
        font-size: 1.2rem;
        text-align: center;
    }

    .container {
        margin-top: 20px !important;
    }

}

</style>
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">

<div class="container">
    <div class="row justify-content-center">

        <!-- col-12 для мобильных, col-md-6 для ПК -->
        <div class="col-12 col-md-6 col-lg-5">

            <div class="card shadow">

                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">Вход в систему</h4>
                </div>

                <div class="card-body">

                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($errorMsg) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMsg): ?>
                        <div class="alert alert-success text-center">
                            <?= htmlspecialchars($successMsg) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">

                        <div class="mb-3">
                            <label class="form-label">Email адрес</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Войти
                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <p class="mb-1">
                            Нет аккаунта?
                            <a href="register.php">Зарегистрироваться</a>
                        </p>

                        <p class="mb-0">
                            <a href="forgot_password.php">Забыли пароль?</a>
                        </p>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
