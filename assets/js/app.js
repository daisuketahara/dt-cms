// assets/js/admin.js
'use strict';

import '../scss/app.scss';

import Vue from 'vue';
import router from '../router';
import store from '../store';
import axios from 'axios';
import VModal from 'vue-js-modal'

import App from '../app/App';

Vue.use(VModal, { dialog: true });

Vue.config.productionTip = false;

new Vue({
    el: '#app',
    store,
    router,
    components: {
        App
    },
    template: '<App/>',
    created() {

    },
    methods: {
        getRoutes() {
            axios.get('/api/v1/app-routes/')
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

                        if (permissions[i]['component'] == 'Page') newRoute.component = Page;
                        if (permissions[i]['props'] != '') newRoute.props = JSON.parse(permissions[i]['props']);

                        var checkExist = this.$router.resolve({path: newRoute.path});
                        if (checkExist.resolved.name == 'NotFound') this.$router.addRoutes([newRoute]);
                    }

                    var pages = JSON.parse(response.data)['pages'];
                    this.$store.commit('setPages', pages);

                    for (var i = 0; i < pages.length; i++) {
                        var newRoute = {
                            path: pages[i]['route'],
                            name: pages[i]['route_name'],
                            pathToRegexpOptions: { strict: true }
                        }

                        if (pages[i]['component'] == 'Page') newRoute.component = Page;
                        if (pages[i]['props'] != '') {
                            var props = JSON.parse(pages[i]['props']);
                            props.locale = pages[i]['locale'];
                            newRoute.props = props;
                        }

                        var checkExist = this.$router.resolve({path: newRoute.path});
                        if (checkExist.resolved.name == 'NotFound') this.$router.addRoutes([newRoute]);
                    }

                    var currentRoute = this.$route.path;
                    this.$router.push('/');
                    this.$router.push(currentRoute);
            })
            .catch(err => console.log(err))
        }
    }
});
