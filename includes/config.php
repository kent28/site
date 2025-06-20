<?php
// Настройки сайта
$config = array();

// Общие настройки сайта
$config['servername'] = 'Your Private Server'; // Название вашего сервера
$config['slogan'] = 'Slogan'; // Слоган
$config['disable_registration'] = false; // Отключение регистрации
$config['allow_dupe_email'] = false; // Разрешение дублирования email
$config['is_gm_server'] = false; // Установите в true, если у вас GM-сервер
$config['max_compatibility_mode'] = false; // Режим максимальной совместимости

// Конфигурация базы данных
$config['db']['account'] = array(
    'host' => 'WIN-LIVFRVQFMKO', // Замените на ваш хост
    'db' => 'AccountServer', // Замените на имя вашей базы данных
    'user' => 'PKODev_Account', // Замените на имя пользователя базы данных
    'pass' => '$~3wVyW~i' // Замените на пароль пользователя базы данных
);

$config['db']['game'] = array(
    'host' => 'WIN-LIVFRVQFMKO',
    'db' => 'GameDB',
    'user' => 'PKODev_Game1',
    'pass' => 'dF~6Vomp%'
);

// Определение констант
define('TABLE_ACCOUNT_LOGIN', 'account_login'); // Имя таблицы для учетных записей
define('TABLE_ACCOUNT', 'account'); // Имя таблицы для аккаунтов
// Дополнительные таблицы для статистики
define('TABLE_CHARACTERS', 'characters');
define('TABLE_GUILDS', 'guilds');
define('DATABASE_ACCOUNT', 'account'); // Имя базы данных для учетных записей
define('DATABASE_GAME', 'game'); // Имя базы данных для игры

// Не изменяйте ничего ниже этой строки!
define('PKOSITE', 1);
define('DEBUG', false);
?>
