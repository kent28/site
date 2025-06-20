(function($){
    $.fn.jclock = function(options){
        var settings = $.extend({utc:false, utc_offset:0}, options);
        return this.each(function(){
            var $this = $(this);
            function update(){
                var date = new Date();
                if(settings.utc){
                    var utc = date.getTime() + date.getTimezoneOffset()*60000;
                    date = new Date(utc + settings.utc_offset*3600000);
                }
                var h = date.getHours().toString().padStart(2,'0');
                var m = date.getMinutes().toString().padStart(2,'0');
                var s = date.getSeconds().toString().padStart(2,'0');
                $this.text(h+':'+m+':'+s);
            }
            update();
            setInterval(update,1000);
        });
    };
})(jQuery);
