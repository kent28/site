$(document).ready(function(){
    function loadStats(){
        $.getJSON('stats.php', function(data){
            $('#stat-time').text(data.time_played);
            $('#stat-online').text(data.online + ' игроков');
            $('#stat-accounts').text(data.accounts);
            $('#stat-characters').text(data.characters);
            $('#stat-guilds').text(data.guilds);
            $('#stat-gm-online').text(data.gm_online);
            $('#stat-load').text(data.load + '%');
        });
    }
    loadStats();
    setInterval(loadStats, 60000);
});
