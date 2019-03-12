jQuery(document).ready(function() {

    if(jQuery('#contact-form').length){

        var locale = jQuery('#contact-form').data('locale');

    	jQuery.ajax({
    		url: '/' + locale + '/ajax/contact/getform/',
    		type: 'POST',
    		data: {
    			locale: locale
    		},
    		dataType: 'html',
    		success: function(html) {
                jQuery('#contact-form').html(html);
            }
        });

        jQuery(document).on('submit', '#contact-form form', function(e) {

            e.preventDefault();
            jQuery('#contact-form [type=submit]').attr('disabled', 'disabled');

            var phone = jQuery('#contact-phone').val();
            var email = jQuery('#contact-email').val();
            var message = jQuery('#contact-message').val();

        	jQuery.ajax({
        		url: '/' + locale + '/ajax/contact/post/',
        		type: 'POST',
        		data: {
        			locale: locale,
        			phone: phone,
        			email: email,
        			message: message,
        		},
        		dataType: 'json',
        		success: function(json) {

                    if (json.result == 'valid') {
                        jQuery('#contact-form').html(json.html);
                    } else {
                        jQuery('#contact-form').prepend(json.html);
                        jQuery('#contact-form [type=submit]').removeAttr("disabled");
                    }
                }
            });
        });
    }
});
