<?php
$serverName = "tcp:185.73.215.215,1433"; // Пример: tcp:192.168.1.100,1433
$connectionOptions = [
    "Database" => "GameDB",
    "Uid" => "PKODev_Game1",
    "PWD" => "dF~6Vomp%",
    "Encrypt" => false, // или true, если требуется
];

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=" . $connectionOptions['Database'], $connectionOptions['Uid'], $connectionOptions['PWD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}