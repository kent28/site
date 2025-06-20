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
</head>
<body>
<header>
    <h1><?php echo htmlspecialchars($config['servername']); ?></h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="news.php">Новости</a>
        <a href="login.php">Вход</a>
        <a href="account.php">Личный кабинет</a>
        <a href="download.php">Скачать</a>
        <a href="contact.php">Форма</a>
        <a href="store.php">Магазин</a>
    </nav>
</header>
<main>
