<?php
include 'templates/header.php';
?>
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
