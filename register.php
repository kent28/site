<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Инициализация сессии
session_start();

// Очистка ошибок и сообщений при обновлении страницы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['errors']);
    unset($_SESSION['successMessage']);
}

// Проверка, отключена ли регистрация
if ($config['disable_registration']) {
    die('Регистрация в настоящее время отключена.');
}

// Генерация CAPTCHA, если она еще не установлена
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = substr(md5(uniqid(rand(), true)), 0, 6);
}

// Переменные для сообщений
$successMessage = '';
$errors = [];

// Проверка, была ли отправлена форма
if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username'] ?? '');
    $password = trim($_POST['reg_password'] ?? '');
    $password2 = trim($_POST['reg_password2'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $userCaptcha = trim($_POST['reg_captcha'] ?? '');

    // Проверка CAPTCHA
    if (!isset($_SESSION['captcha']) || strtolower($userCaptcha) !== strtolower($_SESSION['captcha'])) {
        $errors[] = 'Неверная CAPTCHA.';
    }

    // Проверка ввода
    if (empty($username)) {
        $errors[] = 'Вы не ввели имя пользователя.';
    } elseif (!validAccountname($username)) {
        $errors[] = 'Введенное имя пользователя недопустимо.';
    } elseif (checkDuplicateAccountName($username)) {
        $errors[] = 'Введенное имя пользователя уже занято.';
    }

    if (empty($password)) {
        $errors[] = 'Вы не ввели пароль.';
    } else {
        $passwordValidation = validPassword($password);
        if ($passwordValidation !== true) {
            $errors[] = $passwordValidation;
        }
    }

    if (empty($password2)) {
        $errors[] = 'Вы не повторили пароль.';
    } elseif ($password != $password2) {
        $errors[] = 'Введенные пароли не совпадают.';
    }

    if (empty($email)) {
        $errors[] = 'Вы не ввели адрес электронной почты.';
    } elseif (!validEMail($email)) {
        $errors[] = 'Вы ввели недопустимый адрес электронной почты.';
    } elseif (!$config['allow_dupe_email'] && checkDuplicateEMail($email)) {
        $errors[] = 'Введенный адрес электронной почты уже используется.';
    }

    if (empty($errors)) {
        if (createAccount($username, $password, $email)) {
            $successMessage = "Регистрация прошла успешно!";
            // Очистка CAPTCHA и ошибок после успешной регистрации
            unset($_SESSION['captcha']);
            $_SESSION['errors'] = [];
        } else {
            $errors[] = 'Произошла ошибка при создании вашего аккаунта. Пожалуйста, попробуйте позже.';
        }
    }

    // Сохранение ошибок в сессии
    $_SESSION['errors'] = $errors;
    $_SESSION['successMessage'] = $successMessage;
} else {
    // Очистка ошибок и сообщений при первом заходе на страницу
    $_SESSION['errors'] = [];
    $_SESSION['successMessage'] = '';
}

// Обновление CAPTCHA после каждой попытки регистрации
$_SESSION['captcha'] = substr(md5(uniqid(rand(), true)), 0, 6);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация пирата</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('https://example.com/pirate-background.jpg');
            background-size: cover;
            background-position: center;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            border: 2px solid #d4af37;
        }
        h1 {
            text-align: center;
            color: #d4af37;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #d4af37;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #d4af37;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        input[type="submit"] {
            background-color: #d4af37;
            color: #000;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #f1c40f;
        }
        .captcha-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .captcha-text {
            font-family: 'Arial', sans-serif;
            font-size: 1.5em;
            letter-spacing: 3px;
            color: #d4af37;
            margin-right: 10px;
            background: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
            border-radius: 5px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        .username-status {
            margin-top: -15px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .available {
            color: green;
        }
        .unavailable {
            color: red;
        }
        .password-strength {
            margin-top: -15px;
            margin-bottom: 15px;
        }
        .strength-meter {
            height: 5px;
            background-color: #ddd;
            margin-top: 5px;
            margin-bottom: 5px;
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-meter-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background-color 0.3s;
        }
        .strength-weak {
            background-color: #ff4d4d;
            width: 33%;
        }
        .strength-medium {
            background-color: #ffcc00;
            width: 66%;
        }
        .strength-strong {
            background-color: #00cc00;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Регистрация пирата</h1>
        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="error">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['successMessage'])): ?>
            <div class="success">
                <p><?php echo htmlspecialchars($_SESSION['successMessage']); ?></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <label for="username">Имя пирата:</label>
            <input type="text" id="username" name="reg_username" required>
            <div id="usernameStatus" class="username-status"></div>

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="reg_password" required>
            <div class="password-strength">
                <div class="strength-meter">
                    <div id="strengthMeterFill" class="strength-meter-fill"></div>
                </div>
                <div id="passwordStrengthText"></div>
            </div>

            <label for="password2">Повторите пароль:</label>
            <input type="password" id="password2" name="reg_password2" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="reg_email" required>

            <label for="captcha">Введите CAPTCHA:</label>
            <div class="captcha-container">
                <div class="captcha-text">
                    <?php echo isset($_SESSION['captcha']) ? htmlspecialchars($_SESSION['captcha']) : ''; ?>
                </div>
                <input type="text" id="captcha" name="reg_captcha" required>
            </div>

            <input type="submit" name="register" value="Зарегистрироваться">
        </form>
    </div>

    <script>
        $(document).ready(function() {
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

                // Длина пароля
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;

                // Наличие букв в разных регистрах
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;

                // Наличие цифр
                if (password.match(/\d/)) strength += 1;

                // Наличие специальных символов
                if (password.match(/[^a-zA-Z\d]/)) strength += 1;

                // Обновление шкалы сложности
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
        });
    </script>
</body>
</html>
