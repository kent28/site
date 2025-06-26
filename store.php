<?php
include 'templates/header.php';
?>
<h2>Магазин</h2>
<form onsubmit="return pay();" class="styled-form">
    <label for="paymentSum">Сумма:</label>
    <input type="number" id="paymentSum" name="paymentSum" value="100" min="1">
    <input type="submit" value="Оплатить">
</form>
<script src="https://widget.unitpay.ru/unitpay.js"></script>
<script type="text/javascript">
function pay() {
    var sum = parseFloat(document.getElementById('paymentSum').value) || 0;
    var payment = new UnitPay();
    payment.createWidget({
        publicKey: "123456-1a234",
        sum: sum,
        account: "demo",
        domainName: "unitpay.ru",
        signature: "7aa705cb4a735d2c576850244912af88edf181db47f4a1fd44a944f6387ae943",
        desc: "Описание платежа",
        locale: "ru"
    });
    payment.success(function (params) {
        console.log('Успешный платеж');
    });
    payment.error(function (message, params) {
        console.log(message);
    });
    return false;
}
</script>
<?php include 'templates/footer.php'; ?>
