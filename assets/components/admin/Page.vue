<template>
    <v-container fluid>
        <v-form>
            <div class="mb-3">
                <v-menu offset-y>
                    <template v-slot:activator="{ on }">
                        <v-btn color="secondary" :dark="darkmode" v-on="on">
                            {{translate_name}}
                        </v-btn>
                    </template>
                    <v-list>
                        <v-list-item v-for="item in locales" :key="item.id" @click="setTranslate" :data-lid="item.id">
                            <v-list-item-title>{{ item.title }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
                <v-btn color="secondary" @click="showPanel" data-panel="meta" :dark="darkmode">{{translations.meta_data}}</v-btn>
                <v-btn color="secondary" @click="showPanel" data-panel="settings" :dark="darkmode">{{translations.settings}}</v-btn>
                <v-btn color="secondary" @click="showPanel" data-panel="css" :dark="darkmode">{{translations.custom_css}}</v-btn>
                <v-btn color="secondary" @click="showPanel" data-panel="js" :dark="darkmode">{{translations.custom_js}}</v-btn>
                <v-menu>
                    <template v-slot:activator="{ on }">
                        <v-btn color="secondary" :dark="darkmode" v-on="on">
                            {{selected_editor_name}}
                        </v-btn>
                    </template>
                    <v-list dense :dark="darkmode">
                        <v-list-item @click="setEditor('html')">
                            <v-list-item-title>{{ translations.html || 'HTML' }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="setEditor('editor')">
                            <v-list-item-title>{{ translations.editor || 'Editor' }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="setEditor('builder')">
                            <v-list-item-title>{{ translations.builder || 'Builder' }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
                <v-btn color="primary" @click="update">{{translations.submit}}</v-btn>
            </div>

            <transition-group name="slide">
                <v-card v-if="panel == 'meta'" class="mb-3" key="meta" :dark="darkmode">
                    <v-container fluid>
                        <v-row fluid>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :label="translations['title_tag'] || 'Meta title'"
                                    v-model="page.metaTitle"
                                    counter
                                    maxlength="60"
                                    :dark="darkmode"
                                 ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :label="translations['meta_keywords'] || 'Meta keywords'"
                                    v-model="page.metaKeywords"
                                    counter
                                    maxlength="255"
                                    :dark="darkmode"
                                 ></v-text-field>
                            </v-col>
                        </v-row>

                        <v-textarea
                            :label="translations['meta_description'] || 'Meta description'"
                            v-model="page.metaDescription"
                            rows="3"
                            counter
                            maxlength="158"
                            :dark="darkmode"
                        ></v-textarea>
                        <v-textarea
                            :label="translations['custom_meta'] || 'Custom meta'"
                            v-model="page.metaCustom"
                            rows="2"
                            auto-grow
                            :dark="darkmode"
                        ></v-textarea>
                    </v-container>
                </v-card>
                <v-card v-else-if="panel == 'settings'" class="mb-3" key="panel" :dark="darkmode">
                    <v-container fluid>
                        <v-row fluid>
                            <v-col v-if="false" cols="12" md="3">
                                <v-img
                                ></v-img>
                                <v-btn color="secondary" @click="" data-toggle="modal" data-target="#im-media-manager">
                                    {{ translations['add_image'] || 'Add image'}}
                                </v-btn>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-row fluid>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-menu
                                            v-model="publishDateMenu"
                                            :close-on-content-click="false"
                                            :nudge-right="40"
                                            transition="scale-transition"
                                            offset-y
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="page.publishDate"
                                                    :label="translations['publish_date'] || 'Publish date'"
                                                    prepend-icon="fal fa-calendar-alt"
                                                    readonly
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker v-model="page.publishDate" @input="publishDateMenu = false"></v-date-picker>
                                        </v-menu>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-menu
                                            v-model="expireDateMenu"
                                            :close-on-content-click="false"
                                            :nudge-right="40"
                                            transition="scale-transition"
                                            offset-y
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="page.expireDate"
                                                    :label="translations['expire_date'] || 'Expire date'"
                                                    prepend-icon="fal fa-calendar-alt"
                                                    readonly
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker v-model="page.expireDate" @input="expireDateMenu = false"></v-date-picker>
                                        </v-menu>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-select
                                            :items="pageStatuses"
                                            :label="translations['page_status'] || 'Page status'"
                                            v-model="page.pageStatus"
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-select
                                            :items="pageWidths"
                                            :label="translations['page_width'] || 'Page width'"
                                            v-model="page.pageWidth"
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-select
                                            :items="yesno"
                                            :label="translations['page_wdisable_layoutidth'] || 'Disable layout'"
                                            v-model="page.disableLayout"
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-select
                                            :items="pageWeights"
                                            :label="translations['page_weight'] || 'Page weight'"
                                            v-model="page.pageWeight"
                                        ></v-select>
                                    </v-col>
                                </v-row>
                                <label>{{translations['grant_access'] || 'Grant access to roles'}}</label>
                                <v-row>
                                    <v-col cols="12" md="4" lg="3" v-for="role in roles" :key="role.id">
                                        <v-checkbox dense v-model="page.role[role.id]" :label="role.name"></v-checkbox>
                                    </v-col>
                                </v-row>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card>
                <v-card v-if="panel == 'css'" class="mb-3" key="css" :dark="darkmode">
                    <codemirror v-model="page.customCss" :options="cmCssOptions"></codemirror>
                </v-card>
                <v-card v-if="panel == 'js'" class="mb-3" key="js" :dark="darkmode">
                    <codemirror v-model="page.customJs" :options="cmJsOptions"></codemirror>
                </v-card>
            </transition-group>


            <v-card class="mb-3" :dark="darkmode">
                <v-container fluid>
                    <v-text-field
                        :label="translations.enter_title || 'Enter a page title here...'"
                        v-model="page.pageTitle"
                        :dark="darkmode"
                    ></v-text-field>
                    <v-text-field
                        :label="translations.enter_page_route || 'Enter a page route here...'"
                        v-model="page.pageRoute"
                        :dark="darkmode"
                    ></v-text-field>
                </v-container>
            </v-card>
            <v-card v-if="selected_editor == 'html'" class="mb-3" :dark="darkmode">
                <v-container fluid>
                    <v-textarea
                        v-model="page.content"
                        label="HTML"
                        rows="24"
                    ></v-textarea>
                </v-container>
            </v-card>
            <v-card v-if="selected_editor == 'editor'" class="mb-3" :dark="darkmode">
                <richtext v-model="page.content"></richtext>
            </v-card>
            <editor v-if="selected_editor == 'builder'" v-model="page.content" class="mt-2"></editor>
        </v-form>
    </v-container>
</template>

<script>
    export default {
        name: "Page",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                panel: '',
                translate_id: 0,
                translate_name: '',
                page_id: 0,
                page: {
                    role: ''
                },
                roles: [],
                selected_editor_name: 'Builder',
                selected_editor: 'builder',
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
                publishDateMenu: false,
                expireDateMenu: false,
                pageStatuses: [
                    { text: translations['publish'] || 'Published', value: 1 },
                    { text: translations['draft'] || 'Draft', value: 0 }
                ],
                pageWidths: [
                    { text: translations['default'] || 'Default', value: 1 },
                    { text: '1280px', value: 1280 },
                    { text: '1140px', value: 1140 },
                    { text: '980px', value: 980 },
                    { text: '700px', value: 700 }
                ],
                yesno: [
                    { text: translations['no'] || 'No', value: 0 },
                    { text: translations['yes'] || 'Yes', value: 1 }
                ],
                pageWeights: [
                    { text: '1.0', value: 10 },
                    { text: '0.9', value: 9 },
                    { text: '0.8', value: 8 },
                    { text: '0.7', value: 7 },
                    { text: '0.6', value: 6 },
                    { text: '0.5', value: 5 },
                    { text: '0.4', value: 4 },
                    { text: '0.3', value: 3 },
                    { text: '0.2', value: 2 },
                    { text: '0.1', value: 1 }
                ],
            }
        },
        computed: {
            locale () {
                return this.$store.state.locale;
            },
            locale_id () {
                return this.$store.state.locale_id;
            },
            locale_name () {
                return this.$store.state.locale_name;
            },
            locales () {
                return this.$store.state.locales;
            },
            translations () {
                return this.$store.state.translations;
            },
            darkmode () {
                return this.$store.state.darkmode;
            },
        },
        created() {
            this.translate_name = this.locale_name;
            this.translate_id = this.locale_id;
            this.domain = 'http://yuna.test';
            this.getRoles();
            if (this.$attrs.id > 0) {
                this.page_id = this.$attrs.id;
                this.getPage();
            }
            if (this.$cookies.isKey('selected_editor')) {
                let selectedEditor =this.$cookies.get('selected_editor');
                this.selected_editor = selectedEditor;
                this.selected_editor_name = this.translations[selectedEditor] || selectedEditor;
            }
        },
        methods: {
            getRoles: function() {
                this.$axios.get('/api/v1/user/role/list/', {headers: this.headers})
                .then(response => {
                    this.roles = response.data.data;
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            },
            setTranslate: function(event) {
                event.target.parentNode.classList.toggle("d-block");
                this.translate_id = parseInt(event.target.dataset.lid);

                for (var i = 0; i < this.locales.length; i++) {
                    if (this.translate_id == this.locales[i]['id']) this.translate_name = this.locales[i]['name'];
                }

                this.getPage();
            },
            getPage: function() {

                let url = '/api/v1/page/get/' + this.page_id + '/';

                let params = {};
                if (this.translate_id > 0) params['locale'] = this.translate_id;

                this.$axios.post(url, params, {headers: this.headers})
                .then(response => {
                    var result = response.data;
                    if (result.success) {
                        if (result['data'].constructor === {}.constructor) {
                            this.page = result['data'];
                            this.page.role = result['roles'];
                        } else {
                            this.page = {};
                            this.construct = [];
                        }
                    } else {
                        this.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                    }
                })
                .catch(e => {
                    this.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                });
            },
            update: function(event){

                this.setElementInactive();
                this.enableEdit = false;
                var self = this;

                setTimeout(function() {
                    if (self.selected_editor == 'builder') {
                        let content = document.getElementById('content-editor').innerHTML;
                        content = content.replace(new RegExp('contenteditable="true"', 'g'), '');
                        content = content.replace(new RegExp('contenteditable="false"', 'g'), '');
                        content = content.replace(new RegExp('data-href', 'g'), 'href');
                        self.page.content = content;
                    }

                    let params = {};
                    params['id'] = self.page_id;
                    params['content'] = self.page.content;
                    params['construct'] = self.construct;
                    params['constructCss'] = self.construct_css;
                    params['customCss'] = self.page.customCss;
                    params['customJs'] = self.page.customJs;
                    params['disableLayout'] = self.page.disableLayout;
                    params['locale'] = self.translate_id;
                    params['mainImage'] = self.page.mainImage;
                    params['metaCustom'] = self.page.metaCustom;
                    params['metaDescription'] = self.page.metaDescription;
                    params['metaKeywords'] = self.page.metaKeywords;
                    params['metaTitle'] = self.page.metaTitle;
                    params['pageRoute'] = self.page.pageRoute;
                    params['pageTitle'] = self.page.pageTitle;
                    params['pageWeight'] = self.page.pageWeight;
                    params['pageWidth'] = self.page.pageWidth;
                    params['pageTitle'] = self.page.pageTitle;
                    params['publishDate'] = self.page.publishDate;
                    params['expireDate'] = self.page.expireDate;
                    params['status'] = self.page.status;
                    params['role'] = self.page.role;

                    let url = '/api/v1/page/insert/';
                    if (self.page_id > 0) url = '/api/v1/page/update/'+ self.page_id + '/';

                    self.$axios.put(url, params, {headers: self.headers})
                        .then(response => {
                            var result = response.data;

                            if (result.success) {
                                self.page_id = parseInt(result['id']);
                                self.$store.commit('setAlert', {type: 'success', message: translations.saved || 'Data saved', autohide: true});
                            } else {
                                self.$store.commit('setAlert', {type: 'error', message: translations[result.message] || result.message, autohide: true});
                            }
                            self.enableEdit = true;
                        })
                        .catch(e => {
                            self.$store.commit('setAlert', {type: 'error', message: e, autohide: true});
                            self.enableEdit = true;
                        });
                }, 2);
            },
            setEditor: function(editor) {
                console.log(editor);
                event.target.parentNode.classList.toggle("d-block");
                this.$cookies.set('selected_editor', editor);
                this.selected_editor = editor;
                this.selected_editor_name = event.target.textContent.trim();
            },
            toggleDropdown: function(event) {
                var dropdown = event.target.parentNode.getElementsByClassName('dropdown-menu');
                for (var i = 0; i < dropdown.length; i++) {
                    dropdown[i].classList.toggle("d-block");
                }
            },
            showPanel: function(event) {
                if (this.panel != event.target.dataset.panel) this.panel = event.target.dataset.panel;
                else this.panel = '';
            },
        }
    }
</script>

<style scoped lang="scss">

.slide-enter-active {
   -moz-transition-duration: 0.3s;
   -webkit-transition-duration: 0.3s;
   -o-transition-duration: 0.3s;
   transition-duration: 0.3s;
   -moz-transition-timing-function: ease-in;
   -webkit-transition-timing-function: ease-in;
   -o-transition-timing-function: ease-in;
   transition-timing-function: ease-in;
}

.slide-leave-active {
   -moz-transition-duration: 0.3s;
   -webkit-transition-duration: 0.3s;
   -o-transition-duration: 0.3s;
   transition-duration: 0.3s;
   -moz-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
   -webkit-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
   -o-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
   transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
}

.slide-enter-to, .slide-leave {
   max-height: 100px;
   overflow: hidden;
}

.slide-enter, .slide-leave-to {
   overflow: hidden;
   max-height: 0;
}

</style>
