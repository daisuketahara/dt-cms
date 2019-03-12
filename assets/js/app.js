// assets/js/app.js
// ...

require('../scss/app.scss');
require('animate.css');

require('./simple-cookie-bar.js');
require('./usability.js');
require('./contact-form.js');

// var $ = require('jquery');
const $ = require('jquery');
global.$ = global.jQuery = $;
