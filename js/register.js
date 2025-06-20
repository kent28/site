$(document).ready(function() {
    var modal = document.getElementById("registrationModal");

    // Загрузка CAPTCHA при открытии формы
    function loadCaptcha() {
        $.get('register.php', function(data) {
            $('#captchaText').text(data.captcha);
        }, 'json');
    }

    loadCaptcha();

    // Проверка доступности имени пользователя
    $('#username').on('input', function() {
        var username = $(this).val();
        if (username.length >= 5) {
            $.get('check_username.php', { username: username }, function(data) {
                if (data === 'true') {
                    $('#usernameStatus').text('Имя пользователя доступно').removeClass('unavailable').addClass('available');
                } else {
                    $('#usernameStatus').text('Имя пользователя уже занято').removeClass('available').addClass('unavailable');
                }
            });
        } else {
            $('#usernameStatus').text('');
        }
    });

    // Оценка сложности пароля
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        var meterFill = $('#strengthMeterFill');
        var strengthText = $('#passwordStrengthText');

        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
        if (password.match(/\d/)) strength += 1;
        if (password.match(/[^a-zA-Z\d]/)) strength += 1;

        meterFill.removeClass('strength-weak strength-medium strength-strong');
        if (strength <= 2) {
            meterFill.addClass('strength-weak');
            strengthText.text('Слабый');
        } else if (strength <= 4) {
            meterFill.addClass('strength-medium');
            strengthText.text('Средний');
        } else {
            meterFill.addClass('strength-strong');
            strengthText.text('Сильный');
        }
    });

    // Обработка отправки формы
    $('#registrationForm').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: 'register.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(result) {
                $('#errorMessages').empty();
                $('#successMessage').empty();
                if (result.success) {
                    $('#successMessage').text(result.success);
                    alert(result.success);
                    modal.style.display = "none";
                } else if (result.errors && result.errors.length > 0) {
                    result.errors.forEach(function(error) {
                        $('#errorMessages').append('<p>' + error + '</p>');
                    });
                }
                $('#captchaText').text(result.captcha);
                $('#captcha').val('');
            },
            error: function(xhr, status, error) {
                alert('Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.');
            }
        });
    });
});
