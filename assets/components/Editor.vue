<template>
    <form method="post" v-on:submit.prevent="update">
        <transition name="fade" enter-active-class="animated zoomIn" leave-active-class="animated zoomOut">
            <div v-if="alert.text != '' && alert.type == 'success'" class="alert alert-success" role="alert">
                {{alert.text}}
            </div>
            <div v-else-if="alert.text != '' && alert.type == 'error'" class="alert alert-danger" role="alert">
                {{alert.text}}
            </div>
        </transition>
        <div class="row">
            <div class="col-md-8 col-lg-9">
                <nav class="editor-nav">
                    <div class="btn-group mb-3" role="group" aria-label="Editor functions">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownLocaleButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-on:click="toggleDropdown">
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
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownEditorButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-on:click="toggleDropdown">
                                {{selected_editor_name}}
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownEditorButton">
                                <button class="dropdown-item" data-editor="html" v-on:click.prevent="setEditor">{{translations.html || 'HTML'}}</button>
                                <button class="dropdown-item" data-editor="editor" v-on:click.prevent="setEditor">{{translations.editor || 'Editor'}}</button>
                                <button class="dropdown-item" data-editor="builder" v-on:click.prevent="setEditor">{{translations.builder || 'Builder'}}</button>
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
                                                <input id="form_role_" name="form-role[]" class="form-check-input" :value="role.id" type="checkbox" v-model="page.role[role.id]">
                                                {{ role.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="panel == 'css'" class="mb-2">
                        <codemirror v-model="page.customCss" :options="cmCssOptions"></codemirror>
                    </div>
                    <div v-if="panel == 'js'" class="mb-2">
                        <codemirror v-model="page.customJs" :options="cmJsOptions"></codemirror>
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
                    <div v-for="(element, index) in construct">
                        <div v-if="element.type == 'text_left_image_right'" :id="element.id" v-bind:class="{ component: true, row: true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                            <div v-if="element.selected === true" class="component-functions">
                                <button v-if="index < construct.length-1" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementDown(index)"><i class="far fa-chevron-down"></i></button>
                                <button v-if="index > 0" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementUp(index)"><i class="far fa-chevron-up"></i></button>
                                <button class="btn btn-sm btn-danger" v-on:click.prevent="removeElement(index)"><i class="fal fa-trash-alt"></i></button>
                            </div>
                            <div class="col-sm-6 wrap-content">
                                <h3 v-if="element.parts.title.settings.display!='none'" v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}" :contenteditable="enableEdit" v-html="element.parts.title.content" v-on:click.stop="setActiveElement(element.parts.title, index);"></h3>
                                <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-text': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                                    <ckeditor :editor="inlineEditor" v-model="element.parts.text.content" :config="inlineEditorConfig"></ckeditor>
                                </div>
                                <a v-if="element.parts.button.settings.display!='none'" ::data-href="element.parts.button.settings.href" v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}" :contenteditable="enableEdit" v-on:click.stop="setActiveElement(element.parts.button, index);">{{element.parts.button.content}}</a>
                            </div>
                            <div v-bind:class="{ 'col-sm-6': true, 'component-image': true, active: element.parts.image.selected == true}" v-on:click.stop="setActiveElement(element.parts.image, index);"></div>
                        </div>
                        <div v-else-if="element.type == 'text_right_image_left'" :id="element.id" v-bind:class="{ component: true, row: true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                            <div v-if="element.selected === true" class="component-functions">
                                <button v-if="index < construct.length-1" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementDown(index)"><i class="far fa-chevron-down"></i></button>
                                <button v-if="index > 0" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementUp(index)"><i class="far fa-chevron-up"></i></button>
                                <button class="btn btn-sm btn-danger" v-on:click.prevent="removeElement(index)"><i class="fal fa-trash-alt"></i></button>
                            </div>
                            <div v-bind:class="{ 'col-sm-6': true, 'component-image': true, active: element.parts.image.selected == true}" v-on:click.stop="setActiveElement(element.parts.image, index);"></div>
                            <div class="col-sm-6 wrap-content">
                                <h3 v-if="element.parts.title.settings.display!='none'" v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}" :contenteditable="enableEdit" v-html="element.parts.title.content" v-on:click.stop="setActiveElement(element.parts.title, index);"></h3>
                                <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-text': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                                    <ckeditor :editor="inlineEditor" v-model="element.parts.text.content" :config="inlineEditorConfig"></ckeditor>
                                </div>
                                <a v-if="element.parts.button.settings.display!='none'" :data-href="element.parts.button.settings.href" v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}" :contenteditable="enableEdit" v-on:click.stop="setActiveElement(element.parts.button, index);">{{element.parts.button.content}}</a>
                            </div>
                        </div>
                        <div v-else-if="element.type == 'text_text'" :id="element.id" v-bind:class="{ component: true, row: true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                            <div v-if="element.selected === true" class="component-functions">
                                <button v-if="index < construct.length-1" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementDown(index)"><i class="far fa-chevron-down"></i></button>
                                <button v-if="index > 0" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementUp(index)"><i class="far fa-chevron-up"></i></button>
                                <button class="btn btn-sm btn-danger" v-on:click.prevent="removeElement(index)"><i class="fal fa-trash-alt"></i></button>
                            </div>
                            <div class="col-sm-6 wrap-content">
                                <h3 v-if="element.parts.title.settings.display!='none'" v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}" :contenteditable="enableEdit" v-html="element.parts.title.content" v-on:click.stop="setActiveElement(element.parts.title, index);"></h3>
                                <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-text': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                                    <ckeditor :editor="inlineEditor" v-model="element.parts.text.content" :config="inlineEditorConfig"></ckeditor>
                                </div>
                                <a v-if="element.parts.button.settings.display!='none'" :data-href="element.parts.button.settings.href" v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}" :contenteditable="enableEdit" v-on:click.stop="setActiveElement(element.parts.button, index);">{{element.parts.button.content}}</a>
                            </div>
                            <div class="col-sm-6 wrap-content">
                                <h3 v-if="element.parts.title2.settings.display!='none'" v-bind:class="{ 'component-title2': true, active: element.parts.title2.selected == true}" :contenteditable="enableEdit" v-html="element.parts.title2.content" v-on:click.stop="setActiveElement(element.parts.title2, index);"></h3>
                                <div v-if="element.parts.text2.settings.display!='none'" v-bind:class="{ 'component-text2': true, active: element.parts.text2.selected == true}" v-on:click.stop="setActiveElement(element.parts.text2, index);">
                                    <ckeditor :editor="inlineEditor" v-model="element.parts.text2.content" :config="inlineEditorConfig"></ckeditor>
                                </div>
                                <a v-if="element.parts.button2.settings.display!='none'" :data-href="element.parts.button2.settings.href" v-bind:class="{ 'component-button2': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button2.selected == true}" :contenteditable="enableEdit" v-on:click.stop="setActiveElement(element.parts.button2, index);">{{element.parts.button2.content}}</a>
                            </div>
                        </div>
                        <div v-else-if="element.type == 'image_image'" :id="element.id" v-bind:class="{ 'component': true, row: true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                            <div v-if="element.selected === true" class="component-functions">
                                <button v-if="index < construct.length-1" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementDown(index)"><i class="far fa-chevron-down"></i></button>
                                <button v-if="index > 0" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementUp(index)"><i class="far fa-chevron-up"></i></button>
                                <button class="btn btn-sm btn-danger" v-on:click.prevent="removeElement(index)"><i class="fal fa-trash-alt"></i></button>
                            </div>
                            <div v-bind:class="{ 'col-sm-6': true, 'component-image': true, active: element.parts.image.selected == true}" v-on:click.stop="setActiveElement(element.parts.image, index);"></div>
                            <div v-bind:class="{ 'col-sm-6': true, 'component-image2': true, active: element.parts.image2.selected == true}" v-on:click.stop="setActiveElement(element.parts.image2, index);"></div>
                        </div>
                        <div v-else-if="element.type == 'block'" :id="element.id" class="" v-bind:class="{ 'component-block': true, 'px-3': true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                            <div v-if="element.selected === true" class="component-functions">
                                <button v-if="index < construct.length-1" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementDown(index)"><i class="far fa-chevron-down"></i></button>
                                <button v-if="index > 0" class="btn btn-sm btn-secondary" v-on:click.prevent="moveElementUp(index)"><i class="far fa-chevron-up"></i></button>
                                <button class="btn btn-sm btn-danger" v-on:click.prevent="removeElement(index)"><i class="fal fa-trash-alt"></i></button>
                            </div>
                            <h3 v-if="element.parts.title.settings.display!='none'" v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}" :contenteditable="enableEdit" v-html="element.parts.title.content" v-on:click.stop="setActiveElement(element.parts.title, index);"></h3>
                            <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-text': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                                <ckeditor :editor="inlineEditor" v-model="element.parts.text.content" :config="inlineEditorConfig"></ckeditor>
                            </div>
                            <a v-if="element.parts.button.settings.display!='none'" :data-href="element.parts.button.settings.href" v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}" :contenteditable="enableEdit" v-on:click.stop="setActiveElement(element.parts.button, index);">{{element.parts.button.content}}</a>
                        </div>
                    </div>
                    <div v-if="enableEdit === true" class="text-center mt-3 py-5">
                        <button class="btn btn-lg btn-warning" v-on:click.prevent="selectElement"><i class="fas fa-plus"></i> {{translations.add_element}}</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 component-settings">
                <div v-if="typeof active_component !== 'undefined' && active_component !== false" class="card">
                    <div class="card-header" id="component-settings" v-on:click="setActiveElement(construct[active_component], active_component);">
                        {{translations.component || 'Component'}}
                        <i v-if="construct[active_component].selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].selected == true}">
                        <settings :settings="construct[active_component].settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.title != undefined" class="card-header" id="component-title-settings" v-on:click="setActiveElement(construct[active_component].parts.title, active_component);">
                        {{translations.title || 'Title'}}
                        <i v-if="construct[active_component].parts.title.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.title != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.title.selected == true}">
                        <settings :settings="construct[active_component].parts.title.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.text != undefined" class="card-header" id="component-text-settings" v-on:click="setActiveElement(construct[active_component].parts.text, active_component);">
                        {{translations.content || 'Content'}}
                        <i v-if="construct[active_component].parts.text.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.text != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.text.selected == true}">
                        <settings :settings="construct[active_component].parts.text.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.button != undefined" class="card-header" id="component-button-settings" v-on:click="setActiveElement(construct[active_component].parts.button, active_component);">
                        {{translations.button || 'Button'}}
                        <i v-if="construct[active_component].parts.button.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.button != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.button.selected == true}">
                        <settings :settings="construct[active_component].parts.button.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.title2 != undefined" class="card-header" id="component-title-settings" v-on:click="setActiveElement(construct[active_component].parts.title2, active_component);">
                        {{translations.title || 'Title'}} 2
                        <i v-if="construct[active_component].parts.title2.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.title2 != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.title2.selected == true}">
                        <settings :settings="construct[active_component].parts.title2.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.text2 != undefined" class="card-header" id="component-text-settings" v-on:click="setActiveElement(construct[active_component].parts.text2, active_component);">
                        {{translations.content || 'Content'}} 2
                        <i v-if="construct[active_component].parts.text2.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.text2 != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.text2.selected == true}">
                        <settings :settings="construct[active_component].parts.text2.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.button2 != undefined" class="card-header" id="component-button-settings" v-on:click="setActiveElement(construct[active_component].parts.button2, active_component);">
                        {{translations.button || 'Button'}} 2
                        <i v-if="construct[active_component].parts.button2.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.button2 != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.button2.selected == true}">
                        <settings :settings="construct[active_component].parts.button2.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.image != undefined" class="card-header" id="component-image-settings" v-on:click="setActiveElement(construct[active_component].parts.image, active_component);">
                        {{translations.image || 'Image'}}
                        <i v-if="construct[active_component].parts.image.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.image != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.image.selected == true}">
                        <settings :settings="construct[active_component].parts.image.settings"></settings>
                    </div>
                    <div v-if="construct[active_component].parts.image2 != undefined" class="card-header" id="component-image-settings" v-on:click="setActiveElement(construct[active_component].parts.image2, active_component);">
                        {{translations.image || 'Image'}} 2
                        <i v-if="construct[active_component].parts.image2.selected == true" class="fas fa-chevron-down float-right"></i>
                        <i v-else class="fas fa-chevron-left float-right"></i>
                    </div>
                    <div v-if="construct[active_component].parts.image2 != undefined" v-bind:class="{ 'card-body': true, collapse: true, show: construct[active_component].parts.image2.selected == true}">
                        <settings :settings="construct[active_component].parts.image2.settings"></settings>
                    </div>
                </div>
            </div>
        </div>
        <modal name="page-elements" width="80%" height="90%">
            <div class="container-fluid p-3">
                <div class="row">
                    <div v-for="(element, index) in elements" v-if="element.active == true" class="col-sm-6 col-md-4 col-lg-3 mb-4" :data-element="index">
                        <img :src="element.image" :alt="element.title" class="img-fluid w-100 mb-1">
                        <h4>
                            {{element.title}}
                            <button v-on:click.prevent="addElement" class="btn btn-sm btn-secondary float-right" :data-element="element.type">{{translations.select}}</button>
                        </h4>
                    </div>
                </div>
            </div>
        </modal>
        <modal name="html-editor" width="80%" height="90%">

        </modal>
    </form>
