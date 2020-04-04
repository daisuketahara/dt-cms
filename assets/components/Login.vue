<template>
    <transition-group name="fade" enter-active-class="animated flipInX">
        <v-card v-if="view =='login'" class="login-container" key="login" elevation="4">
            <h1>{{translations.signin || 'Sign in'}}</h1>
            <p>{{translations.signin || 'Hello! Sign in and start managing your website.'}}</p>
            <div class="site-logo">{{site.logo}}</div>
            {{block.login_notification}}
            <v-form>
                <v-text-field
                    v-model="email"
                    :rules="[rules.required]"
                    :label="translations.email || 'Email'"
                    @keyup.enter="login"
                ></v-text-field>
                <v-text-field
                    v-model="password"
                    :append-icon="passwordShow ? 'fal fa-eye' : 'fal fa-eye-slash'"
                    :type="passwordShow ? 'text' : 'password'"
                    name="input-10-1"
                    :rules="[rules.required, rules.min]"
                    :label="translations.password || 'Password'"
                    :hint="translations.login_password_hint || 'Enter your password'"
                    @click:append="passwordShow = !passwordShow"
                    @keyup.enter="login"
                ></v-text-field>
                <v-btn color="primary" @click="login">{{translations.login || 'Login'}}</v-btn>
            </v-form>

            <v-btn @click="gotoForgetPassword">{{translations.forget_password}}</v-btn>
        </v-card>
        <v-card v-else-if="view =='forgetPassword'" class="login-container" key="forget-password">
            <h1>{{translations.request_new_password || 'Request a new password'}}</h1>
            <p>{{translations.signin || 'Hello! Sign in and start managing your website.'}}</p>
            <div class="site-logo">{{site.logo}}</div>
            {{block.login_notification}}

            <v-form>
                <v-text-field
                    outlined
                    v-model="email"
                    :rules="[rules.required]"
                    :label="translations.email || 'Email'"
                    @keyup.enter="RequestPassword"
                ></v-text-field>
                <v-btn color="primary"  @click="RequestPassword">{{translations.send}}</v-btn>
            </v-form>
            <v-btn @click="gotoLogin">{{translations.login}}</v-btn>
        </v-card>
    </transition-group>
</template>

<script>

    export default {
        name: 'Login',
        data() {
            return {
                view: 'login',
                email: '',
                password: '',
                passwordShow: false,
                site: {},
                block: {},
                rules: {
                    required: value => !!value || 'Required.',
                    min: v => v.length >= 8 || 'Min 8 characters',
                    emailMatch: () => ('The email and password you entered don\'t match'),
                },
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

                    this.$axios.post('/api/v1/gettoken/', params)
                        .then(response => {
                            var data = response.data;
                            if (data.success == true) {
                                this.$router.push('/' + this.locale + '/admin/');
                                this.$store.commit('authenticate', true);
                                this.$cookies.set('token', data.token);
                                this.$cookies.set('email', this.email);
                                this.$parent.$parent.$parent.getRoutes();
                            } else {
                                this.$store.commit('setAlert', {type: 'error', message: translations.login_failed || "Login failed", autohide: true});
                            }
                        })
                        .catch(e => {
                            this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                        });
                } else {
                    this.$store.commit('setAlert', {type: 'error', message: translations.email_password_required || "Email and password required", autohide: true});
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

                this.$axios.post('/api/v1/gettoken/', params)
                    .then(response => {
                        var data = response.data;
                        if (data.success == true) {
                            this.$router.push('/' + this.locale + '/admin/');
                            this.$store.commit('authenticate', true);
                            this.$cookies.set('token', data.token);
                            this.$cookies.set('email', this.email);
                            this.$parent.$parent.getRoutes();
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations.login_failed || "Login failed", autohide: true});
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });



            },
            getLocales: function() {
                this.$store.commit('setLocale', document.body.dataset.locale);

                this.$axios.get('/api/v1/locale/list/')
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
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            getTranslations: function(locale) {
                this.$axios.get('/api/v1/translation/locale/'+locale+'/')
                    .then(response => {
                        var translations = JSON.parse(response.data)['data'];
                        this.$store.commit('setTranslations', translations);
                        this.$parent.getRoutes();
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
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
