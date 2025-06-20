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
<form action="edit_news.php" method="post">
    <textarea name="content" rows="10" cols="80"><?php echo htmlspecialchars($content); ?></textarea><br>
    <input type="submit" value="Сохранить">
</form>
<?php include 'templates/footer.php'; ?>
