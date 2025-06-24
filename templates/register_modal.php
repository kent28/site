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
            $.getScript('assets/js/register.js');
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

    $('#jclock1').jclock({utc: true});
});
</script>
