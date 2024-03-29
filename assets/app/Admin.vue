<template>
    <v-app id="admin-app" v-bind:class="{ authenticated: authenticated }">
        <div v-if="!initialised" class="init">
            <i class="fas fa-circle-notch fa-spin"></i>
            Loading...
        </div>
        <login v-else-if="!authenticated"></login>
        <v-navigation-drawer v-if="initialised && authenticated" permanent color="transparent" :dark="darkmode" app>
            <div class="text-center mb-3">
                <div class="dt-user-profile-picture mb-3"></div>
                <div class="dt-user-profile-name mb-3"></div>
            </div>
            <v-list dense nav>
                <template v-for="route in menu">
                    <v-list-group
                        v-if="route.submenu && checkPermission(route.route_name)"
                        :key="route.route_name"
                        link
                    >
                        <template v-slot:activator>
                            <v-list-item-icon>
                                <v-icon small v-text="route.icon"></v-icon>
                            </v-list-item-icon>
                            <v-list-item-title v-text="translations[route.label] || route.label"></v-list-item-title>
                        </template>
                        <v-list-item
                            v-for="subroute in route.submenu"
                            v-if="checkPermission(subroute.route_name)"
                            :key="subroute.route_name"
                            link
                            router v-bind:to="{name: locale + '_' + subroute.route_name}"
                        >
                            <v-list-item-icon>
                                <v-icon></v-icon>
                            </v-list-item-icon>
                            <v-list-item-title v-text="translations[subroute.label] || subroute.label"></v-list-item-title>
                        </v-list-item>
                    </v-list-group>
                    <v-list-item
                        v-else-if="checkPermission(route.route_name)"
                        :key="route.route_name"
                        link
                        router v-bind:to="{name: locale + '_' + route.route_name}"
                    >
                        <v-list-item-icon>
                            <v-icon small v-text="route.icon"></v-icon>
                        </v-list-item-icon>
                        <v-list-item-title v-text="translations[route.label] || route.label"></v-list-item-title>
                    </v-list-item>
                </template>
            </v-list>
        </v-navigation-drawer>
        <v-main v-if="initialised && authenticated">
            <router-view :key="$route.fullPath"></router-view>
            <div class="admin-functions">
                <v-menu transition="slide-y-transition" bottom offset-x>
                    <template v-slot:activator="{ on }">
                        <v-btn outlined x-small fab :dark="darkmode" v-on="on">
                            <v-icon x-small>fal fa-globe</v-icon>
                        </v-btn>
                    </template>
                    <v-list>
                        <v-list-item
                            v-for="locale in locales"
                            :key="locale.locale"
                            @click="setLocale(locale.locale)"
                        >
                            <v-list-item-title>{{ locale.name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
                <v-btn outlined x-small fab :dark="darkmode" @click="setDarkMode">
                    <v-icon x-small>fas fa-adjust</v-icon>
                </v-btn>
                <v-btn outlined x-small fab :dark="darkmode" @click="setFullscreen">
                    <v-icon v-if="fullscreen" x-small>fas fa-compress</v-icon>
                    <v-icon v-else x-small>fas fa-expand-wide</v-icon>
                </v-btn>
                <v-btn outlined x-small fab :dark="darkmode" router v-bind:to="{name: locale + '_admin_profile'}">
                    <v-icon x-small>fal fa-user</v-icon>
                </v-btn>
                <v-btn outlined x-small fab :dark="darkmode" @click="logout">
                    <v-icon x-small>fal fa-sign-out-alt</v-icon>
                </v-btn>
            </div>
        </v-main>
        <v-dialog/>
        <div class="dt-alerts">
            <transition-group name="fade" enter-active-class="animated bounceInUp" leave-active-class="animated fadeOut">
                <v-alert v-for="alert in alerts" :type="alert.type" :key="alert.id">
                    {{alert.message}}
                    <v-btn fab text x-small class="close" color="white" data-dismiss="alert" aria-label="Close" @click="removeAlert" :data-key="alert.id">
                        <v-icon>fal fa-times</v-icon>
                    </v-btn>
                </v-alert>
            </transition-group>
        </div>
    </v-app>
</template>

<script>
    import Login from '../components/admin/Login';


    export default {
        name: 'Admin',
        components: {
            Login
        },
        data() {
            return {
                init: this.$store.state.init,
                site: {},
                block: {},
                page_title: 'Title'
            };
        },
        computed: {
            authenticated () {
                return this.$store.state.authenticated;
            },
            initialised () {
                return this.$store.state.init;
            },
            locales () {
                return this.$store.state.locales;
            },
            locale () {
                return this.$store.state.locale;
            },
            locale_id () {
                return this.$store.state.locale_id;
            },
            translations () {
                return this.$store.state.translations;
            },
            alerts () {
                return this.$store.state.alerts;
            },
            darkmode () {
                return this.$store.state.darkmode;
            },
            fullscreen () {
                return this.$store.state.fullscreen;
            },
            menu () {
                return this.$store.state.menu;
            },
            permissions () {
                return this.$store.state.permissions;
            },
        },
        created() {
            this.getLocales();
            this.checkUser();

            var self = this;

            this.$axios.interceptors.response.use((response) => {
                return response;
            }, function (error) {
                if (error.response.status === 401 || error.response.status === 403) {
                    self.$store.commit('authenticate', false);
                    if (self.$cookies.isKey('admintoken')) self.$cookies.remove('admintoken');
                    if (self.$cookies.isKey('email')) self.$cookies.remove('email');
                    self.$router.push('/' + self.locale + '/admin/login/');
                }
                return Promise.reject(error.response);
            });
        },
        methods: {
            logout() {
                let headers= {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                };
                this.$axios.get('/api/v1/logout/', {headers: headers})
                    .then(response => {
                        this.$store.commit('authenticate', false);
                        this.$cookies.remove('admintoken');
                        this.$cookies.remove('email');
                        this.$router.push('/' + this.locale + '/admin/login/');
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            checkUser: function() {
                if (this.$cookies.isKey('admintoken')) {
                    this.$store.commit('authenticate', true);
                    this.$parent.getRoutes();
                } else {
                    if (this.$router.currentRoute.path != '/' + this.$store.state.locale + '/admin/login/') this.$router.push('/' + this.$store.state.locale + '/admin/login/');
                }
                if (this.$cookies.isKey('darkmode')) {
                    document.body.classList.add('darkmode');
                    this.$store.commit('setDarkmode', true);
                }
                if (this.$cookies.isKey('disableFullscreen')) {
                    document.body.classList.remove('fullscreen');
                    this.$store.commit('setFullscreen', false);
                }
            },
            checkPermission(route_name) {

                for (var i = 0; i < this.permissions.length; i++) {

                    if (this.permissions[i].route_name == route_name) return true;

                }

                return false;
            },
            getLocales: function() {
                this.$store.commit('setLocale', document.body.dataset.locale);

                var params = {
                    filter: 'active=1'
                }

                this.$axios.post('/api/v1/locale/list/', params)
                    .then(response => {
                        var locales = response.data.data;
                        this.$store.commit('setLocales', locales);
                        for (var i = 0; i < locales.length; i++) {
                            if (this.$store.state.locale == locales[i]['locale']) {
                                this.$store.commit('setLocaleId', locales[i]['id']);
                                this.$store.commit('setLocaleName', locales[i]['name']);
                            }
                            if (locales[i]['default']) this.$store.commit('setDefaultLocaleId', locales[i]['id']);
                        }
                        this.$store.commit('setInit', true);
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            getTranslations: function(locale) {
                this.$axios.get('/api/v1/translation/locale/'+locale+'/')
                    .then(response => {
                        var translations = response.data.data;
                        this.$store.commit('setTranslations', translations);
                        this.$parent.getRoutes();
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            setLocale: function(locale) {
                for (var i = 0; i < this.locales.length; i++) {
                    if (locale == this.locales[i]['locale']) {
                        this.$store.commit('setLocale', this.locales[i]['locale']);
                        this.$store.commit('setLocaleId', this.locales[i]['id']);
                    }
                }
                this.getTranslations(locale);
            },
            setDarkMode: function(event) {

                var body = document.body;

                if (this.$store.state.darkmode == false) {
                    body.classList.add('darkmode');
                    this.$store.commit('setDarkmode', true);
                    this.$cookies.set('darkmode', 1);
                } else {
                    body.classList.remove('darkmode');
                    this.$store.commit('setDarkmode', false);
                    this.$cookies.remove('darkmode');
                }
            },
            setFullscreen: function(event) {

                var body = document.body;

                if (this.$store.state.fullscreen == false) {
                    body.classList.add('fullscreen');
                    this.$store.commit('setFullscreen', true);
                    this.$cookies.remove('disableFullscreen');
                } else {
                    body.classList.remove('fullscreen');
                    this.$store.commit('setFullscreen', false);
                    this.$cookies.set('disableFullscreen', 1);
                }
            },
            removeAlert: function(event) {
                this.$store.commit('removeAlert', event.target.dataset.key);
            }
        }
    };
</script>


<style lang="scss">
    .dt-alerts {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        margin-bottom: 0;
        display: inline-block;
        z-index: 9999;
        min-width: 280px;

        .v-alert {

            position: relative;

            .close {
                position: absolute;
                top: 1px;
                right: 1px;
                font-size: 1rem;
            }
        }
    }


        .v-list-item__icon {
            text-align: center;
            margin-right: 1rem !important;

            i {
                width: 24px;
            }
        }
</style>
