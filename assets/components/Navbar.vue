<template>
    <nav class="navbar navbar-nav">
        <transition-group name="menu" enter-active-class="animated flipInX" tag="ul">
            <li v-for="route in menu" v-if="checkPermission(locale + '_' + route.route_name)" v-bind:key="route.route_name">
                <router-link :to="{name: locale + '_' + route.route_name}">
                    <span v-if="route.icon" v-html="route.icon"></span>
                    <span v-if="route.label" v-html="translations[route.label] || route.label"></span>
                    <i v-if="route.submenu" class="fas fa-angle-right float-right"></i>
                </router-link>
                <ul v-if="route.submenu">
                    <li v-for="subroute in route.submenu" v-if="checkPermission(locale + '_' + subroute.route_name)">
                        <router-link :to="{name: locale + '_' + subroute.route_name}">
                            <span v-if="subroute.icon" v-html="subroute.icon"></span>
                            <span v-if="subroute.label" v-html="translations[subroute.label] || subroute.label"></span>
                        </router-link>
                    </li>
                </ul>
            </li>
        </transition-group>
    </nav>
</template>
<script>
    export default {
        name: 'Navbar',
        data() {
            return {

            };
        },
        computed: {
            menu () {
                return this.$store.state.menu;
            },
            permissions () {
                return this.$store.state.permissions;
            },
            locale () {
                return this.$store.state.locale;
            },
            translations () {
                return this.$store.state.translations;
            },
        },
        methods: {
            checkPermission(route_name) {

                for (var i = 0; i < this.permissions.length; i++) {

                    if (1==1) return true;

                }

                return false;
            }

        }
    };
</script>

<style lang="scss" scoped>

</style>
