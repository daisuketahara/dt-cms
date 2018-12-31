// assets/js/app.js

require('../scss/admin.scss');
require('../scss/bootstrap-extended.scss');

require('./list.js');
require('./paginator.js');
require('./table.js');

const $ = require('jquery');
global.$ = global.jQuery = $;

require('animate.css');
require('bootstrap');
import '@fortawesome/fontawesome-free/js/all'

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
