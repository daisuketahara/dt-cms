<template>
    <v-navigation-drawer permanent color="transparent" :dark="darkmode">
        <v-list dense nav>
            <v-list-group
                v-for="route in menu"
                v-if="checkPermission(route.route_name)"
                v-bind:key="route.route_name"
                link
                router v-bind:to="{name: locale + '_' + route.route_name}"
            >
                <template v-slot:activator>
                    <v-list-item-icon>
                        <v-icon small v-text="route.icon"></v-icon>
                    </v-list-item-icon>
                    <v-list-item-title v-text="translations[route.label] || route.label"></v-list-item-title>
                </template>
                <v-list-item
                    v-for="subroute in route.submenu"
                    v-if="checkPermission(subroute.route_name)"
                    :key="subroute.route_name"
                    link
                    router v-bind:to="{name: locale + '_' + subroute.route_name}"
                >
                    <v-list-item-title v-text="translations[subroute.label] || subroute.label"></v-list-item-title>
                    <v-list-item-icon>
                        <v-icon v-text="subroute.icon"></v-icon>
                    </v-list-item-icon>
                </v-list-item>
            </v-list-group>
        </v-list>
    </v-navigation-drawer>
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
            darkmode () {
                return this.$store.state.darkmode;
            },
        },
        methods: {
            checkPermission(route_name) {

                for (var i = 0; i < this.permissions.length; i++) {

                    if (this.permissions[i].route_name == route_name) return true;

                }

                return false;
            }

        }
    };
</script>

<style lang="scss" scoped>


</style>
