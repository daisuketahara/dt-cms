<template>
    <div id="admin-app">
        <div v-if="!initialised" class="init">
            <i class="fas fa-circle-notch fa-spin"></i>
            Loading...
        </div>
        <login v-else-if="!authenticated"></login>
        <transition name="fadeIn" enter-active-class="animated fadeIn">
            <div v-if="initialised && authenticated" class="admin-container d-flex">
                <div v-if="authenticated" class="admin-sidebar">
                    <div class="text-center mb-3">
                        <div class="im-user-profile-picture mb-3"></div>
                        <div class="im-user-profile-name mb-3"></div>
                    </div>
                    <navbar></navbar>
                    <ul class="language-switcher list-inline">
                        <li v-for="locale in locales" class="list-inline-item">
                            <button class="btn btn-sm btn-link" v-on:click="setLocale" :data-locale="locale.locale">
                                <img class="img-fluid" :src="'/img/flags/' + locale.lcid + '.png'" :alt="locale.name" :data-locale="locale.locale">
                            </button>
                        </li>
                    </ul>
                </div>
                <transition name="fade" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                    <main v-if="authenticated" id="admin-content" class="admin-content flex-grow-1">
                        <router-view :key="$route.fullPath"></router-view>
                        <div class="admin-functions">
                            <button class="btn btn-light btn-sm mt-1" v-on:click.prevent="setViewMode"><i class="fas fa-adjust"></i></button>
                            <button class="btn btn-light btn-sm mt-1" href="#"><i class="fal fa-user"></i> <span>{{translations.my_account || 'My account'}}</span></button>
                            <button class="btn btn-light btn-sm mt-1 mr-2" v-on:click="logout"><i class="fal fa-sign-out-alt"></i> <span>{{translations.logout || 'Logout'}}</span></button>
                        </div>
                    </main>
                </transition>
            </div>
        </transition>
        <v-dialog/>
    </div>
</template>

<script>
    import axios from 'axios';

    import Login from '../components/Login';
    import Navbar from '../components/Navbar';

    export default {
        name: 'Admin',
        components: {
            Login,
            Navbar
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
            }
        },
        created() {
            this.getLocales();
            this.checkUser();
        },
        methods: {
            logout() {
                let headers= {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('token')
                };
                axios.get('/api/v1/logout/', {headers: headers})
                    .then(response => {
                        this.$store.commit('authenticate', false);
                        this.$cookies.remove('token');
                        this.$cookies.remove('email');
                        this.$router.push('/' + this.locale + '/admin/login/');
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            checkUser: function() {
                if (this.$cookies.isKey('token')) {
                    this.$store.commit('authenticate', true);
                    this.$parent.getRoutes();
                } else {
                    this.$router.push('/' + this.$store.state.locale + '/admin/login/');
                }
                if (this.$cookies.isKey('darkmode')) {
                    document.body.classList.add('darkmode');
                    this.$store.commit('setDarkmode', 1);
                }
            },
            getLocales: function() {
                this.$store.commit('setLocale', document.body.dataset.locale);

                axios.get('/api/v1/locale/list/')
                    .then(response => {
                        var locales = JSON.parse(response.data)['data'];
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
                        console.log(e);
                        this.errors.push(e)
                    });
            },
            getTranslations: function(locale) {
                axios.get('/api/v1/translation/locale/'+locale+'/')
                    .then(response => {
                        var translations = JSON.parse(response.data)['data'];
                        this.$store.commit('setTranslations', translations);
                        this.$parent.getRoutes();
                    })
                    .catch(e => {
                        console.log(e);
                        this.errors.push(e)
                    });
            },
            setLocale: function(event) {
                var selected = event.target.dataset.locale;
                var locales = this.$store.state.locales;
                for (var i = 0; i < locales.length; i++) {
                    if (selected == locales[i]['locale']) {
                        this.$store.commit('setLocale', locales[i]['locale']);
                        this.$store.commit('setLocaleId', locales[i]['id']);
                    }
                }
                this.getTranslations(selected);
            },
            setAlert: function(text, type) {
                var self = this;
                this.alert = {text: text, type: type};
                setTimeout(function() { self.alert = {}; }, 5000);
            },
            setViewMode: function(event) {

                var body = document.body;

                if (this.$store.state.darkmode == 0) {
                    body.classList.add('darkmode');
                    this.$store.commit('setDarkmode', 1);
                    this.$cookies.set('darkmode', 1);
                } else {
                    body.classList.remove('darkmode');
                    this.$store.commit('setDarkmode', 0);
                    this.$cookies.remove('darkmode');
                }
            }
        }
    };
</script>


<style lang="scss" scoped>

</style>
