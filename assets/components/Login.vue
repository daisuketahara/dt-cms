<template>
    <transition name="fade" enter-active-class="animated flipInX" leave-active-class="animated flipOutX">
        <div class="login-container">
            <div class="site-logo">{{site.logo}}</div>
            {{block.login_notification}}
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{translations[alert.text]}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{translations[alert.text]}}
                </div>
            </transition>
            <form v-if="view =='login'" v-on:submit.prevent="login">
                <div class="form-group">
                    <label for="email">{{translations.email}}</label>
                    <input type="text" id="email" name="email" class="form-control" v-model="email">
                </div>
                <div class="form-group">
                    <label for="password">{{translations.password}}</label>
                    <input type="password" id="password" name="password" class="form-control" v-model="password">
                </div>
                <button class="btn btn-link" v-on:click.prevent="gotoForgetPassword">{{translations.forget_password}}</button>
                <button type="submit" class="btn btn-primary w-100">{{translations.login}}</button>
            </form>
            <form v-else-if="view =='forgetPassword'" v-on:submit.prevent="RequestPassword">
                <div class="form-group">
                    <label for="email">{{translations.email}}</label>
                    <input type="text" id="email" name="email" class="form-control" v-model="email">
                </div>
                <button class="btn btn-link" v-on:click.prevent="gotoLogin">{{translations.login}}</button>
                <button type="submit" class="btn btn-primary w-100">{{translations.send}}</button>
            </form>
            <ul class="language-switcher list-inline">
                <li v-for="locale in locales" class="list-inline-item">
                    <button class="btn btn-sm btn-link" v-on:click="setLocale" :data-locale="locale.locale">
                        <img class="img-fluid" :src="'/img/flags/' + locale.lcid + '.png'" :alt="locale.name" :data-locale="locale.locale">
                    </button>
                </li>
            </ul>
        </div>
    </transition>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'Login',
        data() {
            return {
                view: 'login',
                email: '',
                password: '',
                site: {},
                block: {},
                alert: {}
            }
        },
        computed: {
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
        methods: {
            login() {

                if(this.email != "" && this.password != "") {

                    let params = {};
                    params.email = this.email;
                    params.password = this.password;

                    axios.post('/api/v1/gettoken/', params)
                        .then(response => {
                            var data = response.data;
                            if (data.success == true) {
                                this.$router.push('/' + this.locale + '/admin/');
                                this.$store.commit('authenticate', true);
                                this.$store.commit('setApiKey', data.token);
                                localStorage.setItem('user-token', data.token);
                                this.$store.commit('setEmail', this.email);
                                localStorage.setItem('user-email', this.email);
                                this.$parent.$parent.getRoutes();
                            } else {
                                this.setAlert('login_failed', 'error');
                            }
                        })
                        .catch(e => {
                            this.setAlert(e, 'error');
                        });
                } else {
                    this.setAlert('email_password_required', 'error');
                }

            },
            gotoLogin: function(event) {
                this.view = 'login';
            },
            gotoForgetPassword: function(event) {
                this.view = 'forgetPassword';
            },
            RequestPassword: function(event) {

                let params = {};
                params.email = this.email;
                params.password = this.password;

                axios.post('/api/v1/gettoken/', params)
                    .then(response => {
                        var data = response.data;
                        if (data.success == true) {
                            this.$router.push('/' + this.locale + '/admin/');
                            this.$store.commit('authenticate', true);
                            this.$store.commit('setApiKey', data.token);
                            this.$store.commit('setEmail', this.email);
                            this.$parent.$parent.getRoutes();
                        } else {
                            this.setAlert('login_failed', 'error');
                        }
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });



            },
            getLocales: function() {
                this.$store.commit('setLocale', document.body.dataset.locale);

                axios.get('/api/v1/locale/list/')
                    .then(response => {
                        var locales = JSON.parse(response.data)['data'];
                        this.$store.commit('setLocales', locales);
                        for (var i = 0; i < locales.length; i++) {
                            if (this.$store.state.locale == locales[i]['locale']) this.$store.commit('setLocaleId', locales[i]['id']);
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
    }
</script>

<style scoped>
    .login-container {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        margin-top: 15%;
        padding: 15px;
        background-color: rgba(255,255,255,0.7);
        border-radius: 0.1rem;
        position: relative;
        text-align: left;
    }
    .login-container::before,
    .login-container::after {
        display: block;
        content: '';
        z-index: 1;
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
        margin-left: -15px;
    }
    .login-container::after {
        background-color: rgba(255,255,255,0.75);
    }

    .login-container::before {
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        overflow: hidden;
    }
    .login-container > div,
    .login-container > form {
        z-index: 4;
        position: relative;
    }
    .site-logo {
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    .site-logo img {
        max-width: 200px !important;
        max-height: 90px !important;
    }

    .language-switcher {
        position: relative;
        z-index: 2;
    }
</style>
