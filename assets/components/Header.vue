<template>
    <header>
        <div v-if="true">
            <div id="logo">
                <a href="/">
                <img src="/intermention-logo-2018.png" class="img-fluid" alt="InterMention">
                </a>
            </div>
            <div id="menu-button">
                <button v-bind:class="{ 'is-active': nav_open, 'hamburger': true, 'hamburger--elastic': true }" type="button" aria-label="Menu" aria-controls="navigation" v-on:click="toggleNav">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
                <div class="menu-text">Menu</div>
            </div>
            <modal id="header-nav" name="header-nav" width="80%" height="90%">
                <nav id="navigation">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6 align-middle">
                                <span class="align-helper"></span>
                                <navbar></navbar>
                            </div>
                            <div class="col-lg-6">
                                <span class="align-helper"></span>
                                <div id="navigation-functions" class="d-inline-block align-middle">
                                    <h3>{{translations.contact_details || 'Contactdetails'}}</h3>
                                    <ul id="contact-info" class="list-unstyled">
                    					<li><i class="fas fa-phone"></i> +31 (0)85 0600 700</li>
                    					<li><i class="fab fa-whatsapp"></i> +31 (0)6 2114 6320</li>
                    					<li><i class="fas fa-at"></i> <a href="mailto:info@intermention.com">info@intermention.com</a></li>
                    				</ul>
                                    <h3>{{translations.switch_language || 'Switch language'}}</h3>
                                    <ul class="language-switcher list-inline">
                                        <li v-for="locale in locales" class="list-inline-item">
                                            <button class="btn btn-sm btn-link" v-on:click="setLocale" :data-locale="locale.locale">
                                                <img class="img-fluid" :src="'/img/flags/' + locale.lcid + '.png'" :alt="locale.name" :data-locale="locale.locale">
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </modal>
        </div>
    </header>
</template>

<script>
    import axios from 'axios';

    import Navbar from '../components/Navbar';

    export default {
        name: 'Header',
        components: {
            Navbar
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                site: {},
                block: {},
                header: '',
                footer: '',
                nav_open: false
            };
        },
        computed: {
            locale () {
                return this.$store.state.locale;
            },
            locale_id () {
                return this.$store.state.locale_id;
            },
            locale_name () {
                return this.$store.state.locale_name;
            },
            locales () {
                return this.$store.state.locales;
            },
            translations () {
                return this.$store.state.translations;
            },
            menu () {
                return this.$store.state.menu;
            },
        },
        created() {
        },
        methods: {
            toggleNav() {
                this.nav_open = !this.nav_open;
                if (this.nav_open) this.$modal.show('header-nav');
                else this.$modal.hide('header-nav');
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
#logo {
    position: fixed;
    top: 0.3rem;
    left: 0.3rem;
    z-index: 9999;
    background: repeating-linear-gradient(
      -55deg,
      rgba(20,20,20, 0.8),
      rgba(20,20,20, 0.8),
      rgba(0,0,0,0.8) 2px,
      rgba(0,0,0,0.8) 4px
    );
    width: 70px;
    height: 70px;
    border-radius: 62px;
    text-align: center;
    line-height: 64px;
    border: 2px solid white;
    transition: opacity 0.5s ease-in-out;
    -moz-transition: opacity 0.5s ease-in-out;
    -webkit-transition: opacity 0.5s ease-in-out;
}

#logo img {
    max-width: 46px;
    margin: 0 auto;
}

@media screen and (min-width: 768px) {
    #logo {
        width: 90px;
        height: 90px;
        line-height: 80px;
    }

    #logo img {
        max-width: 62px;
        margin: 0 auto;
    }
}

@media screen and (min-width: 992px) {
    #logo {
        width: 124px;
        height: 124px;
        line-height: 118px;
    }

    #logo img {
        max-width: 80px;
        margin: 0 auto;
    }
}

.menu-open #logo {
    background: none;
    border-color: transparent;
}

#menu-button {
    position: fixed;
    top: 0.3rem;
    right: 0.3rem;
    z-index: 9999;
    background: repeating-linear-gradient(
      -55deg,
      rgba(20,20,20, 0.8),
      rgba(20,20,20, 0.8),
      rgba(0,0,0,0.8) 2px,
      rgba(0,0,0,0.8) 4px
    );
    width: 70px;
    height: 70px;
    border-radius: 35px;
    border: 2px solid white;
    text-align: center;
    transition: opacity 0.5s ease-in-out;
    -moz-transition: opacity 0.5s ease-in-out;
    -webkit-transition: opacity 0.5s ease-in-out;

    button {
        margin-top: 0;
    }

    .hamburger-inner,
    .hamburger-inner::before,
    .hamburger-inner::after,
    .hamburger.is-active .hamburger-inner,
    .hamburger.is-active .hamburger-inner::before,
    .hamburger.is-active .hamburger-inner::after {
        background-color: white;
    }

    .menu-text {
        color: white;
        text-align: center;
        font-size: 0.7rem;
        position: relative;
        top: -18px;
        text-transform: uppercase;
    }
}


.menu-open #menu-button {
    background: none;
    border-color: transparent;
}


</style>
