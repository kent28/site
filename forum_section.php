<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

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

$topics = getForumTopics($sectionId);

include 'templates/header.php';
?>
<h2><?php echo htmlspecialchars($section['title']); ?></h2>
<?php if (isUserLoggedIn()): ?>
<p><a href="forum_new_topic.php?id=<?php echo $sectionId; ?>">Создать тему</a></p>
<?php endif; ?>
<?php if (empty($topics)): ?>
<p>Тем пока нет.</p>
<?php else: ?>
<ul>
<?php foreach ($topics as $topic): ?>
    <li>
        <a href="forum_topic.php?section=<?php echo $sectionId; ?>&topic=<?php echo $topic['id']; ?>">
            <?php echo htmlspecialchars($topic['title']); ?>
        </a> (<?php echo count($topic['posts']); ?>)
    </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php include 'templates/footer.php'; ?>
