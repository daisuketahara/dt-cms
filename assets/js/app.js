// assets/js/app.js
// ...

require('../scss/app.scss');
require('../scss/reviews.scss');
require('../scss/step-blocks.scss');
require('../scss/usability.scss');
require('../scss/bootstrap-extended.scss');
require('../scss/font-awesome-variables.scss');

require('./simple-cookie-bar.js');
require('./usability.js');
require('./contact-form.js');

// var $ = require('jquery');
const $ = require('jquery');
global.$ = global.jQuery = $;


require('bootstrap');
import '@fortawesome/fontawesome-free/js/all';

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
