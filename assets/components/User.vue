<template>
    <transition name="fade" enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutDown">
        <form v-on:submit.prevent="updateUser">
            <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-secondary px-3" v-on:click.prevent="changeTab" data-tab="account">{{translations.account || 'Account'}}</button>
                <button type="button" class="btn btn-secondary px-3" v-on:click.prevent="changeTab" data-tab="information">{{translations.personal_information || 'Personal information'}}</button>
                <button type="button" class="btn btn-secondary px-3" v-on:click.prevent="changeTab" data-tab="note">{{translations.notes || 'Notes'}}</button>
                <button type="button" class="btn btn-secondary px-3" v-on:click.prevent="changeTab" data-tab="permission">{{translations.permissions || 'Permissions'}}</button>
            </div>
            <div v-if="tab=='account'">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label required" for="user-firstname">{{translations.firstname || 'Firstname'}}</label>
                            <input id="user-firstname" name="user-firstname" required="required" class="form-control" v-model="user.firstname" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label required" for="user-lastname">{{translations.lastname || 'Lastname'}}</label>
                            <input id="user-lastname" name="user-lastname" required="required" class="form-control" v-model="user.lastname" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label required" for="user-email">{{translations.email || 'Email'}}</label>
                            <input id="user-email" name="user-email" required="required" class="form-control" v-model="user.email" type="email">
                        </div>
                    </div>
                    <div class="col-md-6" style="position:relative; z-index:2;">
                        <div class="form-group">
                            <label class="form-control-label ten required" for="user-phone">{{translations.phone || 'Phone'}}</label>
                            <vue-tel-input v-model="user.phone"></vue-tel-input>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="user-password">{{translations.password || 'Password'}}</label>
                            <input id="user-password" name="user-password" class="form-control" type="password" v-model="user.password">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="user-password">{{translations.confirm_password || 'Comfirm password'}}</label>
                            <input id="user-password-confirm" name="user-password-confirm" class="form-control" type="password" v-model="user.password">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="user-locale">{{translations.language || 'Language'}}</label>
                            <select name="user-locale" class="user-locale form-control" v-model="user.locale.id">
                                <option v-for="item in locales" :value="item.id">{{item.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <h4>{{translations.user_roles || 'User roles'}}</h4>
                    <div class="row">
                        <div v-for="role in roles" class="col-md-2">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input :id="'form-role-'+role.id" name="form_role[]" class="form-check-input" :value="role.id" v-model="user.roles[role.id]" type="checkbox">
                                    {{ role.name }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <h4>{{translations.account_settings || 'Account settings'}}</h4>
                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input id="user-email-confirmed" name="user-email-confirmed" class="form-check-input" v-model="user.emailConfirmed" type="checkbox">
                            {{translations.email_confirmed || 'Email confirmed'}}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input id="user-phone-confirmed" name="user-phone-confirmed" class="form-check-input" v-model="user.phoneConfirmed" type="checkbox">
                            {{translations.phone_confirmed || 'Phone confirmed'}}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input id="active" name="active" class="form-check-input" v-model="user.active" type="checkbox">
                            {{translations.active || 'Active'}}
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" id="save-account" name="save" class="btn-secondary btn">{{translations.save || 'Save'}}</button>
                </div>
            </div>
            <div v-if="tab=='information'">
                <h4>{{translations.company_information || 'Company information'}}</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_company_name">{{translations.company_name || 'Company name'}}</label>
                            <input id="form_company_name" name="form_company_name" class="form-control" v-model="user.information.companyName" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_website">{{translations.website || 'Website'}}</label>
                            <input id="form_website" name="form_website" class="form-control" v-model="user.information.website" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_vat_number">{{translations.vat_number || 'VAT Number'}}</label>
                            <input id="form_vat_number" name="form_vat_number" class="form-control" v-model="user.information.vatNumber" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_registration_number">{{translations.registration_number || 'Registration Number'}}</label>
                            <input id="form_registration_number" name="form_registration_number" class="form-control" v-model="user.information.registrationNumber" type="text">
                        </div>
                    </div>
                </div>
                <h4>{{translations.email || 'Email'}}</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_mail_address_1">{{translations.address || 'Address'}}</label>
                            <input id="form_mail_address_1" name="form_mail_address_1" class="form-control" v-model="user.information.address1" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_mail_address_2">{{translations.address_2 || 'Address 2'}}</label>
                            <input id="form_mail_address_2" name="form_mail_address_2" class="form-control" v-model="user.information.address2" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_mail_zipcode">{{translations.zipcode || 'Zipcode'}}</label>
                            <input id="form_mail_zipcode" name="form_mail_zipcode" class="form-control" v-model="user.information.zipcode" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label ten" for="form_mail_city">{{translations.city || 'City'}}</label>
                            <input id="form_mail_city" name="form_mail_city" class="form-control" v-model="user.information.city" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_mail_country">{{translations.country || 'Country'}}</label>
                            <input id="form_mail_country" name="form_mail_country" class="form-control" type="text" v-model="user.information.mailCountry">
                        </div>
                    </div>
                </div>
                <h4>{{translations.billing_address || 'Billing address'}}</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_billing_address_1">{{translations.address || 'Address'}}</label>
                            <input id="form_billing_address_1" name="form_billing_address_1" class="form-control" v-model="user.information.billingAddress1" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_billing_address_2">{{translations.address_2 || 'Address 2'}}</label>
                            <input id="form_billing_address_2" name="form_billing_address_2" class="form-control" v-model="user.information.billingAddress2" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_billing_zipcode">{{translations.zipcode || 'Zipcode'}}</label>
                            <input id="form_billing_zipcode" name="form_billing_zipcode" class="form-control" v-model="user.information.billingZipcode" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label ten" for="form_billing_city">{{translations.city || 'City'}}</label>
                            <input id="form_billing_city" name="form_billing_city" class="form-control" v-model="user.information.billingCity" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label" for="form_billing_country">{{translations.country || 'Country'}}</label>
                            <input id="form_billing_country" name="form_billing_country" class="form-control" v-model="user.information.billingCountry" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" id="save-information" name="save" class="btn-secondary btn">{{translations.save || 'Save'}}</button>
                </div>
            </div>
            <div v-if="tab=='note'">
                <div class="form-group">
                    <label for="form_note">{{translations.notes || 'Notes'}}</label>
                    <textarea name="form_note" class="form-control" rows="30" :placeholder="translations.write_a_note || 'Write a note'" v-model="user.note.note"></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" id="save-note" name="save" class="btn-secondary btn">{{translations.save || 'Save'}}</button>
                </div>
            </div>
            <div v-if="tab=='permission'">

                <div v-for="permission in permissions" class="form-group">
                    <h4>
                        {{translations[permission.label] || permission.label}}
                        <button class="btn btn-sm btn-link" v-on:click.prevent="toggleCheckboxes" data-status="0">{{translations.select_all}}</button>
                    </h4>
                    <div class="row">
                        <div v-for="(description, index) in permission.options" class="col-sm-6 col-md-4 col-lg-3">
                            <div class="checkbox">
                                <label :for="'permission-'+index">
                                    <input type="checkbox" :id="'permission-'+index" :name="'permission-'+index" :value="index" v-model="user.permissions[index]">
                                    {{description}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" id="save-permission" name="save" class="btn-secondary btn">{{translations.save || 'Save'}}</button>
                </div>
            </div>
        </form>
    </transition>
</template>

<script>

    export default {
        name: "User",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('token')
                },
                user: {
                    'information': {},
                    'roles': {},
                    'permissions': {},
                    'locale': {},
                },
                roles: [],
                permissions: [],
                tab: 'account'
            }
        },
        computed: {
            locales () {
                return this.$store.state.locales;
            },
            translations () {
                return this.$store.state.translations;
            }
        },
        created() {
            this.getRoles();
        },
        methods: {
            getUser: function() {
                this.$axios.get('/api/v1/user/get/' + this.$attrs.id + '/', {headers: this.headers})
                .then(response => {
                    this.user = JSON.parse(response.data)['data'];

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
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            },
            updateUser: function() {
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
