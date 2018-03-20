jQuery(document).ready(function() {

	jQuery(document).on('click', '#admin-theme-set-day', function()
    {
        setTheme(1);
	});

	jQuery(document).on('click', '#admin-theme-set-night', function()
    {
        setTheme(2);
	});

    function setTheme(set)
    {
		if (set == 1)
        {
			jQuery('body').addClass('day-theme').removeClass('night-theme');
			createCookie('admin-theme','0',30);
            jQuery('#admin-theme-set-day').parent().hide();
            jQuery('#admin-theme-set-night').parent().show();
			theme = 1;
		}
        else
        {
			jQuery('body').addClass('night-theme').removeClass('day-theme');
			createCookie('admin-theme','1',30);
            jQuery('#admin-theme-set-night').parent().hide();
            jQuery('#admin-theme-set-day').parent().show();
			theme = 2;
		}
    }

	function setContainerHeight() {

		var window_height = jQuery(window).height();
		var body_height = jQuery('body').height();
		var main_height = jQuery('.admin-content').height();

		jQuery('body').css('min-height', window_height);
		jQuery('.admin-navigation').css('min-height', window_height - 53);
		jQuery('.admin-content').css('min-height', window_height - 53);
	}

	setContainerHeight();

	function createCookie(name, value, days) {
		var expires;
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toGMTString();
		} else {
			expires = "";
		}
		document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/;";
	}
	function readCookie(name) {
	    var nameEQ = escape(name) + "=";
	    var ca = document.cookie.split(';');
	    for (var i = 0; i < ca.length; i++) {
	        var c = ca[i];
	        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
	        if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
	    }
	    return null;
	}
	function eraseCookie(name) {
		document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	}

});
