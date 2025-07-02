$(document).ready(function(){
    function loadStats(){
        $.getJSON('stats.php', function(data){
            $('#stat-time').text(data.time_played);
            $('#stat-online').text(data.online + ' игроков');
            $('#stat-accounts').text(data.accounts);
            $('#stat-characters').text(data.characters);
            $('#stat-guilds').text(data.guilds);
             var serverEl = $('#stat-server');
            serverEl.text(data.server_status);
            if (data.server_status.toLowerCase() === 'онлайн') {
                serverEl.removeClass('offline').addClass('online');
            } else {
                serverEl.removeClass('online').addClass('offline');
            }
            $('#stat-port').text(data.server_port);
            $('#stat-load').text(data.load + '%');
            $('#load-bar').css('width', data.load + '%');
            $('#stat-port').text(data.server_port);
            $('#stat-load').text(data.load + '%');
        });
    }
    loadStats();
    setInterval(loadStats, 60000);
});
