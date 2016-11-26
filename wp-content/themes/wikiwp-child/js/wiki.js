jQuery(document).ready(function ($)
{
    $('table').tablesorter(
    {
        dateFormat : "dd mmm yyyy"

    });
    $('.nav-container .categories ul').first().makeCollapsible();

    addDelayedCSS('#comment_ifr', 500);

    // Add stylesheet to iframes (comments)
    $('.comment-reply-link, .cancel-comment-reply-link').click(function()
    {
        addDelayedCSS('#comment_ifr', 100);
    })
});

function addDelayedCSS(selector, delay) 
{
    setTimeout(function() 
    {
        jQuery(selector).contents().find("head").append(jQuery("<link/>", 
        { rel: "stylesheet", href: window.glob.style_url, type: "text/css" }));        
    }, delay);
}

// Custom jQuery plugins
(function( $ ) {
 
 	// Converts a <ul> or <ol> to a collapsible list. Only works for one level of depth (for now).
    $.fn.makeCollapsible = function() {
        this.each(function() 
        {
        	var $main = $(this)
        		speed = 200;

        	$main.find('li').not('.current-cat, .current-cat-ancestor').has('ul').addClass('collapsible').hoverIntent(function()
        	{
        		$(this).addClass('active').children('ul').stop(true, true).slideDown(speed);
        	}, function()
        	{
        		$(this).removeClass('active').children('ul').stop(true, true).slideUp(speed);
        	});
        });
 
        return this;
    };

}( jQuery ));