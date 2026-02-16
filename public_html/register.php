<?php
require '../db.php'; 

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $passConfirm = $_POST['password_confirm'];

    if (empty($email) || empty($pass)) {
        $errorMsg = "Заполните все поля!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Некорректный формат Email!";
    } elseif ($pass !== $passConfirm) {
        $errorMsg = "Пароли не совпадают!";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, password_hash, role) 
                VALUES (:email, :hash, 'client')";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':hash' => $hash
            ]);
            $successMsg = "Регистрация успешна! <a href='login.php'>Войти</a>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errorMsg = "Такой email уже зарегистрирован.";
            } else {
                $errorMsg = "Ошибка БД: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    
    <!-- ВАЖНО ДЛЯ МОБИЛЬНЫХ -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Регистрация</title>

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
        
        <!-- col-12 для телефона, col-md-6 для ПК -->
        <div class="col-12 col-md-6 col-lg-5">
            
            <div class="card shadow">
                
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Регистрация</h4>
                </div>
                
                <div class="card-body">

                    <?php if($errorMsg): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errorMsg) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($successMsg): ?>
                        <div class="alert alert-success text-center">
                            <?= $successMsg ?>
                        </div>
                    <?php else: ?>

                    <form method="POST" action="register.php">

                        <div class="mb-3">
                            <label class="form-label">Email адрес</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   required
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" 
                                   name="password_confirm" 
                                   class="form-control" 
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Зарегистрироваться
                        </button>

                    </form>

                    <div class="mt-3 text-center">
                        <a href="login.php">
                            Уже есть аккаунт? Войти
                        </a>
                    </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
