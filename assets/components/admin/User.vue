<template>
    <transition-group name="fade" enter-active-class="animated fadeIn">
        <v-container v-if="!loaded" key="loading" fill-height>
            <v-row justify="center" align="center">
                <v-col cols="12" align="center" style="padding-top:40vh;">
                    <v-progress-circular
                        :size="50"
                        color="white"
                        indeterminate
                        class="mr-5"
                    ></v-progress-circular>
                    <span class="text-uppercase">
                        {{translations.loading || 'Loading...'}}
                    </span>
                </v-col>
            </v-row>
        </v-container>
        <v-form v-else key="content">

            <v-tabs class="mb-3" color: transparent small :dark="darkmode">
                <v-tab @click="changeTab" data-tab="account">{{translations.account || 'Account'}}</v-tab>
                <v-tab @click="changeTab" data-tab="information">{{translations.personal_information || 'Personal information'}}</v-tab>
                <v-tab @click="changeTab" data-tab="note">{{translations.notes || 'Notes'}}</v-tab>
                <v-tab @click="changeTab" data-tab="permission">{{translations.permissions || 'Permissions'}}</v-tab>
            </v-tabs>
            <div v-if="tab=='account'">
                <div class="row mb-2">
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
                    <div class="col-md-6 py-0">
                        <v-select v-model="user.locale.id" :items="localesOptions" :label="translations.language || 'Language'" :rules="[rules.required]" :dark="darkmode"></v-select>
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
                <div class="form-group mb-4">
                    <h4>{{translations.user_roles || 'User roles'}}</h4>
                    <div class="row">
                        <div v-for="role in roles" class="col-md-3">
                            <v-checkbox
                                v-model="user.roles[role.id]"
                                :label="role.name"
                                :dark="darkmode"
                            ></v-checkbox>
                        </div>
                    </div>
                </div>
                <h4>{{translations.account_settings || 'Account settings'}}</h4>
                <v-switch
                    v-model="user.emailConfirmed"
                    :label="translations.email_confirmed || 'Email confirmed'"
                    color="success"
                    :dark="darkmode"
                ></v-switch>
                <v-switch
                    v-model="user.phoneConfirmed"
                    :label="translations.phone_confirmed || 'Phone confirmed'"
                    color="success"
                    :dark="darkmode"
                ></v-switch>
                <v-switch
                    v-model="user.active"
                    :label="translations.active || 'Active'"
                    color="success"
                    :dark="darkmode"
                ></v-switch>
                <v-btn color="primary" :dark="darkmode" @click="updateUser">{{translations.save || 'Save'}}</v-btn>
            </div>
            <div v-if="tab=='information'">
                <h4>{{translations.company_information || 'Company information'}}</h4>
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
                <v-row v-if="user.id > 0" class="mb-2">
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
                <v-btn color="primary" :dark="darkmode" @click="updateUser">{{translations.save || 'Save'}}</v-btn>
            </div>
            <div v-if="tab=='note'">
                <v-textarea v-model="user.note.note" rows="24" :label="translations.write_a_note || 'Write a note'" :dark="darkmode"></v-textarea>
                <v-btn color="primary" :dark="darkmode">{{translations.save || 'Save'}}</v-btn>
            </div>
            <div v-if="tab=='permission'">

                <div v-for="permission in permissions" class="form-group">
                    <h4>
                        {{translations[permission.label] || permission.label}}
                        <button class="btn btn-sm btn-link" v-on:click.prevent="toggleCheckboxes" data-status="0">{{translations.select_all}}</button>
                    </h4>
                    <div class="row">
                        <div v-for="(description, index) in permission.options" class="col-sm-6 col-md-4 col-lg-3 py-1">
                            <div class="checkbox">
                                <label :for="'permission-'+index">
                                    <input type="checkbox" :id="'permission-'+index" :name="'permission-'+index" :value="index" v-model="user.permissions[index]">
                                    {{description}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <v-btn color="primary" :dark="darkmode" @click="updateUser">{{translations.save || 'Save'}}</v-btn>
            </div>
        </v-form>
    </transition-group>
</template>

<script>

    export default {
        name: "User",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                loaded: false,
                user: {
                    'id': 0,
                    'information': {},
                    'roles': {},
                    'permissions': {},
                    'locale': {},
                },
                roles: [],
                localesOptions: [],
                permissions: [],
                tab: 'account',
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
            locales () {
                return this.$store.state.locales;
            },
            translations () {
                return this.$store.state.translations;
            },
            darkmode () {
                return this.$store.state.darkmode;
            }
        },
        created() {
            this.getRoles();

            for(var i=0, n=this.locales.length;i<n;i++) {
                this.localesOptions.push({
                    text: this.locales[i].name,
                    value: this.locales[i].id,
                });
            }
        },
        methods: {
            getUser: function() {
                this.$axios.get('/api/v1/user/get/' + this.$attrs.id + '/', {headers: this.headers})
                .then(response => {
                    this.user = JSON.parse(response.data)['data'];

                    if (
                        (this.user.information.billingAddress1 !== '' && this.user.information.address1 != this.user.information.billingAddress1) ||
                        (this.user.information.billingAddress2 !== '' && this.user.information.address2 != this.user.information.billingAddress2) ||
                        (this.user.information.billingZipcode !== '' && this.user.information.zipcode != this.user.information.billingZipcode) ||
                        (this.user.information.billingCity !== '' && this.user.information.city != this.user.information.billingCity) ||
                        (this.user.information.billingCountry !== '' && this.user.information.country != this.user.information.billingCountry)
                    ) this.billingDifferent = true;

                    var roles = {};
                    for(var i=0, n=this.user.userRoles.length;i<n;i++) {
                        roles[this.user.userRoles[i].id] = true;
                    }
                    this.user.roles = roles;

                    var permissions = {};
                    for(var i=0, n=this.user.permissions.length;i<n;i++) {
                        permissions[this.user.permissions[i].id] = true;
                    }
                    this.user.permissions = permissions;
                    this.loaded = true;
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            },
            updateUser: function() {

                if (!this.billingDifferent) {
                    this.user.information.billingAddress1 = '';
                    this.user.information.billingAddress2 = '';
                    this.user.information.billingZipcode = '';
                    this.user.information.billingCity = '';
                    this.user.information.billingCountry = '';
                }

                let url = '/api/v1/user/insert/';
                if (this.$attrs.id > 0) url = '/api/v1/user/update/' + this.$attrs.id + '/';

                this.$axios.put(url, this.user, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) {
                            this.$store.commit('setAlert', {type: 'success', message: translations.saved || "Saved", autohide: true});
                        } else {
                            this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                        }
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            getRoles: function() {
                this.$axios.get('/api/v1/user/role/list/', {headers: this.headers})
                .then(response => {
                    this.roles = JSON.parse(response.data)['data'];
                    this.getPermissions();
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            },
            getPermissions: function () {
                this.$axios.get('/api/v1/permission/fields/', {headers: this.headers})
                .then(response => {
                    this.permissions = JSON.parse(response.data)['data'];

                    if (this.$attrs.id != undefined) this.getUser();
                    else this.loaded = true;
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            },
            changeTab: function(event) {
                this.tab = event.target.dataset.tab;
            },
            toggleCheckboxes: function(event) {

                var status = event.target.dataset.status;
                var checkboxes = event.target.parentNode.parentNode.querySelectorAll('input[type="checkbox"]');

                if (status == 0) {
                    event.target.dataset.status = 1;
                    for(var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = true;
                    }
                } else {
                    event.target.dataset.status = 0;
                    for(var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = false;
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>
