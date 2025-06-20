<?php
require_once __DIR__ . '/../includes/config.php';
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
<header>
    <h1><?php echo htmlspecialchars($config['servername']); ?></h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="#">Сообщество</a>
        <a href="download.php">Загрузки</a>
        <a href="#">Рейтинги</a>
        <a href="#">База Знаний</a>
        <a href="#">Расписание локаций</a>
        <a href="#">Об игре</a>
    </nav>
</header>
<main>
