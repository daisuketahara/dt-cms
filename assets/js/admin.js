// assets/js/admin.js
'use strict';

import '../scss/admin.scss';

import Vue from 'vue';
import router from '../router';
import store from '../store';
import axios from 'axios';
import VuejsDialog from 'vuejs-dialog';
import CKEditor from '@ckeditor/ckeditor5-vue';

import Admin from '../app/Admin';
import Editor from '../components/Editor';
import MenuEditor from '../components/MenuEditor';
import Control from '../components/Control';
import DataManager from '../components/DataManager';
import User from '../components/User';
import FileManager from '../components/FileManager';
import Dashboard from '../components/Dashboard';

Vue.use(CKEditor);
Vue.use(VuejsDialog, {
    html: true,
    okText: translations['confirm'],
    cancelText: translations['cancel'],
    animation: 'bounce'
});

Vue.config.productionTip = false;

new Vue({
    el: '#admin-app',
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
            axios.get('/api/v1/navigation/admin-routes/', { headers: {"Authorization" : "Bearer " + this.$store.state.apikey} })
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

                        if (permissions[i]['component'] == 'Dasboard') newRoute.component = Dashboard;
                        if (permissions[i]['component'] == 'Editor') newRoute.component = Editor;
                        if (permissions[i]['component'] == 'Control') newRoute.component = Control;
                        if (permissions[i]['component'] == 'MenuEditor') newRoute.component = MenuEditor;
                        if (permissions[i]['component'] == 'FileManager') newRoute.component = FileManager;
                        if (permissions[i]['component'] == 'DataManager') newRoute.component = DataManager;
                        if (permissions[i]['component'] == 'User') newRoute.component = User;
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
