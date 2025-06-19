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

$response = ['captcha' => $_SESSION['captcha']];
if (!empty($successMessage)) {
    $response['success'] = $successMessage;
}
if (!empty($errors)) {
    $response['errors'] = $errors;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;
