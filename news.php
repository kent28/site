<?php
include 'templates/header.php';
?>
<div class="main-container">
    <?php include 'templates/left_sidebar.php'; ?>

    <div class="main-content">
        <div class="news-block">
            <?php
            $newsFile = __DIR__ . '/data/news.html';
            if (file_exists($newsFile)) {
                readfile($newsFile);
                echo '<script src="assets/js/news.js"></script>';
            } else {
                echo '<h2>Новости</h2><p>Файл новостей не найден.</p>';
            }
            ?>
        </div>
    </div>

    <?php include 'templates/right_sidebar.php'; ?>
</div>

<?php include 'templates/register_modal.php'; ?>
<?php include 'templates/footer.php'; ?>
