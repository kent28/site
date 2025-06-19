<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать на <?php echo htmlspecialchars($config['servername']); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        .close {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Добро пожаловать на <?php echo htmlspecialchars($config['servername']); ?></h1>
    <p><?php echo htmlspecialchars($config['slogan']); ?></p>
    <a href="#" id="registerLink">Регистрация</a>

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
                $('#registrationContainer').load('templates/register.html');
            });

            $('#modalClose').on('click', function(){
                $('#registrationModal').hide();
            });

            // Закрытие модального окна при клике вне него
            $(window).on('click', function(event){
                if (event.target === document.getElementById('registrationModal')) {
                    $('#registrationModal').hide();
                }
            });
        });
    </script>
</body>
</html>
