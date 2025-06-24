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

if (!isAdmin($_SESSION['user'])) {
    include 'templates/header.php';
    echo '<h2>Доступ запрещен</h2>';
    include 'templates/footer.php';
    exit;
}

$sections = getForumSections();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($title === '') {
            $errors[] = 'Введите название раздела';
        } else {
            $id = 1;
            foreach ($sections as $s) {
                if ($s['id'] >= $id) {
                    $id = $s['id'] + 1;
                }
            }
            $sections[] = [
                'id' => $id,
                'title' => $title,
                'description' => $desc
            ];
            saveForumSections($sections);
            $sections = getForumSections();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $new = [];
        foreach ($sections as $s) {
            if ($s['id'] != $id) {
                $new[] = $s;
            }
        }
        $sections = $new;
        saveForumSections($sections);
        $topicsFile = __DIR__ . '/data/forum_topics_' . $id . '.json';
        if (file_exists($topicsFile)) {
            unlink($topicsFile);
        }
    }
}

include 'templates/header.php';
?>
<h2>Управление разделами форума</h2>
<?php if (!empty($errors)): ?>
<div class="error">
    <?php foreach ($errors as $e): ?>
        <p><?php echo htmlspecialchars($e); ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<h3>Существующие разделы</h3>
<ul>
<?php foreach ($sections as $sec): ?>
    <li>
        <?php echo htmlspecialchars($sec['title']); ?> - <?php echo htmlspecialchars($sec['description']); ?>
        <form action="admin_forum_sections.php" method="post" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo $sec['id']; ?>">
            <input type="submit" value="Удалить">
        </form>
    </li>
<?php endforeach; ?>
</ul>
<h3>Добавить раздел</h3>
<form action="admin_forum_sections.php" method="post">
    <input type="hidden" name="action" value="add">
    <label>Название:</label>
    <input type="text" name="title">
    <label>Описание:</label>
    <input type="text" name="description">
    <input type="submit" value="Добавить">
</form>
<?php include 'templates/footer.php'; ?>
