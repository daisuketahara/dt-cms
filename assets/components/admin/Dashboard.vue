<template>
    <v-container>

    </v-container>
</template>

<script>

    export default {

        name: "Dashboard",
        components: {
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                payment_methods: [],
                selected_method: '',
            }
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
            darkmode () {
                return this.$store.state.darkmode;
            }
        },
        created() {
        },
        methods: {
            loadGADashboard: function() {

            },
        }
    }
</script>

<style scoped>

</style>
