// assets/js/app.js

require('../scss/admin.scss');
require('../scss/bootstrap-extended.scss');
require('../scss/font-awesome-variables.scss');

require('./list.js');
require('./paginator.js');
require('./table.js');

const $ = require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');
import '@fortawesome/fontawesome-free/js/all'

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
