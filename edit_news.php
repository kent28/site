<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['user'];

if (!isAdmin($username)) {
    include 'templates/header.php';
    echo '<h2>Доступ запрещен</h2>';
    echo '<p>У вас нет прав для просмотра этой страницы.</p>';
    include 'templates/footer.php';
    exit;
}

$newsFile = __DIR__ . '/data/news.html';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    file_put_contents($newsFile, $content);
    $success = 'Новости обновлены.';
}

$content = file_exists($newsFile) ? file_get_contents($newsFile) : '';

include 'templates/header.php';
?>
<h2>Редактирование новостей</h2>
<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<form class="styled-form" action="edit_news.php" method="post" onsubmit="document.getElementById('content').value=document.getElementById('editor').innerHTML;">
    <div id="toolbar">
        <button type="button" data-cmd="bold"><b>B</b></button>
        <button type="button" data-cmd="italic"><i>I</i></button>
        <button type="button" data-cmd="underline"><u>U</u></button>
    </div>
    <div id="editor" contenteditable="true" style="border:1px solid #ccc;min-height:200px;padding:5px;">
        <?php echo $content; ?>
    </div>
    <input type="hidden" name="content" id="content">
    <br>
    <input type="submit" value="Сохранить">
</form>
<script src="assets/js/editor.js"></script>
<?php include 'templates/footer.php'; ?>
