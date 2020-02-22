<template>
    <div>
        dsad
        <transition name="fadeIn" enter-active-class="animated fadeIn">
            <div class="container">
                <TomTomMap address="Vrouwenzandstraat 115, 1443WJ, Purmerend, The Netherlands"></TomTomMap>


            </div>
        </transition>


    </div>
</template>

<script>

    import TomTomMap from '../components/TomTomMap';


    export default {

        name: "Dashboard",
        components: {
            TomTomMap
        },
        data() {
            return {}
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
            }
        },
        created() {
            this.loadGADashboard();

        },
        methods: {
            loadGADashboard: function() {

            }
        }
    }
</script>

<style scoped>

</style>
