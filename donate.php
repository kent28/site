<?php
include 'templates/header.php';
?>
<h2>Пополнение донат валюты</h2>
<form class="styled-form" id="donate-form" action="https://auth.robokassa.ru/Merchant/Index.aspx" method="POST">
    <label for="sum">Введите сумму:</label>
    <input type="text" id="sum" name="OutSum" required>
    <input type="hidden" name="MerchantLogin" value="ваш_идентификатор_магазина">
    <input type="hidden" name="InvId" value="0">
    <input type="hidden" name="Description" value="Пополнение доната">
    <input type="hidden" name="SignatureValue" id="signature">
    <input type="submit" value="Пополнить">
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script>
document.getElementById('donate-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var sum = document.getElementById('sum').value;
    var login = 'ваш_идентификатор_магазина';
    var pass1 = 'ваш_пароль_1';
    var sign = CryptoJS.MD5(login + ':' + sum + ':0:' + pass1).toString();
    document.getElementById('signature').value = sign;
    this.submit();
});
</script>
<?php include 'templates/footer.php'; ?>
