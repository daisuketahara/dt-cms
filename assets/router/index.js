import Vue from 'vue';
import Router from 'vue-router';

import NotFound from '../components/error-pages/NotFound';
import Page from '../components/Page';

Vue.use(Router);

export default new Router({
    mode: 'history',
    linkActiveClass: 'active',
    routes: [
        {
            path: '*',
            name: 'NotFound',
            component: NotFound
        }
    ]
});
