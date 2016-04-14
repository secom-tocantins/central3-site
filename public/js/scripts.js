$(document).ready(function() {

    //set a minimum viewport size
    if ($(window).width() < 600) { $('meta[name=viewport]').attr('content','width=600'); }

    $('#conteudo').scroll4ever({
        trigger:'a#more',
        container:'#noticias > div',
        selector:'.media', // this is the selection made in the source url data
        distance:1000, // distance to the end of window to trigger page load, if avoided, it will not auto trigger
        debug: false, // if you want some messages in console...
        start: function(){ $('#noticias a#more').html('Carregando...');  }, // callback called when a new page load begins, good for loading messages
        complete: function(){}, // callback called when a new page load ends
    });

    $('a[href="'+ window.location.pathname +'"]').addClass('active').parents('span').addClass('active');
});
