'use strict';

import Vue from 'vue';
import VuejsDialog from 'vuejs-dialog';
import CKEditor from '@ckeditor/ckeditor5-vue';
import Control from '../components/Control';

import 'vuejs-dialog/dist/vuejs-dialog.min.css';
import '../scss/control.scss';

Vue.use(VuejsDialog, {
    html: true,
    okText: translations['confirm_delete'],
    cancelText: translations['cancel'],
    animation: 'bounce'
});

Vue.use( CKEditor );

new Vue({
    el: '#admin-content',
    components: {
        'control' : Control,
    }
}).$mount('#admin-content');
