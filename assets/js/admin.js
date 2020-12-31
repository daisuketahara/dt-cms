// assets/js/admin.js
'use strict';

import '../scss/admin.scss';

import 'babel-polyfill';
import Vue from 'vue';
import vuetify from '../plugins/vuetify'
import router from '../router';
import store from '../store/admin.js';
import axios from 'axios';
import VueCookies from 'vue-cookies';
import VModal from 'vue-js-modal';
import VueCodemirror from 'vue-codemirror';
import VTooltip from 'v-tooltip';
import VueTelInputVuetify from 'vue-tel-input-vuetify/lib';
import moment from 'moment';

import Admin from '../app/Admin';
import Page from '../components/admin/Page';
import MenuEditor from '../components/admin/MenuEditor';
import Control from '../components/admin/Control';
import DataManager from '../components/admin/DataManager';
import User from '../components/admin/User';
import FileManager from '../components/admin/FileManager';
import Dashboard from '../components/admin/Dashboard';
import Template from '../components/admin/Template';
import Module from '../components/admin/Module';
import Profile from '../components/Profile';
import Map from '../components/Map';
import Pincode from '../components/parts/Pincode';
import Editor from '../components/parts/Editor';
import Richtext from '../components/parts/Richtext';

Vue.use(VueCodemirror);
Vue.use(VModal, { dialog: true });
Vue.use(VueCookies);
Vue.use(VTooltip);

Vue.use(VueTelInputVuetify, {
  vuetify,
});
Vue.component('filemanager', FileManager);
Vue.component('User', User);
Vue.component('Editor', Editor);
Vue.component('Map', Map);
Vue.component('Pincode', Pincode);
Vue.component('Richtext', Richtext);

Vue.filter('formatDate', function(value) {
    if (value) {
        return moment(value).format('YYYY-MM-DD');
    }
});

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
            this.$axios.get('/api/v1/admin-routes/', { headers: {"X-AUTH-TOKEN" : this.$cookies.get('admintoken')} })
                .then(response => {

                    var menu = response.data.menu;
                    this.$store.commit('setMenu', menu);

                    var permissions = response.data.permissions;
                    this.$store.commit('setPermissions', permissions);

                    for (var i = 0; i < permissions.length; i++) {
                        var newRoute = {
                            path: '/' + this.$store.state.locale + permissions[i]['route'],
                            name: this.$store.state.locale + '_' + permissions[i]['route_name'],
                            pathToRegexpOptions: { strict: true }
                        }

                        if (permissions[i]['component'] == 'Dashboard') newRoute.component = Dashboard;
                        if (permissions[i]['component'] == 'Page') newRoute.component = Page;
                        if (permissions[i]['component'] == 'Control') newRoute.component = Control;
                        if (permissions[i]['component'] == 'MenuEditor') newRoute.component = MenuEditor;
                        if (permissions[i]['component'] == 'FileManager') newRoute.component = FileManager;
                        if (permissions[i]['component'] == 'DataManager') newRoute.component = DataManager;
                        if (permissions[i]['component'] == 'User') newRoute.component = User;
                        if (permissions[i]['component'] == 'Template') newRoute.component = Template;
                        if (permissions[i]['component'] == 'Module') newRoute.component = Module;
                        if (permissions[i]['component'] == 'Profile') newRoute.component = Profile;

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
