<?php
include 'templates/header.php';

$newsFile = __DIR__ . '/data/news.html';

if (file_exists($newsFile)) {
    readfile($newsFile);
} else {
    echo '<h2>Новости</h2><p>Файл новостей не найден.</p>';
}

include 'templates/footer.php';
