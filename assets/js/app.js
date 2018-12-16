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
    showLoadingModal: function(content) {
        var modal = document.querySelector('ons-modal');
        modal.show();
    },
    hideLoadingModal: function() {
        var modal = document.querySelector('ons-modal');
        modal.hide();
    },
    resetForms: function(id) {
        //jQuery('input, textarea').val('');
    },
    resetForms: function() {
        //jQuery('input, textarea').val('');
    },
    resetTimer: function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            app.loadScreen('hello');
            app.resetForms();
        }, 60000);
    }
};

app.initialize();
app.hideSplash();

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
