<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать на <?php echo htmlspecialchars($config['servername']); ?></title>
</head>
<body>
    <h1>Добро пожаловать на <?php echo htmlspecialchars($config['servername']); ?></h1>
    <p><?php echo htmlspecialchars($config['slogan']); ?></p>
    <a href="register.php">Регистрация</a>
</body>
</html>
