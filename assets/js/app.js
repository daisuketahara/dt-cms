// assets/js/admin.js
'use strict';

import '../scss/app.scss';

require('./usability.js');
require('./simple-cookie-bar.js');

import Vue from 'vue';
import axios from 'axios';
//import Datepicker from 'vuejs-datepicker';
import Contact from '../components/Contact';

//Vue.component('datepicker', Datepicker);
Vue.config.productionTip = false;

var contactForm = document.getElementById('im-contact-form');
if (contactForm) {
    new Vue({
        el: '#im-contact-form',
        components: { Contact },
        template: '<Contact/>',
    });
}
