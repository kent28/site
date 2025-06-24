<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$sectionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sections = getForumSections();
$section = null;
foreach ($sections as $s) {
    if ($s['id'] === $sectionId) {
        $section = $s;
        break;
    }
}

if (!$section) {
    include 'templates/header.php';
    echo '<h2>Раздел не найден</h2>';
    include 'templates/footer.php';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title === '' || $content === '') {
        $errors[] = 'Заполните все поля.';
    }
    if (empty($errors)) {
        $topics = getForumTopics($sectionId);
        $topicId = 1;
        foreach ($topics as $t) {
            if ($t['id'] >= $topicId) {
                $topicId = $t['id'] + 1;
            }
        }
        $topics[] = [
            'id' => $topicId,
            'title' => $title,
            'author' => $_SESSION['user'],
            'posts' => [
                [
                    'author' => $_SESSION['user'],
                    'content' => $content,
                    'time' => time()
                ]
            ]
        ];
        saveForumTopics($sectionId, $topics);
        header('Location: forum_topic.php?section=' . $sectionId . '&topic=' . $topicId);
        exit;
    }
}

include 'templates/header.php';
?>
<h2>Новая тема в <?php echo htmlspecialchars($section['title']); ?></h2>
<?php if (!empty($errors)): ?>
<div class="error">
    <?php foreach ($errors as $e): ?>
        <p><?php echo htmlspecialchars($e); ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<form class="styled-form" action="forum_new_topic.php?id=<?php echo $sectionId; ?>" method="post">
    <label>Заголовок:</label>
    <input type="text" name="title">
    <label>Сообщение:</label>
    <textarea name="content" rows="5" cols="60"></textarea>
    <input type="submit" value="Создать">
</form>
<?php include 'templates/footer.php'; ?>
