<?php
include 'templates/header.php';
?>
<div class="main-container">
    <aside class="sidebar">
        <div class="stats">
            <h3>Статистика</h3>
            <ul>
                <li>Время игры: 00:16</li>
                <li>Онлайн: 40 игроков</li>
                <li>Аккаунтов: 972</li>
                <li>Персонажей: 905</li>
                <li>Гильдий: 20</li>
                <li>Модератор онлайн: [GM] Админ</li>
                <li>Нагрузка сервера: 21%</li>
            </ul>
            <h4>Скорость развития</h4>
            <ul>
                <li>Игровой уровень x8</li>
                <li>В отряде x12</li>
            </ul>
        </div>
    </aside>

    <div class="main-content">
        <div class="logo">New Era</div>
        <div class="slider">Здесь будет слайдер с подземельями и сокровищами</div>
        <h3>Игровые новости</h3>
        <p><?php echo htmlspecialchars($config['slogan']); ?></p>
    </div>

    <aside class="sidebar">
        <div class="login-box">
            <h3>Вход в игру</h3>
            <form action="login.php" method="post">
                <input type="text" name="login_username" placeholder="Имя пользователя">
                <input type="password" name="login_password" placeholder="Пароль">
                <input type="submit" value="Войти">
            </form>
            <button id="registerLink">Регистрация</button>
            <a href="#">Я забыл свой пароль</a>
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
});
</script>
<?php include 'templates/footer.php'; ?>
