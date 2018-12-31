// assets/js/pwa.js

require('../scss/pwa.scss');
require('../vendor/onsen-ui/css/onsenui.min.css');
require('../vendor/onsen-ui/css/onsen-css-components.min.css');
require('animate.css');
import '@fortawesome/fontawesome-free/js/all';

// var $ = require('jquery');
const $ = require('jquery');
global.$ = global.jQuery = $;

var contactForm = require('./contact-form.js');
var setLocale;

var app = {

    // Application Constructor
    initialize: function() {
        if (ons.platform.isIPhoneX()) {
            document.documentElement.setAttribute('onsflag-iphonex-portrait', '');
            document.documentElement.setAttribute('onsflag-iphonex-landscape', '');
        }
    },
    onDeviceReady: function() {

    },
    // Screen functions
    hideSplash: function() {
        setTimeout(function() {
            jQuery('#splash').addClass('flipOutY animated');
            setTimeout(function() {
                jQuery('#splash-container').hide();
            }, 1000);
        }, 1500);
    },
    loadScreen: function(id, effect) {

        if (effect == 'slide') {

        } else {
            jQuery('.page.active').addClass('fadeOut animated-fast');
            setTimeout(function() {
                jQuery('.page.active').removeClass('fadeOut animated-fast').removeClass('active').hide();
                jQuery('#'+id).addClass('active').addClass('fadeIn animated-semi-fast').show();
                setTimeout(function() {
                    jQuery('#'+id).removeClass('fadeIn animated-semi-fast');
                },500);
            },100);
        }
    },
    switchLocale: function(locale) {

        setLocale = locale;

        jQuery('body').find('[data-translation-tag]').each(function() {
            var tag = jQuery(this).data('translation-tag');
            var translation = app.getVariable('trans-' + locale + '-' + tag);
            if (typeof translation !== typeof undefined && translation !== false && translation != '' && translation != null) jQuery(this).text(translation);
        });

    },
    openMenu: function() {

        jQuery('#menu').animate({
            left: 0
        },{
            duration: 400,
            easing: 'swing'
        });

    },
    closeMenu: function() {

        jQuery('#menu').animate({
            left: '-100vw'
        },{
            duration: 200,
            easing: 'swing'
        });

    },
    storeVariable: function(key, value) {
        var storage = window.localStorage;
        storage.setItem(key, value);
    },
    getVariable: function(key) {
        var storage = window.localStorage;
        return storage.getItem(key);
    },
    removeVariable: function(key) {
        var storage = window.localStorage;
        storage.removeItem(key);
    },
    showModal: function(content) {
        if (content.length > 0) jQuery('#modal > div').html(content);
        else jQuery('#modal > div').html('<div style="text-align: center"><p><ons-icon icon="md-spinner" size="28px" spin></ons-icon> Loading...</p></div>');
        var modal = document.querySelector('ons-modal');
        modal.show();
    },
    hideModal: function() {
        var modal = document.querySelector('ons-modal');
        modal.hide();
    },
    validateForm: function(id) {
        var error = false;
        jQuery('#'+id).find('input, textarea').each(function() {
            if (jQuery(this).hasClass('required') && jQuery(this).val() == '') {
                jQuery(this).addClass('error');
                error = true;
            } else {
                jQuery(this).removeClass('error');
            }
        });
        if (error) return false;
        return true;
    },
    resetForms: function(id) {
        //jQuery('input, textarea').val('');
    },
    resetForms: function() {
        //jQuery('input, textarea').val('');
    }
};

jQuery(document).on('click', '#menu-toggle', function() {

    var status = jQuery(this).data('status');

    if (status == 0) {
        app.openMenu();
        jQuery(this).data('status', 1);
    } else {
        app.closeMenu();
        jQuery(this).data('status', 0);
    }

});
