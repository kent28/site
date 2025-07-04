<aside class="sidebar right">
    <div class="login-box">
        <?php if (!isUserLoggedIn()): ?>
            <h3>Вход в игру</h3>
            <form action="login.php" method="post">
                <input type="text" name="login_username" placeholder="Имя пользователя">
                <input type="password" name="login_password" placeholder="Пароль">
                <input type="submit" value="Войти">
            </form>
            <button id="registerLink">Регистрация</button>
            <a href="#">Я забыл свой пароль</a>
        <?php else: ?>
            <?php $account = getAccountInfo($_SESSION['user']); ?>
            <h3>Личный кабинет</h3>
            <p>Привет, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
            <p>Ваши персонажи:</p>
            <ul>
                <?php foreach (getAccountCharacters($_SESSION['user']) as $char): ?>
                    <li><?php echo htmlspecialchars($char); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Донат валюта: <?php echo htmlspecialchars($account['donat'] ?? 0); ?></p>
            <a href="donate.php">Пополнить баланс</a><br>
            <a href="account.php">Сменить Пароль</a><br>
            <a href="logout.php">Выйти</a>
        <?php endif; ?>
    </div>
    <div class="character-stats">
        <h3>Характеристики персонажа</h3>
        <ul>
            <li>Сила: 500</li>
            <li>Ловкость: 500</li>
            <li>Телосложение: 500</li>
            <li>Тело: 500</li>
            <li>Дух: 500</li>
        </ul>
    </div>
</aside>
