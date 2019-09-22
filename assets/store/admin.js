
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        init: false,
        authenticated: false,
        translations: translations,
        locales: [],
        locale: '',
        locale_id: 0,
        locale_name: '',
        default_locale: '',
        default_locale_id: 0,
        menu: [],
        permissions: []
    },
    mutations: {
        setInit (state, init) {
            state.init = true;
        },
        authenticate (state, status) {
            state.authenticated = status;
        },
        setMenu (state, menu) {
            state.menu = menu;
        },
        setPermissions (state, permissions) {
            state.permissions = permissions;
        },
        setLocales (state, locales) {
            state.locales = locales;
        },
        setLocale (state, locale) {
            state.locale = locale;
        },
        setLocaleId (state, locale_id) {
            state.locale_id = locale_id;
        },
        setLocaleName (state, locale_name) {
            state.locale_name = locale_name;
        },
        setDefaultLocale (state, default_locale) {
            state.default_locale = default_locale;
        },
        setDefaultLocaleId (state, default_locale_id) {
            state.default_locale_id = default_locale_id;
        },
        setTranslations (state, translations) {
            state.translations = translations;
        },
    }
});
