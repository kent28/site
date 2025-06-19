<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$username = $_GET['username'] ?? '';

if (!empty($username)) {
    if (checkDuplicateAccountName($username)) {
        echo 'false';
    } else {
        echo 'true';
    }
} else {
    echo 'false';
}
?>
