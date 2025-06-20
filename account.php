<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['user'];
$account = getAccountInfo($username);
$characters = getAccountCharacters($username);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'changepw') {
        $current = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            $errors[] = 'Заполните все поля для смены пароля.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Новые пароли не совпадают.';
        } elseif (!loginUser($username, $current)) {
            $errors[] = 'Текущий пароль введен неверно.';
        } else {
            $validation = validPassword($new);
            if ($validation !== true) {
                $errors[] = $validation;
            } elseif (updateAccountPassword($username, $new)) {
                $success = 'Пароль успешно изменен.';
            } else {
                $errors[] = 'Не удалось изменить пароль.';
            }
        }
    } elseif ($action === 'changemail') {
        $newEmail = trim($_POST['new_email'] ?? '');
        if ($newEmail === '') {
            $errors[] = 'Введите новый email.';
        } elseif (!validEMail($newEmail)) {
            $errors[] = 'Недействительный email.';
        } elseif (updateAccountEmail($username, $newEmail)) {
            $success = 'Email успешно обновлен.';
            $account['email'] = $newEmail;
        } else {
            $errors[] = 'Не удалось обновить email.';
        }
    }
}

include 'templates/header.php';
?>
<h2>Личный кабинет</h2>
<p><a href="logout.php">Выйти</a></p>
<?php if ($success): ?>
<div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="error">
    <?php foreach ($errors as $error): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<p>Имя пользователя: <?php echo htmlspecialchars($username); ?></p>
<p>Email: <?php echo htmlspecialchars($account['email'] ?? ''); ?></p>
<p>Персонажи на аккаунте:</p>
<ul>
    <?php foreach ($characters as $char): ?>
        <li><?php echo htmlspecialchars($char); ?></li>
    <?php endforeach; ?>
</ul>
<p>Донат валюта: 0</p>
<button>Пополнить баланс</button>

<h3>Смена пароля</h3>
<form action="account.php" method="post">
    <input type="hidden" name="action" value="changepw">
    <label>Текущий пароль:</label>
    <input type="password" name="current_password">
    <label>Новый пароль:</label>
    <input type="password" name="new_password">
    <label>Повторите новый пароль:</label>
    <input type="password" name="confirm_password">
    <input type="submit" value="Сменить пароль">
</form>

<h3>Смена email</h3>
<form action="account.php" method="post">
    <input type="hidden" name="action" value="changemail">
    <label>Новый email:</label>
    <input type="email" name="new_email" value="<?php echo htmlspecialchars($account['email'] ?? ''); ?>">
    <input type="submit" value="Изменить email">
</form>
<?php include 'templates/footer.php'; ?>
