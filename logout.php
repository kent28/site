<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();
if (isUserLoggedIn()) {
    $username = $_SESSION['user'];
    logoutUser($username);
}

header('Location: index.php');
exit;
?>
