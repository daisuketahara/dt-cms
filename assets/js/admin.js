// assets/js/admin.js
'use strict';

import '../scss/admin.scss';

import 'babel-polyfill';
import Vue from 'vue';
import vuetify from '../plugins/vuetify'
import router from '../router';
import store from '../store/admin.js';
import axios from 'axios';
import CKEditor from '@ckeditor/ckeditor5-vue';
import VueCookies from 'vue-cookies';
import VModal from 'vue-js-modal';
import VueCodemirror from 'vue-codemirror';
import VTooltip from 'v-tooltip';
import VueTelInput from 'vue-tel-input';

import Admin from '../app/Admin';
import Editor from '../components/Editor';
import MenuEditor from '../components/MenuEditor';
import Control from '../components/Control';
import DataManager from '../components/DataManager';
import User from '../components/User';
import FileManager from '../components/FileManager';
import Dashboard from '../components/Dashboard';
import Template from '../components/Template';
import Module from '../components/Module';
import Map from '../components/Map';

Vue.use(CKEditor);
Vue.use(VueCodemirror);
Vue.use(VModal, { dialog: true });
Vue.use(VueCookies);
Vue.use(VTooltip);
Vue.component('filemanager', FileManager);
Vue.component('User', User);
Vue.component('vue-tel-input', VueTelInput);
Vue.component('Map', Map);

Vue.filter('formatTimestamp', function(value) {
    if (value) {
        return moment(value*1000).format('YYYY-MM-DD hh:mm');
    }
});
Vue.filter('formatTime', function(minutes) {
    var dHours = 0;
    var dMinutes = minutes;

    while (dMinutes > 59) {
        dMinutes = dMinutes - 60;
        dHours = dHours + 1;
    }

    return dHours + 'h ' + dMinutes + 'm';
});
Vue.filter('formatPrice', function(value) {
    if (value) {
		value = value / 100
		value = value.toFixed(2);
		value = value.toString();
		value = value.replace('.', ',');

		return value;
    }
});

Vue.prototype.$axios = axios;

Vue.config.productionTip = false;

new Vue({
    el: '#admin-app',
    vuetify,
    store,
    router,
    components: {
        Admin
    },
    template: '<Admin/>',
    created() {

    },
    methods: {
        getRoutes() {
            this.$axios.get('/api/v1/admin-routes/', { headers: {"X-AUTH-TOKEN" : this.$cookies.get('token')} })
                .then(response => {

                    var menu = JSON.parse(response.data)['menu'];
                    this.$store.commit('setMenu', menu);

                    var permissions = JSON.parse(response.data)['permissions'];
                    this.$store.commit('setPermissions', permissions);

                    for (var i = 0; i < permissions.length; i++) {
                        var newRoute = {
                            path: '/' + this.$store.state.locale + permissions[i]['route'],
                            name: this.$store.state.locale + '_' + permissions[i]['route_name'],
                            pathToRegexpOptions: { strict: true }
                        }

                        if (permissions[i]['component'] == 'Dashboard') newRoute.component = Dashboard;
                        if (permissions[i]['component'] == 'Editor') newRoute.component = Editor;
                        if (permissions[i]['component'] == 'Control') newRoute.component = Control;
                        if (permissions[i]['component'] == 'MenuEditor') newRoute.component = MenuEditor;
                        if (permissions[i]['component'] == 'FileManager') newRoute.component = FileManager;
                        if (permissions[i]['component'] == 'DataManager') newRoute.component = DataManager;
                        if (permissions[i]['component'] == 'User') newRoute.component = User;
                        if (permissions[i]['component'] == 'Template') newRoute.component = Template;
                        if (permissions[i]['component'] == 'Module') newRoute.component = Module;
                        if (permissions[i]['props'] != '') newRoute.props = JSON.parse(permissions[i]['props']);

                        var checkExist = this.$router.resolve({path: newRoute.path});
                        if (checkExist.resolved.name == 'NotFound') this.$router.addRoutes([newRoute]);
                    }

                    var currentRoute = this.$route.path;
                    this.$router.push('/' + this.locale + '/admin/');
                    this.$router.push(currentRoute);
            })
            .catch(err => console.log(err))
        }
    }
});
