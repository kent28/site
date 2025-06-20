<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['servername']); ?></title>
    <link rel="stylesheet" href="templates/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/jclock.js"></script>
    <script src="js/stats.js"></script>
</head>
<body>
<nav class="top-nav">
    <a href="index.php">Главная</a>
    <a href="#">Сообщество</a>
    <a href="download.php">Загрузки</a>
    <a href="ranking.php">Рейтинги</a>
    <a href="#">База Знаний</a>
    <a href="#">Расписание локаций</a>
    <a href="#">Об игре</a>
    <?php if (isUserLoggedIn()): ?>
        <a href="account.php">Личный кабинет</a>
        <?php if (isAdmin($_SESSION['user'])): ?>
            <a href="admin.php">Админка</a>
        <?php endif; ?>
        <a href="logout.php">Выход</a>
    <?php else: ?>
        <a href="login.php">Вход</a>
        <a href="register.php">Регистрация</a>
    <?php endif; ?>
</nav>
<header>
    <h1><?php echo htmlspecialchars($config['servername']); ?></h1>
</header>
<main>
