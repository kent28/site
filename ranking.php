<?php
include 'templates/header.php';
?>
<div class="main-container">
    <?php include 'templates/left_sidebar.php'; ?>

    <div class="main-content">
        <h2>Рейтинги</h2>
        <?php
        $rank = isset($_GET['rank']) ? strtolower(preg_replace('/[^a-z]/i', '', $_GET['rank'])) : 'exp';
        $valid = ['exp', 'gold', 'guild'];
        if (!in_array($rank, $valid)) {
            $rank = 'exp';
        }

        echo '<nav class="subnav">';
        foreach ($valid as $type) {
            $class = $rank === $type ? 'active' : '';
            echo '<a class="' . $class . '" href="ranking.php?rank=' . $type . '">' . strtoupper($type) . '</a> ';
        }
        echo '</nav>';

        switch ($rank) {
            case 'gold':
                $list = getTopGoldPlayers($config['ranking_limit']);
                echo '<table class="ranking"><tr><th>#</th><th>Персонаж</th><th>Уровень</th><th>Золото</th></tr>';
                $i = 1;
                foreach ($list as $row) {
                    echo '<tr><td>' . $i++ . '</td><td>' . htmlspecialchars($row['name']) . '</td><td>' . $row['level'] . '</td><td>' . $row['gold'] . '</td></tr>';
                }
                echo '</table>';
                break;
            case 'guild':
                $list = getTopGuilds($config['ranking_limit']);
                echo '<table class="ranking"><tr><th>#</th><th>Гильдия</th><th>Девиз</th><th>Участников</th></tr>';
                $i = 1;
                foreach ($list as $row) {
                    echo '<tr><td>' . $i++ . '</td><td>' . htmlspecialchars($row['name']) . '</td><td>' . htmlspecialchars($row['motto']) . '</td><td>' . $row['members'] . '</td></tr>';
                }
                echo '</table>';
                break;
            default:
                $list = getTopExpPlayers($config['ranking_limit']);
                echo '<table class="ranking"><tr><th>#</th><th>Персонаж</th><th>Уровень</th><th>Опыт</th></tr>';
                $i = 1;
                foreach ($list as $row) {
                    echo '<tr><td>' . $i++ . '</td><td>' . htmlspecialchars($row['name']) . '</td><td>' . $row['level'] . '</td><td>' . $row['exp'] . '</td></tr>';
                }
                echo '</table>';
                break;
        }
        ?>
    </div>

    <?php include 'templates/right_sidebar.php'; ?>
</div>

<?php include 'templates/register_modal.php'; ?>
<?php include 'templates/footer.php'; ?>
