<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$stats = [
    'time_played' => date('H:i'),
    'online' => getOnlineCount(),
    'accounts' => getAccountsCount(),
    'characters' => getCharactersCount(),
    'guilds' => getGuildsCount(),
    'server_status' => getServerStatus(),
    'server_port' => $config['server_port'],
    'load' => getServerLoad()
];

echo json_encode($stats, JSON_UNESCAPED_UNICODE);
