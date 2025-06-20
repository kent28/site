<?php
header('Content-Type: application/json');

$stats = [
    'time_played' => '00:' . rand(10,59),
    'online' => rand(10,50),
    'accounts' => rand(900,1000),
    'characters' => rand(850,950),
    'guilds' => rand(15,25),
    'gm_online' => '[GM] \u0410\u0434\u043c\u0438\u043d',
    'load' => rand(10,70)
];

echo json_encode($stats, JSON_UNESCAPED_UNICODE);