</template>

<script>
    import axios from 'axios';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import InlineEditor from '@ckeditor/ckeditor5-build-inline';
    import EditorSettings from '../components/EditorSettings';

    export default {
        name: "editor",
        components: {
            'settings': EditorSettings
        },
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                panel: '',
                translate_id: 0,
                translate_name: '',
                page_id: 0,
                page: {},
                roles: [],
                alert: {},
                editor: ClassicEditor, //ClassicEditor,
                editorData: {}, //'<p>Rich-text editor content.</p>',
                editorConfig: {},
                inlineEditor: InlineEditor, //InlineEditor,
                inlineEditorData: {}, //'<p>Rich-text editor content.</p>',
                inlineEditorConfig: {
                    toolbar: [ 'heading',  'bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', '|', 'link' ]
                },
                elements: {},
                construct: [],
                construct_css: '',
                enableEdit: true,
                active_component: false,
                example: {},
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
        },
        created() {
            this.translate_name = this.locale_name;
            this.translate_id = this.locale_id;
            this.domain = 'http://yuna.test';
            this.getRoles();
            this.getExample();
            this.getElements();
            if (this.$attrs.id > 0) {
                this.page_id = this.$attrs.id;
                this.getPage();
            }
            var checkSelectedEditor = this.readCookie('selected_editor');
            if (checkSelectedEditor) {
                this.selected_editor = checkSelectedEditor;
                this.selected_editor_name = this.translations[checkSelectedEditor] || checkSelectedEditor;
            }
        },
        methods: {
            getRoles: function() {
                axios.get('/api/v1/user/role/list/', {headers: this.headers})
                .then(response => {
                    this.roles = JSON.parse(response.data)['data'];
                })
                .catch(e => {
                    this.errors.push(e)
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

                let params = {};
                if (this.translate_id > 0) params['locale'] = this.translate_id;

                axios.post(url, params, {headers: this.headers})
                .then(response => {
                    var result = JSON.parse(response.data);
                    if (result.success) {
                        if (result['data'].constructor === {}.constructor) {
                            this.page = result['data'];
                            this.page.role = result['roles'];
                            if (result['data']['construct']) this.construct = JSON.parse(result['data']['construct']);
                            else this.construct = [];
                            this.generateCss();
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

                var component_settings = {
                    display: '',
                    width: '',
                    height: '',
                    min_width: '',
                    min_height: '',
                    max_width: '',
                    max_height: '',
                    display: '',
                    overflow: 'visible',
                    padding: {
                        top: '0',
                        right: '0',
                        bottom: '0',
                        left: '0',
                    },
                    margin: {
                        top: '0',
                        right: '0',
                        bottom: '0',
                        left: '0',
                    },
                    font_weight: '',
                    font_size: '',
                    line_height: '1.6em',
                    color: {
                        hex: '#000000',
                        hsl: { h: 150, s: 0, l: 0, a: 1 },
                        hsv: { h: 150, s: 0, v: 0, a: 1 },
                        rgba: { r: 0, g: 0, b: 0, a: 1 },
                        a: 1
                    },
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_image: '',
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_width: 0,
                    border_width_unit: 'px',
                    border_radius: 0,
                    border_radius_unit: 'px',
                    border_style: 'solid',
                    custom_css: '',
                };
                var title_settings = {
                    display: '',
                    width: '',
                    height: '',
                    min_width: '',
                    min_height: '',
                    max_width: '',
                    max_height: '',
                    display: '',
                    overflow: 'visible',
                    padding: {
                        top: '0',
                        right: '0',
                        bottom: '0',
                        left: '0',
                    },
                    margin: {
                        top: '15px',
                        right: '0',
                        bottom: '15px',
                        left: '0',
                    },
                    tag: 'h3',
                    font_weight: '',
                    font_size: '',
                    line_height: '1.6em',
                    color: {
                        hex: '#000000',
                        hsl: { h: 150, s: 0, l: 0, a: 1 },
                        hsv: { h: 150, s: 0, v: 0, a: 1 },
                        rgba: { r: 0, g: 0, b: 0, a: 1 },
                        a: 1
                    },
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_image: '',
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_width: 0,
                    border_width_unit: 'px',
                    border_radius: 0,
                    border_radius_unit: 'px',
                    border_style: 'solid',
                    custom_css: '',
                };
                var text_settings = {
                    display: '',
                    width: '',
                    height: '',
                    min_width: '',
                    min_height: '',
                    max_width: '',
                    max_height: '',
                    display: '',
                    overflow: 'visible',
                    padding: {
                        top: '0',
                        right: '0',
                        bottom: '0',
                        left: '0',
                    },
                    margin: {
                        top: '0',
                        right: '0',
                        bottom: '15px',
                        left: '0',
                    },
                    font_weight: '',
                    font_size: '',
                    line_height: '1.6em',
                    color: {
                        hex: '#000000',
                        hsl: { h: 150, s: 0, l: 0, a: 1 },
                        hsv: { h: 150, s: 0, v: 0, a: 1 },
                        rgba: { r: 0, g: 0, b: 0, a: 1 },
                        a: 1
                    },
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_image: '',
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_width: 0,
                    border_width_unit: 'px',
                    border_radius: 0,
                    border_radius_unit: 'px',
                    border_style: 'solid',
                    custom_css: '',
                };
                var button_settings = {
                    display: '',
                    href: '',
                    width: '',
                    height: '',
                    min_width: '',
                    min_height: '',
                    max_width: '',
                    max_height: '',
                    display: '',
                    overflow: 'visible',
                    padding: {
                        top: '5px',
                        right: '25px',
                        bottom: '5px',
                        left: '25px',
                    },
                    margin: {
                        top: '0',
                        right: '0',
                        bottom: '15px',
                        left: '0',
                    },
                    font_weight: '',
                    font_size: '',
                    line_height: '1.6em',
                    color: {
                        hex: '#FFFFFF',
                        hsl: { h: 150, s: 0, l: 1, a: 1 },
                        hsv: { h: 150, s: 0, v: 1, a: 1 },
                        rgba: { r: 255, g: 255, b: 255, a: 1 },
                        a: 1
                    },
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_image: '',
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_width: 0,
                    border_width_unit: 'px',
                    border_radius: 0,
                    border_radius_unit: 'px',
                    border_style: 'solid',
                    custom_css: '',
                };
                var image_settings = {
                    background_color: {},
                    background_image: this.example.bg_image,
                    background_size: 'cover',
                    background_position: 'center center',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: 'repeat',
                    background_attachment: 'scroll',
                    border_color: {},
                    border_width: 0,
                    border_width_unit: 'px',
                    border_radius: 0,
                    border_radius_unit: 'px',
                    border_style: 'solid',
                    custom_css: '',
                };

                this.elements = {
                    text_left_image_right: {
                        type: 'text_left_image_right',
                        title: this.translations.text_left_image_right,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                            title: {
                                content: this.example.title,
                                selected: false,
                                settings: title_settings,
                            },
                            text: {
                                content: '<p>' + this.example.text_medium + '</p>',
                                selected: false,
                                settings: text_settings,
                            },
                            button: {
                                content: this.example.btn_txt,
                                selected: false,
                                settings: button_settings,
                            },
                            image: {
                                selected: false,
                                settings: image_settings,
                            },
                        }
                    },
                    text_right_image_left: {
                        type: 'text_right_image_left',
                        title: this.translations.text_right_image_left,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                            title: {
                                content: this.example.title,
                                selected: false,
                                settings: title_settings,
                            },
                            text: {
                                content: '<p>' + this.example.text_medium + '</p>',
                                selected: false,
                                settings: text_settings,
                            },
                            button: {
                                content: this.example.btn_txt,
                                selected: false,
                                settings: button_settings,
                            },
                            image: {
                                selected: false,
                                settings: image_settings,
                            },
                        }
                    },
                    text_text: {
                        type: 'text_text',
                        title: this.translations.text_text,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                            title: {
                                content: this.example.title,
                                selected: false,
                                settings: title_settings,
                            },
                            text: {
                                content: '<p>' + this.example.text_medium + '</p>',
                                selected: false,
                                settings: text_settings,
                            },
                            button: {
                                content: this.example.btn_txt,
                                selected: false,
                                settings: button_settings,
                            },
                            title2: {
                                content: this.example.title,
                                selected: false,
                                settings: title_settings,
                            },
                            text2: {
                                content: '<p>' + this.example.text_medium + '</p>',
                                selected: false,
                                settings: text_settings,
                            },
                            button2: {
                                content: this.example.btn_txt,
                                selected: false,
                                settings: button_settings,
                            },
                        }
                    },
                    image_image: {
                        type: 'image_image',
                        title: this.translations.image_image,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                            image: {
                                selected: false,
                                settings: image_settings
                            },
                            image2: {
                                selected: false,
                                settings: image_settings
                            },
                        }
                    },
                    block: {
                        type: 'block',
                        title: this.translations.block,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                            title: {
                                content: this.example.title,
                                selected: false,
                                settings: title_settings,
                            },
                            text: {
                                content: '<p>' + this.example.text_medium + '</p>',
                                selected: false,
                                settings: text_settings,
                            },
                            button: {
                                content: this.example.btn_txt,
                                selected: false,
                                settings: button_settings,
                            },
                        }
                    },
                    html: {
                        type: 'html',
                        title: this.translations.html,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                            text: {
                                content: '<p>' + this.example.text_medium + '</p>',
                                selected: false,
                                settings: text_settings,
                            },
                        }
                    },
                    slider: {
                        type: 'slider',
                        title: this.translations.slider,
                        image: this.example.bg_image,
                        active: false,
                        selected: false,
                    },
                    collapse: {
                        type: 'collapse',
                        title: this.translations.collapse,
                        image: this.example.bg_image,
                        active: false,
                        selected: false,
                    },
                    hero: {
                        type: 'hero',
                        title: this.translations.hero,
                        image: this.example.bg_image,
                        active: false,
                        selected: false,
                    }
                };
            },
            selectElement: function(event) {
                this.$modal.show('page-elements');
            },
            addElement: function(event) {

                if (!Date.now) Date.now = function() { return new Date().getTime(); }
                var timestamp = Math.floor(Date.now() / 1000);

                var element = this.copyElement(this.elements[event.target.dataset.element]);
                element.id = 'component-'+timestamp;
                this.construct.push(element);

                this.generateCss();
                this.$modal.hide('page-elements');
            },
            copyElement: function(o) {
            	let output, v, key
            	output = Array.isArray(o) ? [] : {}

            	for (key in o) {
            		v = o[key]
            		if(v) {
            			output[key] = (typeof v === "object") ? this.copyElement(v) : v
            		} else {
            			output[key] = v
            		}
            	}
            	return output;
            },
            setActiveElement: function(element, index) {

                this.setElementInactive();
                element.selected = true;
                this.active_component = index;

            },
            setElementInactive: function() {
                // Set all elements inactive
                for (var i = 0; i < this.construct.length; i++) {
                    this.construct[i].selected = false;

                    var parts = this.construct[i].parts;
                    for (var x in parts) {
                        parts[x].selected = false;
                    }
                }
            },
            removeElement: function(index) {
                this.construct.splice(index, 1);
                this.generateCss();
                this.active_component = false;
            },
            moveElementDown: function(index) {
                var tmp = this.construct[index+1];
                this.construct[index+1] = this.construct[index];
                this.construct[index] = tmp;
            },
            moveElementUp: function(index) {
                var tmp = this.construct[index-1];
                this.construct[index-1] = this.construct[index];
                this.construct[index] = tmp;
            },
            generateCss: function() {
                var css = "";
                for(var i=0;i< this.construct.length;i++) {

                    var id = this.construct[i].id;
                    var settings = this.construct[i].settings;
                    var parts = this.construct[i].parts;
                    var properties = this.readCssProperties(settings);
                    if (properties != '') css += "#" + id + " {" + properties + "} ";

                    for (var key in parts) {
                        var element = 'component-' + key;
                        var properties = this.readCssProperties(parts[key].settings);
                        if (properties != '') css += "#" + id + " ." + element + " {" + properties + "} ";
                    }
                }

                this.construct_css = css;

                /* Create style document */
                var element = document.getElementById('page-style');
                if (element !== null) element.parentNode.removeChild(element);
                var style = document.createElement('style');
                style.type = 'text/css';
                style.setAttribute('id', 'page-style');
                style.appendChild(document.createTextNode(css));
                document.getElementsByTagName("head")[0].appendChild(style);
            },
            readCssProperties:function(settings) {

                var css = "";

                if (typeof settings.width !== 'undefined' && settings.width != '') css+= "width: " + settings.width + "; ";
                if (typeof settings.height !== 'undefined' && settings.height != '') css+= "height: " + settings.height + "; ";
                if (typeof settings.min_width !== 'undefined' && settings.min_width != '') css+= "min-width: " + settings.min_width + "; ";
                if (typeof settings.min_height !== 'undefined' && settings.min_height != '') css+= "min-height: " + settings.min_height + "; ";
                if (typeof settings.max_width !== 'undefined' && settings.max_width != '') css+= "max-width: " + settings.max_width + "; ";
                if (typeof settings.max_height !== 'undefined' && settings.max_height != '') css+= "max-height: " + settings.max_height + "; ";
                if (typeof settings.overflow !== 'undefined' && settings.overflow != '') css+= "overflow: " + settings.overflow + "; ";
                if (typeof settings.padding !== 'undefined') {
                    if (settings.padding.top != '') css+= "padding-top: " + settings.padding.top + "; ";
                    if (settings.padding.right != '') css+= "padding-right: " + settings.padding.right + "; ";
                    if (settings.padding.bottom != '') css+= "padding-bottom: " + settings.padding.bottom + "; ";
                    if (settings.padding.left != '') css+= "padding-left: " + settings.padding.left + "; ";
                }
                if (typeof settings.margin !== 'undefined') {
                    if (settings.margin.top != '') css+= "margin-top: " + settings.margin.top + "; ";
                    if (settings.margin.right != '') css+= "margin-right: " + settings.margin.right + "; ";
                    if (settings.margin.bottom != '') css+= "margin-bottom: " + settings.margin.bottom + "; ";
                    if (settings.margin.left != '') css+= "margin-left: " + settings.margin.left + "; ";
                }
                if (typeof settings.font_weight !== 'undefined' && settings.font_weight != '') css+= "font-weight: " + settings.font_weight + "; ";
                if (typeof settings.font_size !== 'undefined' && settings.font_size != '') css+= "font-size: " + settings.font_size + "; ";
                if (typeof settings.line_height !== 'undefined' && settings.line_height != '') css+= "line-height: " + settings.line_height + "; ";
                if (typeof settings.color !== 'undefined') {
                    if (settings.color.hex != '') css+= "color: " + settings.color.hex + "; ";
                }
                if (typeof settings.text_shadow !== 'undefined' && settings.text_shadow != '') css+= "text-shadow: " + settings.text_shadow + "; ";
                if (typeof settings.text_align !== 'undefined' && settings.text_align != '') css+= "text-align: " + settings.text_align + "; ";
                if (typeof settings.font_style !== 'undefined' && settings.font_style != '') css+= "font-style: " + settings.font_style + "; ";
                if (typeof settings.text_decoration !== 'undefined' && settings.text_decoration != '') css+= "text-decoration: " + settings.text_decoration + "; ";
                if (typeof settings.background_color !== 'undefined') {
                    if (settings.background_color.hex != '') css+= "background-color: " + settings.background_color.hex + "; ";
                }
                if (typeof settings.background_image !== 'undefined' && settings.background_image != '') css+= "background-image: url(" + settings.background_image + "); ";
                if (typeof settings.background_size !== 'undefined' && settings.background_size != '') css+= "background-size: " + settings.background_size + "; ";
                if (typeof settings.background_position !== 'undefined' && settings.background_position != '') css+= "background-position: " + settings.background_position + "; ";
                if (typeof settings.background_repeat !== 'undefined' && settings.background_repeat != '') css+= "background-repeat: " + settings.background_repeat + "; ";
                if (typeof settings.background_attachment !== 'undefined' && settings.background_attachment != '') css+= "background-attachment: " + settings.background_attachment + "; ";
                if (typeof settings.box_shadow !== 'undefined' && settings.box_shadow != '') css+= "box-shadow: " + settings.box_shadow + "; ";

                if (typeof settings.border_radius !== 'undefined' && settings.border_radius > 0) css+= "border-radius: " + settings.border_radius + settings.border_radius_unit + "; ";
                if (typeof settings.border_width !== 'undefined' && settings.border_width > 0) {
                    css+= "border-width: " + settings.border_width + settings.border_width_unit + "; ";
                    if (typeof settings.border_style !== 'undefined' && settings.border_style != '') css+= "border-style: " + settings.border_style + "; ";
                    if (typeof settings.border_color !== 'undefined') {
                        if (settings.border_color.hex != '') css+= "border-color: " + settings.border_color.hex + "; ";
                    }
                }

                if (typeof settings.custom_css !== 'undefined' && settings.custom_css != '') {
                    var lines = settings.custom_css.split('\n');
                    for(var i = 0;i < lines.length;i++) {
                        var line = lines[i];
                        css += line.trim() + ';';
                    }
                }

                return css;
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

                    axios.put(url, params, {headers: self.headers})
                        .then(response => {
                            var result = JSON.parse(response.data);

                            if (result.success) {
                                self.page_id = parseInt(result['id']);
                                self.setAlert(translations.saved, 'success');
                            } else {
                                self.setAlert(result.message, 'error');
                            }
                            self.enableEdit = true;
                        })
                        .catch(e => {
                            self.errors.push(e);
                            self.enableEdit = true;
                        });
                }, 2);
            },
            setEditor: function(event) {
                event.target.parentNode.classList.toggle("d-block");
                this.createCookie('selected_editor', event.target.dataset.editor);
                this.selected_editor = event.target.dataset.editor;
                this.selected_editor_name = event.target.textContent;
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

<style lang="scss">

@import '../scss/components.scss';

.editor-nav {
    z-index: 10;
}

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

#content-editor {
    display: block;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    text-align: center;
}



#content-editor > button {
    margin: 0 auto;
    margin-top: 5rem;
    margin-bottom: 5rem;
}

.component.active,
.component-block.active,
.component-title.active,
.component-title2.active,
.component-text.active,
.component-text2.active,
.component-button.active,
.component-button2.active,
.component-image.active,
.component-image2.active {
    border: 2px dashed red !important;
}

.remove-element {
    display: none;
}

.component, .component-block {
    position: relative;
}

.component.active .component-functions,
.component-block.active .component-functions {
    display: inline-block;
    position: absolute;
    top: 0;
    right: 0;
    z-index: 2;
}

</style>
