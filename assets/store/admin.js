
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
        permissions: [],
        darkmode: false,
        fullscreen: false,
        alerts: {},
        settings: {}
    },
    getters: {
        getAlerts: state => {
            return state.alerts;
        },
        getSetting: state => key => {
            return state.settings[key];
        }
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
        setDarkmode (state, darkmode) {
            state.darkmode = darkmode;
        },
        setFullscreen (state, fullscreen) {
            state.fullscreen = fullscreen;
        },
        setAlert (state, alert) {

            if (typeof alert === 'string') alert = {
                message: alert,
                type: 'info'
            };

            var alert_id = 'alert_' + Date.now();
            alert.id = alert_id;

            if (typeof alert.autohide != typeof undefined && alert.autohide)
                setTimeout(function() { Vue.delete(state.alerts, alert_id) }, 10000);

            Vue.set(state.alerts, alert_id, alert);

        },
        removeAlert (state, key) {

            Vue.delete(state.alerts, key);

        },
        storeSetting (state, settings) {
            var oldSettings = state.settings;
            state.settings = {oldSettings, settings}
        },
    }
});
