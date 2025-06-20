<?php
include 'templates/header.php';
?>
<h2>Вход</h2>
<form action="#" method="post">
    <label for="login_username">Имя:</label>
    <input type="text" id="login_username" name="login_username">
    <label for="login_password">Пароль:</label>
    <input type="password" id="login_password" name="login_password">
    <input type="submit" value="Войти">
</form>
<?php include 'templates/footer.php'; ?>
