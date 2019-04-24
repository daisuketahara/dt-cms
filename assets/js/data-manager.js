'use strict';

import Vue from 'vue';
import VuejsDialog from 'vuejs-dialog';
import CKEditor from '@ckeditor/ckeditor5-vue';
import DataManager from '../components/DataManager';

import 'vuejs-dialog/dist/vuejs-dialog.min.css';
import 'vue-tel-input/dist/vue-tel-input.css';
import '../scss/table.scss';

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
        'datamanager' : DataManager,
    }
}).$mount('#admin-content');
