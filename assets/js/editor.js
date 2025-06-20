$(function(){
    $('#toolbar button').on('click', function(){
        document.execCommand($(this).data('cmd'), false, null);
    });
});
