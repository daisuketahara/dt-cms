<template>
    <v-container>

        <v-card :dark="darkmode">
            <v-card-text>
                <v-radio-group v-model="selected_method">
                    <v-radio v-for="method in payment_methods" :key="method.id">
                        <template slot="label">
                            <v-img :src="method.image2x" width="40" max-width="40" class="mr-3"></v-img>
                            {{ method.description }}
                        </template>
                    </v-radio>
                </v-radio-group>
            </v-card-text>
        </v-card>




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
            this.getPaymentMethods();
        },
        methods: {
            loadGADashboard: function() {

            },
            getPaymentMethods: function() {

                this.$axios.get('/api/v1/payment/get-methods/', {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) this.payment_methods = result.data;
                        else this.$store.commit('setAlert', {type: 'error', message: result.message, autohide: true});
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });

            },
        }
    }
</script>

<style scoped>

</style>
