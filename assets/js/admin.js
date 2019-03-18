// assets/js/admin.js

require('../scss/admin.scss');
require('animate.css');

require('./list.js');
require('./paginator.js');
require('./table.js');

const $ = require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
