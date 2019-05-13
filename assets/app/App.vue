<template>
    <div id="app">
        <Header></Header>
        <div v-if="!initialised" class="init">
            <i class="fas fa-circle-notch fa-spin"></i>
            Loading...
        </div>
        <div v-else class="app-container">
            <transition name="fade" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                <main id="admin-content" class="app-content">
                    <router-view :key="$route.fullPath"></router-view>
                </main>
            </transition>
        </div>
        <footer class="container-fluid font-12">
            <Block></Block>
        </footer>
        <v-dialog/>
    </div>
</template>

<script>
    import axios from 'axios';

    import Header from '../components/Header';
    import Block from '../components/Block';
    import Login from '../components/Login';
    import Navbar from '../components/Navbar';

    export default {
        name: 'App',
        components: {
            Header,
            Block,
            Login,
            Navbar
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                init: this.$store.state.init,
                site: {},
                block: {},
                menu: [],
                header: '',
                footer: '',
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
            getPage () {

            }
        },
        created() {
            this.getLocales();
            this.$parent.getRoutes();
        },
        methods: {
            logout() {

                let params = {};
                params.email = this.$store.state.email;
                params.token = this.$store.state.apikey;

                axios.post('/api/v1/logout/', params, {headers: this.headers})
                    .then(response => {
                        this.$router.push('/' + this.locale + '/admin/login/')
                        this.$store.commit('authenticate', false);
                        this.$store.commit('setApiKey', false);
                        this.$store.commit('setEmail', false);
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

#app {
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


.v--modal-overlay {
    background: rgba(0, 0, 0, 0.5);
}
</style>
