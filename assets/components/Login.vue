<template>
    <transition name="fade" enter-active-class="animated flipInX" leave-active-class="animated flipOutX">
        <div v-if="view =='login'" class="login-container">
            <h1>{{translations.signin || 'Sign in'}}</h1>
            <p>{{translations.signin || 'Hello! Sign in and start managing your website.'}}</p>
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
            <form v-on:submit.prevent="login">
                <div class="form-group">
                    <input type="text" id="email" name="email" class="form-control" v-model="email" :placeholder="translations.email || 'Email'">
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" class="form-control" v-model="password" :placeholder="translations.password || 'Password'">
                </div>
                <button type="submit" class="btn btn-lg btn-secondary">{{translations.login}}</button>
            </form>

            <button class="btn btn-link" v-on:click.prevent="gotoForgetPassword">{{translations.forget_password}}</button>
        </div>
        <div v-else-if="view =='forgetPassword'" class="login-container">
            <h1>{{translations.request_new_password || 'Request a new password'}}</h1>
            <p>{{translations.signin || 'Hello! Sign in and start managing your website.'}}</p>
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
            <form v-on:submit.prevent="RequestPassword">
                <div class="form-group">
                    <label for="email">{{translations.email}}</label>
                    <input type="text" id="email" name="email" class="form-control" v-model="email">
                </div>
                <button type="submit" class="btn btn-lg btn-secondary">{{translations.send}}</button>
            </form>
            <button class="btn btn-link" v-on:click.prevent="gotoLogin">{{translations.login}}</button>
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
                                this.$cookies.set('token', data.token);
                                this.$cookies.set('email', this.email);
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
                            this.$cookies.set('token', data.token);
                            this.$cookies.set('email', this.email);
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
        max-width: 700px;
        margin: 0 auto;
        margin-top: 15vh;
        padding: 15px;
        background-color: rgba(255,255,255,0.7);
        border-radius: 0.1rem;
        position: relative;
        text-align: center;
    }
    .login-container * {
        position: relative;
        z-index: 2;
    }
    .login-container h1 {
        font-weight: 300;
        margin-top: 5vh;
    }
    .login-container p {
        font-weight: 300;
        margin-bottom: 5vh;
    }
    .login-container form {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        margin-bottom: 5vh;
    }
    .login-container input {
        background-color: rgba(0,0,0,0.05);
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
