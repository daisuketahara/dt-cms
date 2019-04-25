<template>
    <form method="post" v-on:submit.prevent="update">
        <div class="row">
            <div class="col-md-8 col-lg-9">
                <nav class="editor-nav">
                    <div class="btn-group mb-3" role="group" aria-label="Editor functions">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownLocaleButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{translate_name}}
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownLocaleButton">
                                <button v-for="item in locales" class="dropdown-item" :data-lid="item.id" v-on:click.prevent="setTranslate">{{item.name}}</button>
                            </div>
                        </div>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'meta'}" type="button" v-on:click.prevent="showPanel" data-panel="meta">{{translations.meta_data}}</button>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'settings'}" type="button" v-on:click.prevent="showPanel" data-panel="settings">{{translations.settings}}</button>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'css'}" type="button" v-on:click.prevent="showPanel" data-panel="css">{{translations.custom_css}}</button>
                        <button :class="{ 'btn': true, 'btn-secondary': true, ' px-3': true, 'active' : panel == 'js'}" type="button" v-on:click.prevent="showPanel" data-panel="js">{{translations.custom_js}}</button>
                    </div>
                    <div class="btn-group mb-3" role="group" aria-label="Editor select">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownEditorButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{selected_editor_name}}
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownEditorButton">
                                <button class="dropdown-item" data-editor="html" v-on:click.prevent="setEditor">{{translations.html}}</button>
                                <button class="dropdown-item" data-editor="editor" v-on:click.prevent="setEditor">{{translations.editor}}</button>
                                <button class="dropdown-item" data-editor="builder" v-on:click.prevent="setEditor">{{translations.builder}}</button>
                            </div>
                        </div>
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









                <textarea v-if="selected_editor == 'html'" id="page-content" class="form-control mb-2" v-model="page.content" rows="24"></textarea>
                <ckeditor v-if="selected_editor == 'editor'" :editor="editor" v-model="page.content" :config="editorConfig"></ckeditor>
                <div v-if="selected_editor == 'builder'" id="content-editor" class="mt-2">
                    <div v-for="element in construct" class="container-fluid">
                        <div v-if="element.type == 'text_left_image_right'" class="row" v-on:click="activateElement">
                            <div class="col-sm-6 wrap-content" :contenteditable="enableEdit">
                                <h3>{{element.title}}</h3>
                                {{element.text}}
                                <a href="#" class="btn btn-lg btn-secondary" :contenteditable="enableEdit">{{element.cta_button_text}}</a>
                            </div>
                            <div class="col-sm-6 bg-image"></div>
                        </div>
                        <div v-else-if="element.type == 'text_right_image_left'" class="row" v-on:click="activateElement">
                            <div class="col-sm-6 bg-image"></div>
                            <div class="col-sm-6 wrap-content" :contenteditable="enableEdit">
                                <h3>{{element.title}}</h3>
                                {{element.text}}
                                <a href="#" class="btn btn-lg btn-secondary" :contenteditable="enableEdit">{{element.cta_button_text}}</a>
                            </div>
                        </div>
                        <div v-else-if="element.type == 'block'" class="" :contenteditable="enableEdit" v-on:click="activateElement">
                            <h3>{{element.title}}</h3>
                            {{element.text}}
                            <a href="#" class="btn btn-lg btn-secondary" :contenteditable="enableEdit">{{element.cta_button_text}}</a>
                        </div>
                    </div>
                    <div v-if="enableEdit" class="text-center mt-3 py-5">
                        <button class="btn btn-lg btn-secondary" v-on:click.prevent="selectElement"><i class="fas fa-plus"></i> {{translations.add_element}}</button>
                    </div>
                </div>









            </div>
            <div class="col-md-4 col-lg-3">
            </div>
        </div>
        <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
            <div v-if="modal" id="im-editor-modal">
                <div class="im-modal-backdrop">
                    <div class="im-modal-body p-4">
                        <div class="im-modal-close" v-on:click="closeModal"><i class="fas fa-times"></i></div>
                            <div v-if="modal_view == 'elements'" class="row">
                                <div v-for="(element, index) in elements" class="col-sm-6 col-md-4 col-lg-3 mb-4" :data-element="index">
                                    <img :src="element.image" :alt="element.title" class="img-fluid w-100 mb-1">
                                    <h4>
                                        {{element.title}}
                                        <button v-on:click.prevent="addElement" class="btn btn-sm btn-secondary float-right" :data-element="element.type">{{translations.select}}</button>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
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
            editorConfig: {},
            modal: false,
            modal_view: '',
            elements: {},
            construct: [],
            construct_css: '',
            enableEdit: true,
            active_element: '',
            example: {},
            selected_editor_name: 'HTML',
            selected_editor: 'html'
        }
    },
    created() {
        this.translations = translations;
        this.domain = domain;
        this.getRoles();
        this.getLocales();
        this.getExample();
        this.getElements();
        if (page_id > 0) {
            this.page_id = page_id;
            this.getPage();
        }

        var checkSelectedEditor = this.readCookie('selected_editor');
        if (checkSelectedEditor) {
            this.selected_editor = checkSelectedEditor;
            this.selected_editor_name = checkSelectedEditor;
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
        getExample: function() {

            this.example = {
                title: 'Title goes here',
                bg_image: '/img/img-placeholder.png',
                btn_txt: 'Button text',
                text_short: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                text_medium: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugifffat nulla pariatur.',
            };

            this.example.text_long = this.example.text_short + ' ' + this.example.text_short;
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
                    if (result['data'].constructor === {}.constructor) {
                        this.page = result['data'];
                        if (result['data']['construct']) this.construct = JSON.parse(result['data']['construct']);
                        else this.construct = [];
                    } else {
                        this.page = {};
                        this.construct = [];
                    }
                } else {
                    this.setAlert(result.message, 'error');
                }
            })
            .catch(e => {
                this.setAlert(e, 'error');
                this.errors.push(e)
            });
        },
        getElements: function() {
            this.elements = {
                text_left_image_right: {
                    'title': this.translations.text_left_image_right,
                    'image': this.example.bg_image,
                    'options': {
                        'title': this.example.title,
                        'text': '<p>' + this.example.text_medium + '</p>',
                        'cta_button': this.example.btn_txt,
                        'bg_image': this.example.bg_image
                    }
                },
                text_right_image_left: {
                    'title': this.translations.text_right_image_left,
                    'image': this.example.bg_image,
                    'options': {
                        'title': this.example.title,
                        'text': '<p>' + this.example.text_medium + '</p>',
                        'cta_button': this.example.btn_txt,
                        'bg_image': this.example.bg_image
                    }
                },
                block: {
                    'title': this.translations.block,
                    'image': this.example.bg_image,
                    'options': {
                        'title': this.example.title,
                        'text': '<p>' + this.example.text_medium + '</p>',
                        'cta_button': this.example.btn_txt,
                        'bg_image': this.example.bg_image
                    }
                },
                html: {
                    'title': this.translations.html,
                    'image': this.example.bg_image
                },
                slider: {
                    'title': this.translations.slider,
                    'image': this.example.bg_image
                },
                collapse: {
                    'title': this.translations.collapse,
                    'image': this.example.bg_image
                },
                hero: {
                    'title': this.translations.collapse,
                    'image': this.example.bg_image
                }
            };
        },
        selectElement: function(event) {
            this.modal_view = 'elements';
            this.launchModal();
        },
        addElement: function(event) {
            this.construct.push({
                'type': event.target.dataset.element
            });







            this.modal = false;
        },
        activateElement: function(event) {
            this.active_element = event.target;
        },
        launchModal: function() {
            document.getElementById('admin-content').style.zIndex = '14';
            this.modal = true;
        },
        closeModal: function() {
            this.modal = false;
            document.getElementById('admin-content').style.zIndex = '11';
        },
        update: function(event){

            this.enableEdit = false;
            this.page.content = document.getElementById('content-editor').innerHTML;
            this.enableEdit = true;

            let headers = {
                'Content-Type': 'application/json;charset=UTF-8',
                "Authorization" : "Bearer " + apikey
            }

            let params = {};
            params['id'] = this.page_id;
            params['content'] = this.page.content;
            params['construct'] = this.construct;
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
        setEditor: function(event) {
            this.createCookie('selected_editor', event.target.dataset.editor);
            this.selected_editor = event.target.dataset.editor;
            this.selected_editor_name = event.target.textContent;
        },
        showPanel: function(event) {
            if (this.panel != event.target.dataset.panel) this.panel = event.target.dataset.panel;
            else this.panel = '';
        },
        setAlert: function(text, type) {
            var self = this;
            this.alert = {text: text, type: type};
            setTimeout(function() { self.alert = {}; }, 5000);
        },
        createCookie: function(name, value, days) {
            var expires;
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            } else {
                expires = "";
            }
            document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/;";
        },
        readCookie: function(name) {
            var nameEQ = escape(name) + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
            }
            return false;
        }
    }
}
</script>

<style scoped>

</style>
