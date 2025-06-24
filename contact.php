<?php
include 'templates/header.php';
?>
<h2>Форма обратной связи</h2>
<form class="styled-form" action="#" method="post">
    <label for="contact_name">Имя:</label>
    <input type="text" id="contact_name" name="contact_name">
    <label for="contact_email">Email:</label>
    <input type="email" id="contact_email" name="contact_email">
    <label for="contact_message">Сообщение:</label>
    <textarea id="contact_message" name="contact_message"></textarea>
    <input type="submit" value="Отправить">
</form>
<?php include 'templates/footer.php'; ?>
