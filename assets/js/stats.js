$(document).ready(function(){
    function loadStats(){
        $.getJSON('stats.php', function(data){
            $('#stat-time').text(data.time_played);
            $('#stat-online').text(data.online + ' игроков');
            $('#stat-accounts').text(data.accounts);
            $('#stat-characters').text(data.characters);
            $('#stat-guilds').text(data.guilds);
            $('#stat-server').text(data.server_status);
            $('#stat-port').text(data.server_port);
            $('#stat-load').text(data.load + '%');
        });
    }
    loadStats();
    setInterval(loadStats, 60000);
});
