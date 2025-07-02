<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();

$itemsFile = __DIR__ . '/data/items.json';
$items = [];
if (file_exists($itemsFile)) {
    $items = json_decode(file_get_contents($itemsFile), true) ?: [];
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    if (!isUserLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    $id = (int)$_POST['item_id'];
    $item = null;
    foreach ($items as $it) {
        if ((int)$it['id'] === $id) {
            $item = $it;
            break;
        }
    }
    if ($item) {
        $account = getAccountInfo($_SESSION['user']);
        if ($account && $account['donat'] >= $item['price']) {
            if (updateDonationBalance($_SESSION['user'], -$item['price'])) {
                $success = 'Вы приобрели ' . htmlspecialchars($item['name']);
                $account['donat'] -= $item['price'];
            } else {
                $errors[] = 'Не удалось обновить баланс.';
            }
        } else {
            $errors[] = 'Недостаточно донат валюты.';
        }
    } else {
        $errors[] = 'Предмет не найден.';
    }
}

include 'templates/header.php';
?>
<div class="main-container">
    <?php include 'templates/left_sidebar.php'; ?>
    <div class="main-content">
        <h2>Магазин</h2>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e): ?>
                    <p><?php echo htmlspecialchars($e); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (empty($items)): ?>
            <p>Товары недоступны.</p>
        <?php else: ?>
            <table class="shop">
                <tr><th>Предмет</th><th>Цена</th><th></th></tr>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($it['name']); ?></td>
                        <td><?php echo (int)$it['price']; ?></td>
                        <td>
                            <?php if (isUserLoggedIn()): ?>
                                <form method="post" action="store.php">
                                    <input type="hidden" name="item_id" value="<?php echo (int)$it['id']; ?>">
                                    <input type="submit" value="Купить">
                                </form>
                            <?php else: ?>
                                Войдите для покупки
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <?php include 'templates/right_sidebar.php'; ?>
</div>
<?php include 'templates/register_modal.php'; ?>
<?php include 'templates/footer.php'; ?>

