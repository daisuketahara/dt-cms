<template>
    <div id="admin-app">
        <div v-if="!initialised" class="init">
            <i class="fas fa-circle-notch fa-spin"></i>
            Loading...
        </div>
        <login v-else-if="!authenticated"></login>
        <div v-else class="admin-container">
            <transition name="fade" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                <div v-if="authenticated" class="admin-sidebar">
                    <div class="text-center mb-3 mt-4">
                        <div class="im-user-profile-picture mb-3"></div>
                        <div class="im-user-profile-name mb-3"></div>
                        <button class="btn btn-secondary btn-sm" href="#">{{translations.my_account}}</button>
                        <button class="btn btn-secondary btn-sm" v-on:click="logout">{{translations.logout}}</button>
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
            </transition>
            <transition name="fade" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                <main v-if="authenticated" id="admin-content" class="admin-content">
                    <router-view :key="$route.fullPath"></router-view>
                </main>
            </transition>
        </div>
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
                block: {}
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
                    "Authorization" : "Bearer " + this.$store.state.apikey
                };
                axios.get('/api/v1/logout/', {headers: headers})
                    .then(response => {
                        this.$store.commit('authenticate', false);
                        this.$store.commit('setApiKey', false);
                        this.$store.commit('setEmail', false);
                        localStorage.removeItem('user-email');
                        localStorage.removeItem('user-token');
                        this.$router.push('/' + this.locale + '/admin/login/');
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            checkUser: function() {
                if (this.$store.state.email != '' && this.$store.state.token != '') {
                    this.$store.commit('authenticate', true);
                    this.$parent.getRoutes();
                } else {
                    this.$router.push('/' + this.$store.state.locale + '/admin/login/');
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
            }
        }
    };
</script>


<style lang="scss" scoped>

    html, body {
        width: 100%;
        background-color: rgb(252,252,252);
    }

    body {
        min-height: 100vh;
        padding-top: 2vh;
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#cedce7+0,596a72+100;Grey+3D+%231 */
        background: #cedce7; /* Old browsers */
        background: -moz-linear-gradient(-45deg, #cedce7 0%, #596a72 100%); /* FF3.6-15 */
        background: -webkit-linear-gradient(-45deg, #cedce7 0%,#596a72 100%); /* Chrome10-25,Safari5.1-6 */
        background: linear-gradient(135deg, #cedce7 0%,#596a72 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cedce7', endColorstr='#596a72',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
    }

    #admin-app {
        text-align: center;
    }

    .init {

        background-color: white;
        background-color: rgba(255,255,255,0.9);
        border-radius: 0.15rem;
        -webkit-border-radius: 0.15rem;
        -moz-border-radius: 0.15rem;
        display: inline-block;
        padding: 2rem 4rem;
        margin: 0 auto;
        margin-top: 40vh;
        font-size: 2rem;
    }

    .admin-container {
        position: absolute;
        top: 2vh;
        left: 5%;
        width: 90%;
        min-height: 96vh;
        margin: 0 auto;
    }

    .admin-sidebar {
        float: left;
        min-height: 96vh;
        width: 260px;
        padding-bottom: 30px;
        z-index: 11;
        position: relative;
        border-radius: 0.15rem;
        -webkit-border-radius: 0.15rem;
        -moz-border-radius: 0.15rem;
        -webkit-box-shadow: 0px 4px 17px -4px rgba(102,102,102,1);
        -moz-box-shadow: 0px 4px 17px -4px rgba(102,102,102,1);
        box-shadow: 0px 4px 17px -4px rgba(102,102,102,1);
        color: white;

        div {
            z-index: 99;
            position: relative;
        }

        .admin-logo {
            position: relative;
            z-index: 2;

            width: 100%;
            overflow: hidden;
            color: #fff;

            a {
                display: block;
                width: 100%;
                height: 70px;
                color: #fff;
                font-size: 24px;
                line-height: 70px;
                padding-left: 0;
                text-align: center;
            }
            a:hover {
                text-decoration: none;
            }

            img {
                max-height: 100%;
                max-width: 100%;
                margin-left: 0 auto;
            }
        }
    }

    .admin-sidebar:hover {
        z-index: 14 !important;
    }

    .admin-sidebar::before,
    .admin-sidebar::after {
        display: block;
        content: '';
        z-index: 1;
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
    }
    .admin-sidebar::after {
        background-color: rgba(40,40,40,0.83);
    }
    .admin-sidebar::before {
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        overflow: hidden;
    }

    .admin-content {
        min-height: 96vh;
        position: relative;
        z-index: 11;
        background: rgba(255,255,255,0.95);
        padding: 15px;
        padding-top: 20px;
        margin-left: 275px;
        -webkit-border-radius: 0.1rem;
        -moz-border-radius: 0.1rem;
        border-radius: 0.2rem;
        text-align: left;

        h1 {
            font-size: 1.8rem;
            letter-spacing: 0.1rem;
        }

        label {
            margin-bottom: 0;
        }
    }


    .im-user-profile-picture {
        width: 140px;
        height: 140px;
        margin: 0 auto;
        border-radius: 80px;
        background-color: white;
        border: 2px solid white;
        background-image: url(/img/im-logo-2018.png);
        background-position: center center;
        background-repeat: no-repeat;
        background-size: cover;
    }
    .im-user-profile-name {
        letter-spacing: 0.1rem;
        text-transform: uppercase;
    }

    .admin-content {
        position: relative;
        border-radius: 0.15rem;
        -webkit-box-shadow: 0px 4px 17px -4px rgba(102,102,102,1);
        -moz-box-shadow: 0px 4px 17px -4px rgba(102,102,102,1);
        box-shadow: 0px 4px 17px -4px rgba(102,102,102,1);
    }
    .admin-content * {
        z-index: 4;
        position: relative;
    }

    .admin-content::before,
    .admin-content::after {
        display: block;
        content: '';
        z-index: 1;
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
    }
    .admin-content::before,
    .admin-content::after {
        margin-left: -15px;
    }
    .admin-content::after {
        background-color: rgba(255,255,255,0.75);
    }

    .admin-content::before {
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        overflow: hidden;
    }

    .language-switcher {
        text-align: center;
        position: relative;
        z-index: 2;
        margin: 0 auto;
    }

    .v--modal-overlay {
        background: rgba(0, 0, 0, 0.5);
    }

</style>
