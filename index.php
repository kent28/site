<?php
include 'templates/header.php';
?>
<div class="main-container">
    <?php include 'templates/left_sidebar.php'; ?>

    <div class="main-content">
        <div class="logo">New Era</div>
        <div class="slider">
    <div class="slider-text">События в подземелье</div>

    <div class="entry" data-open="0/2/0" data-duration="0/0/40" data-stay="0/0/50">
        <div class="label">Подземелье А:</div>
        <span class="status">⏳</span>
        <span class="countdown">00:00:00</span>
    </div>

    <div class="entry" data-open="0/0/3" data-duration="0/0/2" data-stay="0/0/1">
        <div class="label">Подземелье Б:</div>
        <span class="status">⏳</span>
        <span class="countdown">00:00:00</span>
    </div>
</div>
        <div class="news-block">
            <?php
            $newsFile = __DIR__ . '/data/news.html';
            if (file_exists($newsFile)) {
                include $newsFile;
                echo '<script src="assets/js/news.js"></script>';
            } else {
                echo '<p>Новости еще не опубликованы.</p>';
            }
            ?>
        </div>
    </div>

    <?php include 'templates/right_sidebar.php'; ?>
</div>

<?php include 'templates/register_modal.php'; ?>
<?php include 'templates/footer.php'; ?>
<script src="assets/js/slider.js"></script>