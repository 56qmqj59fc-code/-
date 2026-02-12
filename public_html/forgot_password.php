<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require '../db.php';

$error = '';
$step2 = false;
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Ошибка CSRF');
    }

    // Шаг 1: ввод email
    if (isset($_POST['email']) && !isset($_POST['password'])) {
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

    // Шаг 2: смена пароля
    if (isset($_POST['password']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $password_confirm) {
            $error = 'Пароли не совпадают';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $password)) {
            $error = 'Пароль должен содержать минимум 8 символов, 1 заглавную букву, 1 цифру и 1 спецсимвол';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            header('Location: login.php?password=success');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Восстановление пароля</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Стиль как в форме регистрации ===== */
body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.password-wrapper {
    position: relative;
}
.password-wrapper input {
    padding-right: 40px;
}
.toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
}

#step2-form {
    transition: all 0.5s ease;
    opacity: 0;
}
</style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">🔑 Восстановление пароля</h4>
                </div>
                <div class="card-body">

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if(!$step2): ?>
                    <!-- Шаг 1: Email -->
                    <form method="post" id="step1-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100 mb-2">Продолжить</button>
                        <a href="login.php" class="btn btn-secondary w-100">Назад ко входу</a>
                    </form>
                    <?php else: ?>
                    <!-- Шаг 2: Новый пароль -->
                    <form method="post" id="step2-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">

                        <div class="mb-3 password-wrapper">
                            <label>Новый пароль</label>
                            <input type="password" name="password" class="form-control" required id="password">
                            <span class="toggle-password" onclick="togglePassword()">👁</span>
                        </div>

                        <div class="mb-3 password-wrapper">
                            <label>Повтор пароля</label>
                            <input type="password" name="password_confirm" class="form-control" required id="password_confirm">
                            <span class="toggle-password" onclick="toggleConfirm()">👁</span>
                        </div>

                        <button class="btn btn-success w-100 mb-2">Сохранить пароль</button>
                        <a href="login.php" class="btn btn-secondary w-100">Назад ко входу</a>
                    </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    const p = document.getElementById('password');
    p.type = p.type === 'password' ? 'text' : 'password';
}
function toggleConfirm(){
    const p = document.getElementById('password_confirm');
    p.type = p.type === 'password' ? 'text' : 'password';
}

<?php if($step2): ?>
window.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('step2-form');
    setTimeout(()=>{ form.style.opacity = 1; }, 50);
});
<?php endif; ?>
</script>

</body>
</html>
