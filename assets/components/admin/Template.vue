<template>
    <div class="h-100">
        <transition name="fade" enter-active-class="animated fadeIn">
            <v-container v-if="view == 'template'" fluid>
                <v-btn class="mb-3" outlined x-small fab :dark="darkmode" @click="gotoList">
                    <v-icon x-small>fal fa-arrow-left</v-icon>
                </v-btn>
                <v-form>
                    <div v-if="template.id == 1" class="form-group">
                        <label>{{translations.header || 'Header'}}</label>
                        <v-row fluid>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-header" id="template-header-1" value="standard" v-model="template.settings.header">
                                    <label class="form-check-label" for="template-header-1">
                                        {{translations.standard || 'Standard'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-header" id="template-header-2" value="top" v-model="template.settings.header">
                                    <label class="form-check-label" for="template-header-2">
                                        {{translations.top_fixed || 'Top Fixed'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-header" id="template-header-3" value="centered" v-model="template.settings.header">
                                    <label class="form-check-label" for="template-header-3">
                                        {{translations.centered || 'Centered'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-header" id="template-header-4" value="overlay" v-model="template.settings.header">
                                    <label class="form-check-label" for="template-header-4">
                                        {{translations.overlay || 'Overlay'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-header" id="template-header-5" value="none" v-model="template.settings.header">
                                    <label class="form-check-label" for="template-header-5">
                                        {{translations.none || 'None'}}
                                    </label>
                                </div>
                            </div>
                        </v-row>
                    </div>
                    <div v-if="template.id == 1" class="form-group">
                        <label>{{translations.footer || 'Footer'}}</label>
                        <div class="row">
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-footer" id="template-footer-1" value="standard" v-model="template.settings.footer">
                                    <label class="form-check-label" for="template-footer-1">
                                        {{translations.standard || 'Standard'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-footer" id="template-footer-2" value="bottom" v-model="template.settings.footer">
                                    <label class="form-check-label" for="template-footer-2">
                                        {{translations.bottom_fixed || 'Bottom Fixed'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-footer" id="template-footer-3" value="centered" v-model="template.settings.footer">
                                    <label class="form-check-label" for="template-footer-3">
                                        {{translations.centered || 'Centered'}}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-2">
                                <div class="template-header-img"></div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template-footer" id="template-footer-4" value="none" v-model="template.settings.footer">
                                    <label class="form-check-label" for="template-footer-4">
                                        {{translations.none || 'None'}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>{{translations.custom_css || 'Custom CSS'}}</label>
                        <codemirror v-model="template.customCss" :options="cmCssOptions"></codemirror>
                    </div>
                    <div class="mb-3">
                        <label>{{translations.custom_javascript || 'Custom Javascript'}}</label>
                        <codemirror v-model="template.customJs" :options="cmJsOptions"></codemirror>
                    </div>
                    <v-btn color="primary" @click="save">{{translations.submit}}</v-btn>
                </v-form>
            </v-container>
        </transition>
        <transition name="fade" enter-active-class="animated fadeIn">
            <v-container v-if="view == 'list'">
                <div class="row mt-5 pt-3">
                    <div v-for="item in templates" class="col-md-4 mb-3">
                        <v-card class="mx-auto" :dark="darkmode">
                            <v-img
                                class="white--text align-end"
                                height="200px"
                                :src="item.image != null ? '/' + item.image.filePath + item.image.fileName : '/img/img-placeholder.png'"
                            >
                                <v-card-title>{{item.name}}</v-card-title>
                            </v-img>
                            <v-card-text>
                                <p>
                                    {{item.description}}
                                </p>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn
                                    block
                                    color="secondary"
                                    @click="getTemplate"
                                    :data-id="item.id"
                                >
                                    {{translations['edit_template'] || edit_template}}
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </div>
                </div>
            </v-container>
        </transition>
    </div>
</template>

<script>
    import { codemirror } from 'vue-codemirror';

    import 'codemirror/lib/codemirror.css'
    import 'codemirror/theme/base16-light.css'

    export default {
        name: 'Template',
        components: {
            codemirror
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                view: '',
                template: {
                    settings: {}
                },
                templates: [],
                data: {},ons: {
                    tabSize: 4,
                    theme: 'base16-light',
                    mode: 'text/css',
                    lineNumbers: true,
                },
                cmJsOptions: {
                    tabSize: 4,
                    theme: 'base16-light',
                    mode: 'text/javascript',
                    lineNumbers: true,
                }
            };
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            },
            darkmode () {
                return this.$store.state.darkmode;
            },
        },
        created() {
            this.getTemplates();
        },
        methods: {
            getTemplates() {
                this.$axios.get('/api/v1/template/list/', {headers: this.headers})
                    .then(response => {
                        this.templates = response.data.data;
                        this.view = 'list';
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            getTemplate(event) {
                var id = event.target.dataset.id;
                this.$axios.get('/api/v1/template/get/'+id+'/', {headers: this.headers})
                    .then(response => {
                        this.template = response.data.data;
                        if (this.template.settings == '')  this.template.settings = {};
                        this.view = 'template';
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            save() {
                var id = event.target.dataset.id;
                this.$axios.put('/api/v1/template/update/'+this.template.id+'/', this.template, {headers: this.headers})
                    .then(response => {
                        var result = response.data;
                        if (result.success) this.$store.commit('setAlert', {type: 'success', message: translations[result.message] || result.message, autohide: true});
                        else this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                    })
                    .catch(e => {
                        this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                    });
            },
            gotoList: function(event) {
                this.view = 'list';
                this.template = {};
            }
        }
    };
</script>

<style lang="scss" scoped>

    .container {

        height: 100%;


        .card {
            text-align: left;
        }
    }

    .template-header-img {
        height: 200px;
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        background-image: url(/img/img-placeholder.png);
    }

    .template-img {
        width: 100%;
        height: 200px;
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
    }

</style>
