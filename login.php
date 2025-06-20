<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();

// Получаем ошибки из сессии, если они есть
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['login_username'] ?? '');
    $password = trim($_POST['login_password'] ?? '');

    if ($username === '') {
        $errors[] = 'Вы не ввели имя пользователя.';
    } elseif (!validAccountname($username)) {
        $errors[] = 'Введенное имя пользователя недопустимо.';
    }

    if ($password === '') {
        $errors[] = 'Вы не ввели пароль.';
    } else {
        $passwordValidation = validPassword($password);
        if ($passwordValidation !== true) {
            $errors[] = $passwordValidation;
        }
    }

    if (empty($errors)) {
        if (loginUser($username, $password)) {
            header('Location: account.php');
            exit;
        }
        $errors[] = 'Неверное имя пользователя или пароль.';
    }

    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

include 'templates/header.php';
?>

<h2>Вход</h2>
<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="login.php" method="post">
    <label for="login_username">Имя:</label>
    <input type="text" id="login_username" name="login_username">
    <label for="login_password">Пароль:</label>
    <input type="password" id="login_password" name="login_password">
    <input type="submit" value="Войти">
</form>

<?php include 'templates/footer.php'; ?>
