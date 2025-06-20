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

$stats = [
    'accounts' => getAccountsCount(),
    'characters' => getCharactersCount(),
    'guilds' => getGuildsCount(),
    'online' => getOnlineCount(),
    'gm_online' => getGMOnline()
];

include 'templates/header.php';
?>
<h2>Админ-панель</h2>
<ul>
    <li>Всего аккаунтов: <?php echo $stats['accounts']; ?></li>
    <li>Всего персонажей: <?php echo $stats['characters']; ?></li>
    <li>Всего гильдий: <?php echo $stats['guilds']; ?></li>
    <li>Игроков онлайн: <?php echo $stats['online']; ?></li>
    <li>Модератор онлайн: <?php echo htmlspecialchars($stats['gm_online']); ?></li>
</ul>
<?php include 'templates/footer.php'; ?>
