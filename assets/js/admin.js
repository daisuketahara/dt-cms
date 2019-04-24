// assets/js/admin.js
'use strict';

const $ = require('jquery');
global.$ = global.jQuery = $;

require('../scss/admin.scss');
require('animate.css');
require('bootstrap');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
