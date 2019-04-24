<template>
    <form method="post" v-on:submit.prevent="update">
        <div class="row">
            <div class="col-md-8 col-lg-9">
                <nav class="editor-nav">
                    <div class="btn-group mb-3" role="group" aria-label="Editor functions">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{translate_name}}
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button v-for="item in locales" class="dropdown-item" :data-lid="item.id" v-on:click.prevent="setTranslate">{{item.name}}</button>
                            </div>
                        </div>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'meta'}" type="button" v-on:click.prevent="showPanel" data-panel="meta">{{translations.meta_data}}</button>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'settings'}" type="button" v-on:click.prevent="showPanel" data-panel="settings">{{translations.settings}}</button>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'css'}" type="button" v-on:click.prevent="showPanel" data-panel="css">{{translations.custom_css}}</button>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'js'}" type="button" v-on:click.prevent="showPanel" data-panel="js">{{translations.custom_js}}</button>
                    </div>
                    <div class="btn-group mb-3" role="group" aria-label="Editor submit">
                        <button class="btn btn-primary px-5">{{translations.submit}}</button>
                    </div>
                </nav>

                <transition name="slide">
                    <div v-if="panel == 'meta'" class="mb-2">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translations['title_tag']}}</label>
                                    <input type="input" id="page-meta-title" name="page-meta-title" class="form-control" :value="page.meta_title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translations['meta_keywords']}}</label>
                                    <input type="input" id="page-meta-keywords" name="page-meta-keywords" class="form-control" :value="page.meta_keywords">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{translations['meta_description']}}</label>
                                    <input type="input" id="page-meta-description" name="page-meta-description" class="form-control" :value="page.meta_description">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{translations['custom_meta']}}</label>
                            <textarea id="page-meta-custom" name="page-meta-custom" class="form-control" rows="4">{{page.meta_custom}}</textarea>
                        </div>
                    </div>
                    <div v-else-if="panel == 'settings'" class="mb-2">
                        <div class="row">
                            <div class="col-md-auto">
                                <img v-if="page.main_image != ''" :src="page.main_image" alt="..." class="img-thumbnail">
                                <img v-else src="/img/default-300x169.png" alt="" class="img-thumbnail">
                                <a id="" class="btn btn-secondary w-100 margin-top-5" data-toggle="modal" data-target="#im-media-manager">{{translations['add_image']}}</a>
                            </div>
                            <div class="col">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{translations['publish_date']}}</label>
                                            <input type="date" id="page-publish-date" name="page-publish-date" class="form-control" :value="page.publishDate">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{translations['expire_date']}}</label>
                                            <input type="date" id="page-expire-date" name="page-expire-date" class="form-control" :value="page.expireDate">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{translations['page_status']}}</label>
                                            <select id="page-status" name="page-status" class="form-control" v-model="page.pageStatus">
                                                <option value="1">{{translations['publish']}}</option>
                                                <option value="0">{{translations['draft']}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{translations['page_width']}}</label>
                                            <select id="page-width" name="page-width" class="form-control" v-model="page.pageWidth">
                                                <option value="default">{{translations['default']}}</option>
                                                <option value="1140">1140px</option>
                                                <option value="980">980px</option>
                                                <option value="700">700px</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{translations['disable_layout']}}</label>
                                            <select id="disable-layout" name="disable-layout" class="form-control" v-model="page.disableLayout">
                                                <option value="0">{{translations['no']}}</option>
                                                <option value="1">{{translations['yes']}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="page-weight">{{translations['page_weight']}}</label>
                                            <select id="page-weight" name="page-weight" class="form-control" v-model="page.pageWeight">
                                                <option value="10">1.0</option>
                                                <option value="9">0.9</option>
                                                <option value="8">0.8</option>
                                                <option value="7">0.7</option>
                                                <option value="6">0.6</option>
                                                <option value="5">0.5</option>
                                                <option value="4">0.4</option>
                                                <option value="3">0.3</option>
                                                <option value="2">0.2</option>
                                                <option value="1">0.1</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <label>{{translations['grant_access']}}</label>
                                <div class="row">
                                    <div v-for="role in roles" class="col-md-2">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input id="form_role_" name="form_role[]" class="form-check-input" :value="role.id" type="checkbox">
                                                {{ role.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="panel == 'css'" class="mb-2">
                        <textarea class="form-control" rows="6" v-model="page.customCss"></textarea>
                    </div>
                    <div v-if="panel == 'js'" class="mb-2">
                        <textarea class="form-control" rows="6" v-model="page.customJs"></textarea>
                    </div>
                </transition>

                <div class="form-group mb-2">
                    <input type="text" id="page-title" name="page-title" class="form-control" :value="page.pageTitle" :placeholder="translations['enter_title']">
                </div>
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon3">{{domain}}/{{locale}}</span>
                    </div>
                    <input type="text" class="form-control" id="page-route" name="page-route" :value="page.pageRoute" aria-describedby="basic-addon3">
                </div>
                <textarea class="form-control" rows="20" v-model="page.content"></textarea>


            </div>
            <div class="col-md-4 col-lg-3">
            </div>
        </div>
    </form>
</template>

<script>
import axios from 'axios';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    name: "editor",
    data() {
        return {
            panel: '',
            locales: [],
            locale: '',
            locale_id: 0,
            default_locale_id: 0,
            translations: [],
            translate_id: 0,
            translate_name: '',
            page_id: 0,
            page: {},
            roles: [],
            alert: {},
            editor: ClassicEditor, //ClassicEditor,
            editorData: {}, //'<p>Rich-text editor content.</p>',
            editorConfig: {
                'min-height': '500px'
            }
        }
    },
    created() {
        this.translations = translations;
        this.domain = domain;
        this.getRoles();
        this.getLocales();
        if (page_id > 0) {
            this.page_id = page_id;
            this.getPage();
        }
    },
    methods: {
        getLocales: function() {
            this.locale = document.body.dataset.locale;
            axios.get('/api/v1/locale/list/', { headers: {"Authorization" : "Bearer " + apikey} })
            .then(response => {
                this.locales = JSON.parse(response.data)['data'];
                for (var i = 0; i < this.locales.length; i++) {
                    if (this.locale == this.locales[i]['locale']) {
                        this.locale_id = this.locales[i]['id'];
                        this.translate_id = this.locales[i]['id'];
                        this.translate_name = this.locales[i]['name'];
                    }
                    if (this.locales[i]['default']) this.default_locale_id = this.locales[i]['id'];
                }
            })
            .catch(e => {
                this.errors.push(e)
            });
        },
        getRoles: function() {
            axios.get('/api/v1/user/role/list/', { headers: {"Authorization" : "Bearer " + apikey} })
            .then(response => {
                this.roles = JSON.parse(response.data)['data'];
            })
            .catch(e => {
                this.errors.push(e)
            });
        },
        setTranslate: function(event) {
            this.translate_id = parseInt(event.target.dataset.lid);

            for (var i = 0; i < this.locales.length; i++) {
                if (this.translate_id == this.locales[i]['id']) this.translate_name = this.locales[i]['name'];
            }

            this.getPage();
        },
        getPage: function() {

            let url = '/api/v1/page/get/' + this.page_id + '/';

            let headers = {
                'Content-Type': 'application/json;charset=UTF-8',
                "Authorization" : "Bearer " + apikey
            }

            let params = {};
            if (this.translate_id > 0) params['locale'] = this.translate_id;

            axios.post(url, params, { headers: headers })
            .then(response => {
                var result = JSON.parse(response.data);
                if (result.success) {
                    if (result['data'].constructor === {}.constructor) this.page = result['data'];
                    else this.page = {};
                } else {
                    this.setAlert(result.message, 'error');
                }
            })
            .catch(e => {
                this.setAlert(e, 'error');
                this.errors.push(e)
            });
        },
        update: function(event){

            let headers = {
                'Content-Type': 'application/json;charset=UTF-8',
                "Authorization" : "Bearer " + apikey
            }

            let params = {};
            params['id'] = this.page_id;
            params['content'] = this.page.content;
            params['customCss'] = this.page.customCss;
            params['customJs'] = this.page.customJs;
            params['disableLayout'] = this.page.disableLayout;
            params['locale'] = this.translate_id;
            params['mainImage'] = this.page.mainImage;
            params['metaCustom'] = this.page.metaCustom;
            params['metaDescription'] = this.page.metaDescription;
            params['metaKeywords'] = this.page.metaKeywords;
            params['metaTitle'] = this.page.metaTitle;
            params['pageRoute'] = this.page.pageRoute;
            params['pageTitle'] = this.page.pageTitle;
            params['pageWeight'] = this.page.pageWeight;
            params['pageWidth'] = this.page.pageWidth;
            params['pageTitle'] = this.page.pageTitle;
            params['publishDate'] = this.page.publishDate;
            params['expireDate'] = this.page.expireDate;
            params['status'] = this.page.status;

            let url = '/api/v1/page/insert/';
            if (this.page_id > 0) url = '/api/v1/page/update/'+ this.page_id + '/';

            axios.put(url, params, {headers: headers})
                .then(response => {
                    var result = JSON.parse(response.data);

                    if (result.success) {
                        this.page_id = parseInt(result['id']);
                        this.setAlert(translations.saved, 'success');
                    } else {
                        this.setAlert(result.message, 'error');
                    }
                })
                .catch(e => {
                    this.errors.push(e)
                });

        },
        showPanel: function(event) {
            if (this.panel != event.target.dataset.panel) this.panel = event.target.dataset.panel;
            else this.panel = '';
        },
        setAlert: function(text, type) {
            var self = this;
            this.alert = {text: text, type: type};
            setTimeout(function() { self.alert = {}; }, 5000);
        }
    }
}
</script>

<style scoped>

</style>
