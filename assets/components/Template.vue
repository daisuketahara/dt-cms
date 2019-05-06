<template>
    <transition name="fade" enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutDown">
        <div v-if="view == 'template'">
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <form v-on:submit.prevent="save">
                <div class="form-group">
                    <label>{{translations.css}}</label>
                    <codemirror v-model="template.customCss" :options="cmCssOptions"></codemirror>
                </div>
                <div class="form-group">
                    <label>{{translations.javascript}}</label>
                    <codemirror v-model="template.customJs" :options="cmJsOptions"></codemirror>
                </div>
                <button class="btn btn-secondary">{{translations.submit}}</button>
                <button class="btn btn-secondary" v-on:click.prevent="compile" :data-id="template.id">{{translations.compile}}</button>
                <button class="btn btn-secondary float-right" v-on:click.prevent="gotoList">{{translations.back_to_list}}</button>
            </form>
        </div>
        <div v-else>
            <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
                <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                    {{alert.text}}
                </div>
                <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                    {{alert.text}}
                </div>
            </transition>
            <div class="row">
                <div v-for="item in templates" class="col-3 mb-3">
                    <div class="card">
                        <div :style="'background-image: url(/' + item.image.filePath + item.image.fileName + ')'" class="template-img"></div>
                        <div class="card-body">
                            <h5 class="card-title">{{item.name}}</h5>
                            <p class="card-text">{{item.description}}</p>
                            <button class="btn btn-secondary" v-on:click="getTemplate" :data-id="item.id">{{translations['edit_template'] || edit_template}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import axios from 'axios';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import { codemirror } from 'vue-codemirror'

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
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                view: 'list',
                template: {},
                templates: [],
                data: {},
                editor: ClassicEditor,
                editorData: {},
                editorConfig: {
                    codeSnippet_theme: 'default',
                    toolbar: []
                },
                cmCssOptions: {
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
                },
                alert: {}
            };
        },
        computed: {
            translations () {
                return this.$store.state.translations;
            },
        },
        created() {
            this.getTemplates();
        },
        methods: {
            getTemplates() {
                axios.get('/api/v1/template/list/', {headers: this.headers})
                    .then(response => {
                        this.templates = JSON.parse(response.data)['data'];
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            getTemplate(event) {
                var id = event.target.dataset.id;
                axios.get('/api/v1/template/get/'+id+'/', {headers: this.headers})
                    .then(response => {
                        this.template = JSON.parse(response.data)['data'];
                        this.view = 'template';
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            save() {
                var id = event.target.dataset.id;
                axios.put('/api/v1/template/update/'+this.template.id+'/', this.template, {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) this.setAlert(result.message, 'success');
                        else this.setAlert(result.message, 'error');
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            compile() {
                var id = event.target.dataset.id;
                axios.get('/api/v1/template/compile/'+id+'/', {headers: this.headers})
                    .then(response => {
                        var result = JSON.parse(response.data);
                        if (result.success) this.setAlert(result.message, 'success');
                        else this.setAlert(result.message, 'error');
                    })
                    .catch(e => {
                        this.setAlert(e, 'error');
                    });
            },
            gotoList: function(event) {
                this.view = 'list';
                this.template = {};
            },
            setAlert: function(text, type) {
                var self = this;
                this.alert = {text: text, type: type};
                setTimeout(function() { self.alert = {}; }, 5000);
            }
        }
    };
</script>

<style lang="scss" scoped>
    .template-img {
        width: 100%;
        height: 200px;
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
    }
</style>
