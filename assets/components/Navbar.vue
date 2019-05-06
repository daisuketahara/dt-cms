<template>
    <nav class="navbar">
        <ul>
            <li v-for="route in menu" v-if="checkPermission(locale + '_' + route.route_name)">
                <router-link :to="{name: locale + '_' + route.route_name}">
                    <span v-if="route.icon" v-html="route.icon"></span>
                    <span v-if="translations[route.label]" v-html="translations[route.label] || route.label"></span>
                    <span v-else v-html="route.label"></span>
                    <i v-if="route.submenu" class="fas fa-angle-right float-right"></i>
                </router-link>
                <ul v-if="route.submenu">
                    <li v-for="subroute in route.submenu" v-if="checkPermission(locale + '_' + subroute.route_name)">
                        <router-link :to="{name: locale + '_' + subroute.route_name}">
                            <span v-if="subroute.icon" v-html="subroute.icon"></span>
                            <span v-if="translations[subroute.label]" v-html="translations[subroute.label] || subroute.label"></span>
                            <span v-else v-html="subroute.label"></span>
                        </router-link>
                    </li>
                </ul>
            </li>
        </ul>
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

    nav {
        position: relative;
        z-index: 2;
        padding: 0;
        ul {
            position: relative;
            z-index: 2;
            list-style: none;
            width: 260px;
            padding-left: 0;
            li {
                width: 100%;
                text-align: left;
                position: relative;
                border-top: 1px solid #555555;
                a {
                    color: white;
                    display: block;
                    width: 100%;
                    padding: 14px 5px 14px 20px;
                    -webkit-transition: all 0.25s ease-in-out;
                    -moz-transition: all 0.25s ease-in-out;
                    -o-transition: all 0.25s ease-in-out;
                    transition: all 0.25s ease-in-out;
                    font-weight: 300;
                    font-size: 0.8rem;
                    letter-spacing: 0.15rem;
                    span:first-child {
                        display: inline-block;
                        width: 20px;
                        margin-right: 0.5rem;
                        text-align: center;
                        font-size: 1.1rem;
                    }
                    i.fa-angle-right {
                        float: right;
                        position: relative;
                        top: 0.3rem;
                        right: 0.3rem;
                    }
                    .icon {

                    }
                    .label {

                    }
                }
                a:hover {
                    background-color: rgba(255,255,255,0.2);
                    text-decoration: none;
                }
                ul {
                    background-color: rgb(40, 40, 40);
                    display: block;
                    position: absolute;
                    left: 260px;
                    top: 0;
                    padding-left: 0;
                    width: 200px;
                    visibility:hidden;
                    opacity: 0;
                    list-style: none;
                    -webkit-transition: all 0.25s ease-in-out;
                    -moz-transition: all 0.25s ease-in-out;
                    -o-transition: all 0.25s ease-in-out;
                    transition: all 0.25s ease-in-out;

                    a span {
                        font-size: 0.8rem !important;
                    }
                }
            }
            li:hover {

                ul {
                    visibility: visible;
                    opacity: 1;
                    width: 200px;
                    -webkit-transition: all 0.1s ease-in-out;
                    -moz-transition: all 0.1s ease-in-out;
                    -o-transition: all 0.1s ease-in-out;
                    transition: all 0.1s ease-in-out;
                }
            }
        }
    }
</style>
