// assets/js/app.js

require('../scss/app.scss');
require('../scss/components.scss');
require('animate.css');

require('./simple-cookie-bar.js');
require('./usability.js');
require('./contact-form.js');

require('bootstrap');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
