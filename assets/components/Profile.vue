<template>
    <v-container>
        <h1>{{translations.edit_profile || 'Edit profile'}}</h1>
        <v-form>
            <div class="row mb-4">
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.firstname" :label="translations.firstname || 'Firstname'" :rules="[rules.required]" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.lastname" :label="translations.lastname || 'Lastname'" :rules="[rules.required]" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.email" :label="translations.email || 'Email'" :rules="[rules.required]" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0" style="position:relative; z-index:2;">
                    <div class="form-group">
                        <label class="form-control-label ten required" for="user-phone">{{translations.phone || 'Phone'}}</label>
                        <vue-tel-input v-model="user.phone"></vue-tel-input>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.companyName" :label="translations.company_name || 'Company name'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.website" :label="translations.website || 'Website'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.vatNumber" :label="translations.vat_number || 'VAT Number'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.registrationNumber" :label="translations.registration_number || 'Registration Number'" :dark="darkmode"></v-text-field>
                </div>
            </div>
            <v-row v-if="user.id > 0" class="mb-2">
                <v-col cols="12">
                    <v-switch
                        v-model="changePassword"
                        :label="translations.change_password || 'Change password'"
                        :dark="darkmode"
                    ></v-switch>
                </v-col>
            </v-row>
            <transition name="fade" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                <div v-if="user.id == 0 || changePassword" class="row mb-4">
                    <div class="col-md-6 py-0">
                        <v-text-field
                            v-model="user.password"
                            :append-icon="passwordShow ? 'fal fa-eye' : 'fal fa-eye-slash'"
                            :type="passwordShow ? 'text' : 'password'"
                            :rules="[rules.required]"
                            :label="translations.password || 'Password'"
                            :dark="darkmode"
                            @click:append="passwordShow = !passwordShow"
                        ></v-text-field>
                    </div>
                    <div class="col-md-6 py-0">
                        <v-text-field
                            v-model="user.password"
                            :append-icon="passwordShow ? 'fal fa-eye' : 'fal fa-eye-slash'"
                            :type="passwordShow ? 'text' : 'password'"
                            :rules="[rules.required]"
                            :label="translations.confirm_password || 'Confirm password'"
                            :dark="darkmode"
                            @click:append="passwordShow = !passwordShow"
                        ></v-text-field>
                    </div>
                </div>
            </transition>
            <h4>{{translations.address || 'Address'}}</h4>
            <div class="row">
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.address1" :label="translations.address || 'Address'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.address2" :label="translations.address_2 || 'Address 2'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.zipcode" :label="translations.zipcode || 'Zipcode'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.city" :label="translations.city || 'City'" :dark="darkmode"></v-text-field>
                </div>
                <div class="col-md-6 py-0">
                    <v-text-field v-model="user.information.country" :label="translations.country || 'Country'" :dark="darkmode"></v-text-field>
                </div>
            </div>
            <v-row class="mb-2">
                <v-col cols="12">
                    <v-switch
                        v-model="billingDifferent"
                        :label="translations.billing_address_different || 'Billing address different'"
                        :dark="darkmode"
                    ></v-switch>
                </v-col>
            </v-row>
            <transition name="fade" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
                <div v-if="billingDifferent" class="mb-4">
                    <h4>{{translations.billing_address || 'Billing address'}}</h4>
                    <div class="row">
                        <div class="col-md-6 py-0">
                            <v-text-field v-model="user.information.billingAddress1" :label="translations.address || 'Address'" :dark="darkmode"></v-text-field>
                        </div>
                        <div class="col-md-6 py-0">
                            <v-text-field v-model="user.information.billingAddress2" :label="translations.address_2 || 'Address 2'" :dark="darkmode"></v-text-field>
                        </div>
                        <div class="col-md-6 py-0">
                            <v-text-field v-model="user.information.billingZipcode" :label="translations.zipcode || 'zipcode'" :dark="darkmode"></v-text-field>
                        </div>
                        <div class="col-md-6 py-0">
                            <v-text-field v-model="user.information.billingCity" :label="translations.city || 'City'" :dark="darkmode"></v-text-field>
                        </div>
                        <div class="col-md-6 py-0">
                            <v-text-field v-model="user.information.billingCountry" :label="translations.country || 'Country'" :dark="darkmode"></v-text-field>
                        </div>
                    </div>
                </div>
            </transition>
            <v-btn color="primary" :dark="darkmode" @click="update">{{translations.save || 'Save'}}</v-btn>
        </v-form>
    </v-container>
</template>

<script>

    export default {

        name: "Profile",
        components: {
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                user: {
                    information: {}
                },
                passwordShow: false,
                changePassword: false,
                billingDifferent: false,
                rules: {
                    required: value => !!value || 'Required.',
                    min: v => v.length >= 8 || 'Min 8 characters',
                    emailMatch: () => ('The email and password you entered don\'t match'),
                },
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
            this.get();
        },
        methods: {
            get: function() {
                this.$axios.get('/api/v1/user/get-profile/', {headers: this.headers})
                .then(response => {
                    this.user = JSON.parse(response.data)['data'];

                    if (
                        (this.user.information.billingAddress1 !== '' && this.user.information.address1 != this.user.information.billingAddress1) ||
                        (this.user.information.billingAddress2 !== '' && this.user.information.address2 != this.user.information.billingAddress2) ||
                        (this.user.information.billingZipcode !== '' && this.user.information.zipcode != this.user.information.billingZipcode) ||
                        (this.user.information.billingCity !== '' && this.user.information.city != this.user.information.billingCity) ||
                        (this.user.information.billingCountry !== '' && this.user.information.country != this.user.information.billingCountry)
                    ) this.billingDifferent = true;
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    console.log(e);
                });
            },
            update: function() {

                if (!this.billingDifferent) {
                    this.user.information.billingAddress1 = '';
                    this.user.information.billingAddress2 = '';
                    this.user.information.billingZipcode = '';
                    this.user.information.billingCity = '';
                    this.user.information.billingCountry = '';
                }

                let url = '/api/v1/user/save-profile/';

                this.$axios.put(url, this.user, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) {
                            this.$store.commit('setAlert', {type: 'success', message: translations.saved || 'Data saved', autohide: true});
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                        }
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
