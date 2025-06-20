$(function(){
    var allItems = $('.news-item');
    var currentList = allItems;
    var index = 0;

    function showCurrent() {
        allItems.hide().removeClass('active');
        if (currentList.length) {
            currentList.eq(index).show().addClass('active');
        }
    }

    function setList(list) {
        currentList = list;
        index = 0;
        showCurrent();
    }

    $('.news-next').on('click', function(e){
        e.preventDefault();
        if (!currentList.length) return;
        index = (index + 1) % currentList.length;
        showCurrent();
    });

    $('.news-prev').on('click', function(e){
        e.preventDefault();
        if (!currentList.length) return;
        index = (index - 1 + currentList.length) % currentList.length;
        showCurrent();
    });

    $('.news-nav a').on('click', function(e){
        e.preventDefault();
        $('.news-nav a').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');
        if (filter === 'all') {
            setList(allItems);
        } else {
            var filtered = allItems.filter('[data-category="'+filter+'"],[data-author="'+filter+'"]');
            setList(filtered);
        }
    });

    setList(allItems);
});
