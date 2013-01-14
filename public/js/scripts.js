$(document).ready(function() {
    $('.modal').fancybox({helpers : {title: {type: 'outside'}}});
    $('#content.noticias div').scroll4ever({
            trigger:'#content.noticias a.next',
            container: '#content.noticias div',
            selector:'#content.noticias div > *', // this is the selection made in the source url data
            distance:100, // distance to the end of window to trigger page load, if avoided, it will not auto trigger
            debug: true, // if you want some messages in console...
            start: function(){}, // callback called when a new page load begins, good for loading messages
            complete: function(){}, // callback called when a new page load ends
    });
});