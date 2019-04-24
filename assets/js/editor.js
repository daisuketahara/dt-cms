'use strict';

import Vue from 'vue';
import VuejsDialog from 'vuejs-dialog';
import CKEditor from '@ckeditor/ckeditor5-vue';
import VueHighlightJS from 'vue-highlightjs';
import Editor from '../components/Editor';

import 'vuejs-dialog/dist/vuejs-dialog.min.css';
import '../scss/editor.scss';

Vue.use(VuejsDialog, {
    html: true,
    okText: translations['confirm_delete'],
    cancelText: translations['cancel'],
    animation: 'bounce'
});

Vue.use(CKEditor);
Vue.use(VueHighlightJS);

new Vue({
    el: '#admin-content',
    components: {
        'editor' : Editor,
    }
}).$mount('#admin-content');
