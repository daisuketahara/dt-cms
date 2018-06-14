
(function($) {

	var window_height = jQuery(window).height();
	var body_height = jQuery('body').height();
    var scroll = jQuery(window).scrollTop();

    if (scroll == 0) jQuery('body').addClass('position-top');
    else jQuery('body').removeClass('position-top');

    jQuery(window).scroll(function (event) {
        scroll = jQuery(window).scrollTop();
        if (scroll == 0) jQuery('body').addClass('position-top');
        else jQuery('body').removeClass('position-top');
    });

    jQuery('body').on('click', '#site-scroll-down', function() {
		jQuery("html, body").animate({ scrollTop: window_height }, 1000);
	});

    jQuery('body').on('click', '#site-scroll-to-top', function() {
		jQuery("html, body").animate({ scrollTop: 0 }, 0);
	});

    addScrollToTop();
    addScrollDown();

    jQuery(window).resize(function() {
        addScrollToTop();
        addScrollDown();
    });

    function addScrollToTop() {
        if (body_height > window_height) {
            var button = '<a id="site-scroll-to-top" class="pointer">';
            button += '<i class="fas fa-arrow-circle-up"></i>';
            button += '</a>';
            jQuery('body').append(button);
        } else {
            jQuery('#site-scroll-to-top').remove();
        }
    }

    function addScrollDown() {
        if (body_height > window_height) {
            var button = '<a id="site-scroll-down" class="animated infinite pulse pointer">';
            button += '<i class="fas fa-arrow-circle-down"></i>';
            button += '</a>';
            jQuery('body').append(button);
        } else {
            jQuery('#site-scroll-to-top').remove();
        }
    }

}(jQuery));
