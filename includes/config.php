<?php
// Настройки сайта
$config = array();

// Общие настройки сайта
$config['servername'] = 'New Era server'; // Название вашего сервера
$config['slogan'] = 'Slogan'; // Слоган
$config['disable_registration'] = false; // Отключение регистрации
$config['allow_dupe_email'] = false; // Разрешение дублирования email
$config['is_gm_server'] = false; // Установите в true, если у вас GM-сервер
$config['max_compatibility_mode'] = false; // Режим максимальной совместимости
$config['ranking_limit'] = 10; // Количество записей в таблицах рейтинга
$config['ranking_cache'] = 300; // Время кеширования рейтинга в секундах

// Настройки игрового сервера
$config['server_host'] = '185.73.215.215'; // Адрес игрового сервера
$config['server_port'] = 2020;            // Порт игрового сервера

// Конфигурация базы данных
$config['db']['account'] = array(
    'host' => '185.73.215.215', // Замените на ваш хост
    'db' => 'AccountServer', // Замените на имя вашей базы данных
    'user' => 'PKODev_Account', // Замените на имя пользователя базы данных
    'pass' => '$~3wVyW~i' // Замените на пароль пользователя базы данных
);

$config['db']['game'] = array(
    'host' => '185.73.215.215',
    'db' => 'GameDB',
    'user' => 'PKODev_Game1',
    'pass' => 'dF~6Vomp%'
);

// Определение констант
define('TABLE_ACCOUNT_LOGIN', 'account_login'); // Имя таблицы для учетных записей
define('TABLE_ACCOUNT', 'account'); // Имя таблицы для аккаунтов
// Дополнительные таблицы для статистики
// В GameDB таблица персонажей называется `character`
define('TABLE_CHARACTERS', 'character');
define('TABLE_GUILDS', 'guilds');
define('DATABASE_ACCOUNT', 'account'); // Имя базы данных для учетных записей
define('DATABASE_GAME', 'game'); // Имя базы данных для игры

// Не изменяйте ничего ниже этой строки!
define('PKOSITE', 1);
define('DEBUG', false);
?>
