<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$sections = getForumSections();

include 'templates/header.php';
?>
<h2>Форум</h2>
<?php if (isAdmin($_SESSION['user'] ?? '')): ?>
<p><a href="admin_forum_sections.php">Управление разделами</a></p>
<?php endif; ?>
<?php if (empty($sections)): ?>
<p>Разделов пока нет.</p>
<?php else: ?>
<ul>
<?php foreach ($sections as $section): ?>
    <li>
        <a href="forum_section.php?id=<?php echo $section['id']; ?>">
            <?php echo htmlspecialchars($section['title']); ?>
        </a> - <?php echo htmlspecialchars($section['description']); ?>
    </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php include 'templates/footer.php'; ?>
