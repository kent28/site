<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$sectionId = isset($_GET['section']) ? (int)$_GET['section'] : 0;
$topicId = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;

$topics = getForumTopics($sectionId);
$topic = null;
foreach ($topics as $index => $t) {
    if ($t['id'] === $topicId) {
        $topic = &$topics[$index];
        break;
    }
}

if (!$topic) {
    include 'templates/header.php';
    echo '<h2>Тема не найдена</h2>';
    include 'templates/footer.php';
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isUserLoggedIn()) {
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $errors[] = 'Введите сообщение.';
    } else {
        $topic['posts'][] = [
            'author' => $_SESSION['user'],
            'content' => $content,
            'time' => time()
        ];
        saveForumTopics($sectionId, $topics);
        header('Location: forum_topic.php?section=' . $sectionId . '&topic=' . $topicId);
        exit;
    }
}

include 'templates/header.php';
?>
<h2><?php echo htmlspecialchars($topic['title']); ?></h2>
<ul>
<?php foreach ($topic['posts'] as $post): ?>
    <li>
        <strong><?php echo htmlspecialchars($post['author']); ?></strong>
        (<?php echo date('d.m.Y H:i', $post['time']); ?>):<br>
        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </li>
<?php endforeach; ?>
</ul>
<?php if (isUserLoggedIn()): ?>
<?php if (!empty($errors)): ?>
<div class="error">
    <?php foreach ($errors as $e): ?>
        <p><?php echo htmlspecialchars($e); ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<form class="styled-form" action="forum_topic.php?section=<?php echo $sectionId; ?>&topic=<?php echo $topicId; ?>" method="post">
    <textarea name="content" rows="4" cols="60"></textarea>
    <input type="submit" value="Отправить">
</form>
<?php endif; ?>
<?php include 'templates/footer.php'; ?>
