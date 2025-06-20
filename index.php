<?php
include 'templates/header.php';
?>
<div class="main-container">
    <aside class="sidebar left">
        <div class="stats">
            <h3>Статистика</h3>
            <ul>
                <li>Время игры: <span id="stat-time">--:--</span></li>
                <li>Онлайн: <span id="stat-online">--</span></li>
                <li>Аккаунтов: <span id="stat-accounts">--</span></li>
                <li>Персонажей: <span id="stat-characters">--</span></li>
                <li>Гильдий: <span id="stat-guilds">--</span></li>
                <li>Модератор онлайн: <span id="stat-gm-online">--</span></li>
                <li>Нагрузка сервера: <span id="stat-load">--</span></li>
            </ul>
            <h4>Время сервера: <span id="jclock1">--:--:--</span></h4>
        </div>
    </aside>

    <div class="main-content">
        <div class="logo">New Era</div>
        <div class="slider">Здесь будет слайдер с подземельями и сокровищами</div>
        <h3>Игровые новости</h3>
        <p><?php echo htmlspecialchars($config['slogan']); ?></p>
    </div>

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
                <h3>Личный кабинет</h3>
                <p>Привет, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
                <p>Ваши персонажи:</p>
                <ul>
                    <?php foreach (getAccountCharacters($_SESSION['user']) as $char): ?>
                        <li><?php echo htmlspecialchars($char); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Донат валюта: 0</p>
                <a href="account.php">Редактировать профиль</a>
                <br>
                <a href="logout.php">Выйти</a>
            <?php endif; ?>
        </div>
        <div class="character-stats">
            <h3>Характеристики персонажа</h3>
            <ul>
                <li>Сила: 100</li>
                <li>Ловкость: 100</li>
                <li>Телосложение: 100</li>
                <li>Тело: 100</li>
                <li>Дух: 100</li>
            </ul>
        </div>
    </aside>
</div>

<div id="registrationModal" class="modal">
    <div class="modal-content">
        <span class="close" id="modalClose">&times;</span>
        <div id="registrationContainer"></div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#registerLink').on('click', function(e){
        e.preventDefault();
        $('#registrationModal').show();
        $('#registrationContainer').load('templates/register.html', function(){
            $.getScript('js/register.js');
        });
    });

    $('#modalClose').on('click', function(){
        $('#registrationModal').hide();
    });

    $(window).on('click', function(event){
        if (event.target === document.getElementById('registrationModal')) {
            $('#registrationModal').hide();
        }
    });

    $('#jclock1').jclock({utc: true});
});
</script>
<?php include 'templates/footer.php'; ?>
