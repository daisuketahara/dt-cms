<template>
    <v-container class="px-0">
        <div id="content-editor" class="mt-2">
            <v-container class="py-0" v-for="(element, index) in construct" v-bind:key="'element_'+index">
                <div v-if="element.selected === true" class="component-functions">
                    <v-btn-toggle v-model="row_settings" @change="row_settings=false">
                        <v-btn value="1" fab x-small v-if="index < construct.length-1" @click="moveElementDown(index)"><v-icon>far fa-chevron-down</v-icon></v-btn>
                        <v-btn value="2" fab x-small v-if="index > 0" @click="moveElementUp(index)"><v-icon>far fa-chevron-up</v-icon></v-btn>
                        <v-btn value="3" fab x-small @click="sheets.style = true;"><v-icon>fal fa-cogs</v-icon></v-btn>
                        <v-btn value="4" fab x-small @click="removeElement(index)"><v-icon>fal fa-trash-alt</v-icon></v-btn>
                    </v-btn-toggle>
                </div>
                <v-row v-if="element.type == 'text_left_image_right' || element.type == 'text_right_image_left'" :id="element.id" v-bind:class="{ component: true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                    <v-col cols="12" sm="6" class="wrap-content" :order-sm="element.type == 'text_left_image_right' ? 1 : 2">
                        <h3
                            v-if="element.parts.title.settings.display!='none'"
                            v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.title, index);"
                        >
                            {{ element.parts.title.content }}
                            <v-btn v-if="element.parts.title.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </h3>
                        <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-textarea': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                            <v-btn v-if="element.parts.text.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                            <richtext v-model="element.parts.text.content" :content="element.parts.text.content" :active="element.parts.text.selected"></richtext>
                        </div>
                        <a
                            v-if="element.parts.button.settings.display!='none'"
                            :data-href="element.parts.button.settings.href"
                            v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.button, index);"
                        >
                            {{element.parts.button.content}}
                            <v-btn v-if="element.parts.button.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </a>
                    </v-col>
                    <v-col
                        cols="12"
                        sm="6"
                        v-bind:class="{ 'component-image': true, active: element.parts.image.selected == true}"
                        :order-sm="element.type == 'text_left_image_right' ? 2 : 1"
                        v-on:click.stop="setActiveElement(element.parts.image, index);"
                    >
                        <v-btn v-if="element.parts.image.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                    </v-col>
                </v-row >
                <v-row v-else-if="element.type == 'text_text'" :id="element.id" v-bind:class="{ component: true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                    <v-col cols="12" sm="6" class="wrap-content">
                        <h3
                            v-if="element.parts.title.settings.display!='none'"
                            v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.title, index);
                        ">
                            {{ element.parts.title.content }}
                            <v-btn v-if="element.parts.title.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </h3>
                        <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-textarea': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                            <v-btn v-if="element.parts.text.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                            <richtext v-model="element.parts.text.content" :content="element.parts.text.content" :active="element.parts.text.selected"></richtext>
                        </div>
                        <a
                            v-if="element.parts.button.settings.display!='none'"
                            :data-href="element.parts.button.settings.href"
                            v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.button, index);"
                        >
                            {{element.parts.button.content}}
                            <v-btn v-if="element.parts.button.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </a>
                    </v-col>
                    <v-col cols="12" sm="6" class="wrap-content">
                        <h3
                            v-if="element.parts.title2.settings.display!='none'"
                            v-bind:class="{ 'component-title2': true, active: element.parts.title2.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.title2, index);
                        ">
                            {{ element.parts.title2.content }}
                            <v-btn v-if="element.parts.title2.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </h3>
                        <div v-if="element.parts.text2.settings.display!='none'" v-bind:class="{ 'component-textarea2': true, active: element.parts.text2.selected == true}" v-on:click.stop="setActiveElement(element.parts.text2, index);">
                            <v-btn v-if="element.parts.text2.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                            <richtext v-model="element.parts.text2.content" :content="element.parts.text2.content" :active="element.parts.text2.selected"></richtext>
                        </div>
                        <a
                            v-if="element.parts.button2.settings.display!='none'"
                            :data-href="element.parts.button2.settings.href"
                            v-bind:class="{ 'component-button2': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button2.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.button2, index);"
                        >
                            {{element.parts.button2.content}}
                            <v-btn v-if="element.parts.button2.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </a>
                    </v-col>
                </v-row>
                <v-row v-else-if="element.type == 'image_image'" :id="element.id" v-bind:class="{ 'component': true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                    <v-col
                        cols="12"
                        sm="6"
                        v-bind:class="{ 'component-image': true, active: element.parts.image.selected == true}"
                        v-on:click.stop="setActiveElement(element.parts.image, index);"
                    >
                        <v-btn v-if="element.parts.image.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                    </v-col>
                    <v-col
                        cols="12"
                        sm="6"
                        v-bind:class="{ 'component-image2': true, active: element.parts.image2.selected == true}"
                        v-on:click.stop="setActiveElement(element.parts.image2, index);"
                    >
                        <v-btn v-if="element.parts.image2.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                    </v-col>
                </v-row>
                <v-row v-else-if="element.type == 'text'" :id="element.id" class="" v-bind:class="{ 'component': true, 'component-text': true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                    <v-col cols="12">
                        <h3
                            v-if="element.parts.title.settings.display!='none'"
                            v-bind:class="{ 'component-title': true, active: element.parts.title.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.title, index);
                        ">
                            {{ element.parts.title.content }}
                            <v-btn v-if="element.parts.title.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </h3>
                        <div v-if="element.parts.text.settings.display!='none'" v-bind:class="{ 'component-textarea': true, active: element.parts.text.selected == true}" v-on:click.stop="setActiveElement(element.parts.text, index);">
                            <v-btn v-if="element.parts.text.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                            <richtext v-model="element.parts.text.content" :content="element.parts.text.content" :active="element.parts.text.selected"></richtext>
                        </div>
                        <a
                            v-if="element.parts.button.settings.display!='none'"
                            :data-href="element.parts.button.settings.href"
                            v-bind:class="{ 'component-button': true, btn: true, 'btn-lg': true, 'btn-secondary': true, active: element.parts.button.selected == true}"
                            :contenteditable="enableEdit"
                            v-on:click.stop="setActiveElement(element.parts.button, index);"
                        >
                            {{element.parts.button.content}}
                            <v-btn v-if="element.parts.button.selected" fab x-small class="btn-settings" @click="sheets.style = true;"><v-icon>fas fa-cogs</v-icon></v-btn>
                        </a>
                    </v-col>
                </v-row>
                <v-row v-else-if="element.type == 'block'" :id="element.id" class="" v-bind:class="{ 'component': true, 'component-block': true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                </v-row>
                <v-row v-else-if="element.type == 'html'" :id="element.id" class="" v-bind:class="{ 'component': true, 'component-html': true, active: element.selected == true}" v-on:click.stop="setActiveElement(element, index);">
                    <v-col cols="12" v-html="element.html"></v-col>
                    <div class="component-html-overlay">
                        <v-btn @click="html_editor=true">{{ translations.edit || 'Edit' }}</v-btn>
                    </div>
                </v-row>
            </v-container>
            <div v-if="enableEdit === true" class="text-center mt-3 py-5">
                <v-btn large color="warning" @click="selectElement"><i class="fas fa-plus"></i> {{translations.add_element}}</v-btn>
            </div>
        </div>
        <v-bottom-sheet hide-overlay v-model="sheets.elements" :dark="darkmode">
            <v-sheet class="text-center" :dark="darkmode">
                <v-btn
                    class="close-bottom-sheet"
                    fab
                    small
                    @click="sheets.elements=false"
                >
                  <v-icon>fal fa-times</v-icon>
                </v-btn>
                <v-slide-group
                  v-model="settings_slide"
                  class="pa-4"
                  active-class="success"
                  show-arrows
                >
                    <v-slide-item v-for="(element, index) in elements" v-if="element.active" :key="index">
                        <v-card width="300" height="210" class="px-2" ripple @click="addElement(element.type)">
                            <v-img :src="element.image" :alt="element.title" class="img-fluid w-100 mb-1"></v-img>
                            <h4>
                                {{element.title}}
                            </h4>
                        </v-card>
                    </v-slide-item>
                </v-slide-group>
            </v-sheet>
        </v-bottom-sheet>
        <v-bottom-sheet hide-overlay v-model="sheets.style" :dark="darkmode">
            <v-sheet class="text-center" :dark="darkmode">
                <v-btn
                    class="close-bottom-sheet"
                    fab
                    small
                    @click="sheets.style=false"
                >
                  <v-icon>fal fa-times</v-icon>
                </v-btn>
                <v-chip-group mandatory active-class="primary--text" v-model="settings_filter">
                    <v-chip small value="all">
                        {{ translations.all || 'All' }}
                    </v-chip>
                    <v-chip
                        small
                        value="font"
                        v-if="typeof settings.font_weight !== 'undefined'"
                    >
                        <v-avatar left><v-icon>far fa-font-case</v-icon></v-avatar left>
                        {{ translations.font || 'Font' }}
                    </v-chip>
                    <v-chip
                        small
                        value="background"
                        v-if="typeof settings.background_color !== 'undefined'"
                    >
                        <v-avatar left><v-icon>fas fa-image</v-icon></v-avatar left>
                        {{ translations.background || 'Background' }}
                    </v-chip>
                    <v-chip
                        small
                        value="dimensions"
                        v-if="typeof settings.width !== 'undefined'"
                    >
                        <v-avatar left><v-icon>fal fa-ruler-combined</v-icon></v-avatar left>
                        {{ translations.dimensions || 'Dimensions' }}
                    </v-chip>
                    <v-chip
                        small
                        value="display"
                        v-if="typeof settings.display !== 'undefined'"
                    >
                        <v-avatar left><v-icon>fal fa-eye</v-icon></v-avatar left>
                        {{ translations.display || 'Display' }}
                    </v-chip>
                    <v-chip
                        small
                        value="color"
                        v-if="typeof settings.background_color !== 'undefined'"
                    >
                        <v-avatar left><v-icon>fas fa-tint</v-icon></v-avatar left>
                        {{ translations.color || 'Color' }}
                    </v-chip>
                    <v-chip
                        small
                        value="border"
                    >
                        <v-avatar left><v-icon>far fa-border-outer</v-icon></v-avatar left>
                        {{ translations.border || 'Border' }}
                    </v-chip>
                    <v-chip
                        small
                        value="custom"
                    >
                        <v-avatar left><v-icon>far fa-brackets-curly</v-icon></v-avatar left>
                        {{ translations.custom || 'Custom' }}
                    </v-chip>
                </v-chip-group>
                <v-slide-group
                  v-model="settings_slide"
                  class="pa-4"
                  active-class="success"
                  show-arrows
                >
                    <v-slide-item v-if="(settings_filter == 'dimensions' || settings_filter == 'all') && typeof settings.width !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{translations.dimensions || 'Dimensions'}}</label>
                            <v-row v-if="typeof settings.width !== 'undefined'" class="mb-1">
                                <v-col cols="6"><v-text-field hide-details dense :label="translations.width || 'Width'" v-model="settings.width" v-on:change="generateCss"></v-text-field></v-col>
                                <v-col cols="6"><v-text-field hide-details dense :label="translations.height || 'Height'" v-model="settings.height" v-on:change="generateCss"></v-text-field></v-col>
                            </v-row>
                            <v-row v-if="typeof settings.min_width !== 'undefined'" class="mb-1">
                                <v-col cols="6"><v-text-field hide-details dense :label="translations.min_w || 'Min W'" v-model="settings.min_width" v-on:change="generateCss"></v-text-field></v-col>
                                <v-col cols="6"><v-text-field hide-details dense :label="translations.min_h || 'Min H'" v-model="settings.min_height" v-on:change="generateCss"></v-text-field></v-col>
                            </v-row>
                            <v-row v-if="typeof settings.max_width !== 'undefined'" class="row mb-1">
                                <v-col cols="6"><v-text-field hide-details dense :label="translations.max_w || 'Max W'" v-model="settings.max_width" v-on:change="generateCss"></v-text-field></v-col>
                                <v-col cols="6"><v-text-field hide-details dense :label="translations.max_h || 'Max H'" v-model="settings.max_height" v-on:change="generateCss"></v-text-field></v-col>
                            </v-row>
                        </v-card>
                    </v-slide-item>
                    <v-slide-item v-if="(settings_filter == 'dimensions' || settings_filter == 'all') && typeof settings.margin !== 'undefined' && settings.padding !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{translations.padding_margin || 'Padding & Margin'}}</label>
                            <div class="spacing-grid mb-3">
                                <div class="row">
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                    <div class="col-4"><input type="int" v-model="settings.margin.top" v-on:change="generateCss"></div>
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                </div>
                                <div class="row">
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                    <div class="col-4"><input type="int" v-model="settings.padding.top" v-on:change="generateCss"></div>
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                </div>
                                <div class="row">
                                    <div class="col-2"><input type="int" v-model="settings.margin.left" v-on:change="generateCss"></div>
                                    <div class="col-2"><input type="int" v-model="settings.padding.left" v-on:change="generateCss"></div>
                                    <div class="col-4"></div>
                                    <div class="col-2"><input type="int" v-model="settings.padding.right" v-on:change="generateCss"></div>
                                    <div class="col-2"><input type="int" v-model="settings.margin.right" v-on:change="generateCss"></div>
                                </div>
                                <div class="row">
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                    <div class="col-4"><input type="int" v-model="settings.padding.bottom" v-on:change="generateCss"></div>
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                </div>
                                <div class="row">
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                    <div class="col-4"><input type="int" v-model="settings.margin.bottom" v-on:change="generateCss"></div>
                                    <div class="col-2"></div>
                                    <div class="col-2"></div>
                                </div>
                            </div>
                        </v-card>
                    </v-slide-item>
                    <!-- Display -->
                    <v-slide-item v-if="(settings_filter == 'display' || settings_filter == 'all') && typeof settings.display !== 'undefined'"  class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <div v-if="typeof settings.display !== 'undefined'" class="mb-2">
                                <label>{{translations.display || 'Display'}}</label>
                                <v-btn-toggle mandatory v-model="settings.display" @change="generateCss()">
                                    <v-btn small value="block">
                                        <v-icon small>fal fa-eye</v-icon>
                                    </v-btn>
                                    <v-btn small value="none">
                                        <v-icon small>fal fa-eye-slash</v-icon>
                                    </v-btn>
                                    <v-btn small value="auto">
                                        Auto
                                    </v-btn>
                                </v-btn-toggle>
                            </div>
                            <div v-if="typeof settings.overflow !== 'undefined'" class="mb-2">
                                <label>{{translations.overflow || 'Overflow'}}</label>
                                <v-btn-toggle mandatory v-model="settings.overflow" @change="generateCss()">
                                    <v-btn small value="visible">
                                        <v-icon small>fal fa-eye</v-icon>
                                    </v-btn>
                                    <v-btn small value="hidden">
                                        <v-icon small>fal fa-eye-slash</v-icon>
                                    </v-btn>
                                    <v-btn small value="scroll">
                                        <v-icon small>fal fa-arrows-v</v-icon>
                                    </v-btn>
                                    <v-btn small value="auto">
                                        Auto
                                    </v-btn>
                                </v-btn-toggle>
                            </div>
                        </v-card>
                    </v-slide-item>
                    <!-- Text -->
                    <v-slide-item v-if="(settings_filter == 'font' || settings_filter == 'all') && typeof settings.font_weight !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{translations.font || 'Font'}}</label>
                            <div v-if="typeof settings.font_weight !== 'undefined'" class="mb-1">
                                <v-btn-toggle mandatory v-model="settings.font_weight" @change="generateCss()">
                                    <v-btn small value="light">
                                        <v-icon small>fal fa-font</v-icon>
                                    </v-btn>
                                    <v-btn small value="normal">
                                        <v-icon small>far fa-font</v-icon>
                                    </v-btn>
                                    <v-btn small value="bold">
                                        <v-icon small>fas fa-font</v-icon>
                                    </v-btn>
                                    <v-btn small value="auto">
                                        Auto
                                    </v-btn>
                                </v-btn-toggle>
                            </div>
                            <div v-if="typeof settings.text_align !== 'undefined'" class="mb-1">
                                <v-btn-toggle mandatory v-model="settings.text_align" @change="generateCss()">
                                    <v-btn small value="left">
                                        <v-icon small>fal fa-align-left</v-icon>
                                    </v-btn>
                                    <v-btn small value="center">
                                        <v-icon small>fal fa-align-center</v-icon>
                                    </v-btn>
                                    <v-btn small value="right">
                                        <v-icon small>fal fa-align-right</v-icon>
                                    </v-btn>
                                    <v-btn small value="justify">
                                        <v-icon>fal fa-align-justify</v-icon>
                                    </v-btn>
                                    <v-btn small value="auto">
                                        Auto
                                    </v-btn>
                                </v-btn-toggle>
                            </div>
                            <v-row v-if="typeof settings.font_style !== 'undefined'" no-gutters dense class="mb-1">
                                <v-col cols="5">
                                    <v-btn-toggle mandatory v-model="settings.font_style" @change="generateCss()">
                                        <v-btn small value="normal">
                                            <v-icon small>fal fa-font</v-icon>
                                        </v-btn>
                                        <v-btn small value="italic">
                                            <v-icon small>fal fa-italic</v-icon>
                                        </v-btn>
                                    </v-btn-toggle>
                                </v-col>
                                <v-col cols="7">
                                    <v-btn-toggle mandatory v-model="settings.text_decoration" @change="generateCss()">
                                        <v-btn small value="inherit">
                                            <v-icon small>fal fa-font</v-icon>
                                        </v-btn>
                                        <v-btn small value="strikethrough">
                                            <v-icon small>fal fa-strikethrough</v-icon>
                                        </v-btn>
                                        <v-btn small value="underline" >
                                            <v-icon small>fal fa-underline</v-icon>
                                        </v-btn>
                                    </v-btn-toggle>
                                </v-col>
                            </v-row>
                            <div v-if="typeof settings.font_size !== 'undefined'">
                                <v-slider
                                    v-if="typeof settings.font_size !== 'undefined'"
                                    prepend-icon="far fa-text-size"
                                    v-model="settings.font_size"
                                    thumb-label
                                    hide-details
                                    dense
                                    min="6"
                                    max="100"
                                    @mouseup="generateCss()"
                                ></v-slider>
                            </div>
                            <div v-if="typeof settings.line_height !== 'undefined'">
                                <v-slider
                                    v-if="typeof settings.line_height !== 'undefined'"
                                    prepend-icon="far fa-line-height"
                                    v-model="settings.line_height"
                                    thumb-label
                                    hide-details
                                    dense
                                    min="1"
                                    max="100"
                                    @mouseup="generateCss()"
                                ></v-slider>
                            </div>
                        </v-card>
                    </v-slide-item>
                    <!-- Text color -->
                    <v-slide-item v-if="(settings_filter == 'color' || settings_filter == 'font' || settings_filter == 'all') && typeof settings.color !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>
                                {{ translations.text_color || 'Text color' }}
                                <v-switch class="float-right mt-0" dense hide-details v-model="settings.color_enabled" @change="generateCss"></v-switch>
                            </label>
                            <v-color-picker
                                dot-size="25"
                                swatches-max-height="100"
                                hide-inputs
                                :disabled="!settings.color_enabled"
                                v-model="settings.color"
                                @update:color="generateCss"
                                width="300"
                            ></v-color-picker>
                        </v-card>
                    </v-slide-item>
                    <!-- Background color -->
                    <v-slide-item v-if="(settings_filter == 'background' || settings_filter == 'all') && typeof settings.background_image !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{ translations.background_image || 'Background image' }}</label>
                            <v-img
                                width="300"
                                max-height="180"
                                :src="settings.background_image"
                                @click="file_modal=true"
                            ></v-img>
                            <v-btn
                                v-if="settings.background_image != example.bg_image"
                                block
                                @click="settings.background_image = example.bg_image"
                            >{{ translations.remove_background || "Remove background" }}</v-btn>
                            <v-btn
                                v-else
                                block
                                @click="file_modal=true"
                            >{{ translations.set_background || "Set background" }}</v-btn>
                        </v-card>
                    </v-slide-item>
                    <!-- Background -->
                    <v-slide-item v-if="(settings_filter == 'background' || settings_filter == 'all') && typeof settings.background_color !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{translations.background_image_settings || 'Background image settings'}}</label>
                            <v-select
                                :label="translations.size || 'Size'"
                                v-model="settings.background_size"
                                :items="[
                                    { value: 'cover', text: translations.cover || 'Cover' },
                                    { value: 'contain', text: translations.contain || 'Contain' },
                                    { value: 'auto', text: translations.auto || 'Auto' }
                                ]"
                                hide-details
                                dense
                                class="mb-4"
                                @change="generateCss"
                            ></v-select>
                            <v-select
                                :label="translations.position || 'Position'"
                                v-model="settings.background_position"
                                :items="[
                                    { value: 'top left', text: translations.top_left || 'top left' },
                                    { value: 'top center', text: translations.top_center || 'top center' },
                                    { value: 'top right', text: translations.top_right || 'top right' },
                                    { value: 'center left', text: translations.center_left || 'center left' },
                                    { value: 'center center', text: translations.center_center || 'center center' },
                                    { value: 'center right', text: translations.center_right || 'center right' },
                                    { value: 'bottom left', text: translations.bottom_left || 'bottom left' },
                                    { value: 'bottom center', text: translations.bottom_center || 'bottom center' },
                                    { value: 'bottom right', text: translations.bottom_right || 'bottom right' },
                                    { value: 'custom', text: translations.custom || 'Custom' }
                                ]"
                                hide-details
                                dense
                                class="mb-4"
                                @change="generateCss"
                            ></v-select>
                            <v-row v-if="settings.background_position == 'custom'" no-gutters dense class="mb-4">
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.top || 'Top'"
                                        v-model="settings.background_position_top"
                                        hide-details
                                        dense
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.right || 'Right'"
                                        v-model="settings.background_position_right"
                                        hide-details
                                        dense
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.bottom || 'Bottom'"
                                        v-model="settings.background_position_bottom"
                                        hide-details
                                        dense
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.left || 'Left'"
                                        v-model="settings.background_position_left"
                                        hide-details
                                        dense
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-select
                                :label="translations.tile || 'Tile'"
                                v-model="settings.background_repeat"
                                :items="[
                                    { value: 'repeat', text: translations.repeat || 'Repeat' },
                                    { value: 'repeat-x', text: translations.horizontal || 'Horizontal' },
                                    { value: 'repeat-y', text: translations.vertical || 'Vertical' },
                                    { value: 'no-repeat', text: translations.no_repeat || 'No repeat' }
                                ]"
                                hide-details
                                dense
                                class="mb-4"
                                @change="generateCss"
                            ></v-select>
                            <v-select
                                :label="translations.fixed || 'Fixed'"
                                v-model="settings.background_attachment"
                                :items="[
                                    { value: 'scroll', text: translations.no || 'No' },
                                    { value: 'fixed', text: translations.yes || 'Yes' }
                                ]"
                                hide-details
                                dense
                                class="mb-4"
                                @change="generateCss"
                            ></v-select>
                        </v-card>
                    </v-slide-item>
                    <!-- Background color -->
                    <v-slide-item v-if="(settings_filter == 'color' || settings_filter == 'background' || settings_filter == 'all') && typeof settings.background_color !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>
                                <v-switch class="float-right mt-0" dense hide-details v-model="settings.background_color_enabled" @change="generateCss"></v-switch>
                                {{ translations.background_color || 'Background color' }}
                            </label>
                            <v-color-picker
                                dot-size="25"
                                swatches-max-height="100"
                                hide-inputs
                                :disabled="!settings.background_color_enabled"
                                v-model="settings.background_color"
                                @update:color="generateCss"
                            ></v-color-picker>
                        </v-card>
                    </v-slide-item>
                    <!-- Background -->
                    <v-slide-item v-if="(settings_filter == 'border' || settings_filter == 'all') && typeof settings.background_color !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{ translations.border || 'Border' }}</label>
                            <v-row no-gutters dense class="mb-4">
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.top || 'Top'"
                                        v-model="settings.border_top"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.right || 'Right'"
                                        v-model="settings.border_right"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.bottom || 'Bottom'"
                                        v-model="settings.border_bottom"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.left || 'Left'"
                                        v-model="settings.border_left"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                            </v-row><label>{{ translations.border_radius || 'Border radius' }}</label>
                            <v-row no-gutters dense class="mb-4">
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.top || 'Top'"
                                        v-model="settings.border_radius_1"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.right || 'Right'"
                                        v-model="settings.border_radius_2"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.bottom || 'Bottom'"
                                        v-model="settings.border_radius_3"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                                <v-col rows="3">
                                    <v-text-field
                                        :label="translations.left || 'Left'"
                                        v-model="settings.border_radius_4"
                                        hide-details
                                        dense
                                        @change="generateCss"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-select
                                :label="translations.style || 'Style'"
                                v-model="settings.border_style"
                                :items="[
                                    { value: 'solid', text: translations.repeat || 'Solid' },
                                    { value: 'dashed', text: translations.horizontal || 'Dashed' },
                                    { value: 'dotted', text: translations.vertical || 'Dotted' },
                                    { value: 'double', text: translations.no_repeat || 'Double' }
                                ]"
                                hide-details
                                dense
                                class="mb-4"
                                @change="generateCss"
                            ></v-select>
                        </v-card>
                    </v-slide-item>
                    <v-slide-item v-if="(settings_filter == 'color' || settings_filter == 'border' || settings_filter == 'all') && typeof settings.border_color !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>
                                <v-switch class="float-right mt-0" dense hide-details v-model="settings.border_color_enabled" @change="generateCss"></v-switch>
                                {{ translations.border_color || 'Border color' }}
                            </label>
                            <v-color-picker
                                dot-size="25"
                                swatches-max-height="100"
                                hide-inputs
                                :disabled="!settings.border_color_enabled"
                                v-model="settings.border_color"
                                @update:color="generateCss"
                            ></v-color-picker>
                        </v-card>
                    </v-slide-item>
                    <!-- Background -->
                    <v-slide-item v-if="(settings_filter == 'custom' || settings_filter == 'all') && typeof settings.custom_css !== 'undefined'" class="settings-block">
                        <v-card width="300" height="240" class="px-2">
                            <label>{{ translations.custom_css || 'Custom CSS' }}</label>
                            <codemirror v-model="settings.custom_css" :options="cmCssOptions" height="200" @input="generateCss"></codemirror>
                        </v-card>
                    </v-slide-item>
                </v-slide-group>
            </v-sheet>
        </v-bottom-sheet>
        <v-bottom-sheet hide-overlay v-model="html_editor" :dark="darkmode">
            <v-sheet :dark="darkmode">
                <v-btn
                    class="close-bottom-sheet"
                    fab
                    small
                    @click="html_editor=false"
                >
                  <v-icon>fal fa-times</v-icon>
                </v-btn>
                <codemirror v-model="active_element.html" :options="cmHtmlOptions" @input="generateCss"></codemirror>
            </v-sheet>
        </v-bottom-sheet>
        <v-bottom-sheet hide-overlay v-model="file_modal" :dark="darkmode" scrollable>
             <v-card>
                 <v-btn
                     class="close-bottom-sheet"
                     fab
                     small
                     @click="file_modal=false"
                 >
                   <v-icon>fal fa-times</v-icon>
                 </v-btn>
                 <v-card-text>
                    <filemanager :select="true" @choosen="setBackgroundImage"></filemanager>
                </v-card-text>
            </v-card>
        </v-bottom-sheet>
    </v-container>
</template>

<script>

    export default {

        name: "Editor",
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "X-AUTH-TOKEN" : this.$cookies.get('admintoken')
                },
                sheets: {
                    elements: false,
                    style: false,
                },
                settings_filter: 'all',
                settings_slide: null,
                settings: {},
                row_settings: false,
                file_modal: false,
                elements: {},
                construct: [],
                construct_css: '',
                enableEdit: true,
                active_element: false,
                example: {},
                html_editor: false,
                cmCssOptions: {
                    tabSize: 4,
                    theme: 'base16-light',
                    mode: 'text/css',
                    lineNumbers: true,
                    height: 164,
                },
                cmJsOptions: {
                    tabSize: 4,
                    theme: 'base16-light',
                    mode: 'text/javascript',
                    lineNumbers: true,
                },
                cmHtmlOptions: {
                    tabSize: 4,
                    theme: 'base16-light',
                    mode: 'text/html',
                    lineNumbers: true,
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
            this.getExample();
            this.getElements();

            if (typeof this.$attrs.value != 'undefined' && this.$attrs.value.length > 0) {

                var construct = JSON.parse(this.$attrs.value);
                this.construct = construct;
                this.setElementInactive();
                this.generateCss();
            }
        },
        methods: {
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
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0,
                    },
                    margin: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0,
                    },
                    font_weight: 'normal',
                    font_size: 14,
                    line_height: 10,
                    color: {},
                    color_enabled: false,
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_color_enabled: false,
                    background_image: this.example.bg_image,
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_color_enabled: false,
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
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0,
                    },
                    margin: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0,
                    },
                    tag: 'h3',
                    font_weight: 'bold',
                    font_size: 32,
                    line_height: 48,
                    color: {},
                    color_enabled: false,
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_color_enabled: false,
                    background_image: this.example.bg_image,
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_color_enabled: false,
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
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0,
                    },
                    margin: {
                        top: 0,
                        right: 0,
                        bottom: '15px',
                        left: 0,
                    },
                    font_weight: 'normal',
                    font_size: 14,
                    line_height: 10,
                    color: {},
                    color_enabled: false,
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_color_enabled: false,
                    background_image: this.example.bg_image,
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_color_enabled: false,
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
                        top: '8px',
                        right: '25px',
                        bottom: '8px',
                        left: '25px',
                    },
                    margin: {
                        top: 0,
                        right: 0,
                        bottom: '15px',
                        left: 0,
                    },
                    font_weight: 'normal',
                    font_size: 16,
                    line_height: 24,
                    color: {},
                    color_enabled: false,
                    text_shadow: '',
                    text_align: '',
                    font_style: '',
                    text_decoration: '',
                    background_color: {},
                    background_color_enabled: false,
                    background_image: this.example.bg_image,
                    background_size: '',
                    background_position: '',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: '',
                    background_attachment: '',
                    box_shadow: '',
                    border_color: {},
                    border_color_enabled: false,
                    border_width: 0,
                    border_width_unit: 'px',
                    border_radius: 4,
                    border_radius_unit: 'px',
                    border_style: 'solid',
                    custom_css: '',
                };
                var image_settings = {
                    background_color: {},
                    background_color_enabled: false,
                    background_image: this.example.bg_image,
                    background_size: 'cover',
                    background_position: 'center center',
                    background_position_x: 0,
                    background_position_y: 0,
                    background_repeat: 'repeat',
                    background_attachment: 'scroll',
                    border_color: {},
                    border_color_enabled: false,
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
                        title: this.translations.text_text || 'Text Text',
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
                        title: this.translations.image_image || 'Image Image',
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
                    text: {
                        type: 'text',
                        title: this.translations.text,
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
                    block: {
                        type: 'block',
                        title: this.translations.block,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        parts: {
                        }
                    },
                    html: {
                        type: 'html',
                        title: this.translations.html,
                        image: this.example.bg_image,
                        active: true,
                        selected: false,
                        settings: component_settings,
                        html: '',
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
                        title: this.translations.hero || 'Hero',
                        image: this.example.bg_image,
                        active: false,
                        selected: false,
                    }
                };
            },
            selectElement: function(event) {
                this.sheets.elements = true;
            },
            addElement: function(new_element) {

                if (!Date.now) Date.now = function() { return new Date().getTime(); }
                var timestamp = Math.floor(Date.now() / 1000);

                var element = this.copyElement(this.elements[new_element]);
                element.id = 'component-'+timestamp;
                this.construct.push(element);

                this.generateCss();

                this.sheets.elements = false;
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
                this.active_element = element;
                //this.sheets.style = true;
                this.settings = element.settings;
                this.generateCss();
            },
            setElementInactive: function() {
                // Set all elements inactive
                for (var i = 0; i < this.construct.length; i++) {
                    this.construct[i].selected = false;

                    var parts = this.construct[i].parts;
                    for (var x in parts) {

                        if (parts[x].selected && document.getElementById(this.construct[i].id) != null) {

                            if (typeof document.getElementById(this.construct[i].id).getElementsByClassName('component-'+x)[0] != typeof undefined)
                                parts[x].content = document.getElementById(this.construct[i].id).getElementsByClassName('component-'+x)[0].textContent.replace(/(\r\n|\n|\r)/gm,"").trim();

                        }
                        parts[x].selected = false;
                    }
                }

                this.$emit('input', JSON.stringify(this.construct));
                this.settings = {};
            },
            removeElement: function(index) {
                this.construct.splice(index, 1);
                this.generateCss();
                this.active_element = false;
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
                this.$emit('input', JSON.stringify(this.construct));
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
                if (typeof settings.font_size !== 'undefined' && settings.font_size != '') {
                    css+= "font-size: " + settings.font_size + "px; ";
                    if (typeof settings.line_height !== 'undefined' && settings.line_height != '') css+= "line-height: " + (settings.font_size + settings.line_height) + "px; ";
                }
                if (typeof settings.color !== 'undefined' && settings.color_enabled) {
                    if (settings.color.hex != '') css+= "color: " + settings.color.hex + "; ";
                }
                if (typeof settings.text_shadow !== 'undefined' && settings.text_shadow != '') css+= "text-shadow: " + settings.text_shadow + "; ";
                if (typeof settings.text_align !== 'undefined' && settings.text_align != '') css+= "text-align: " + settings.text_align + "; ";
                if (typeof settings.font_style !== 'undefined' && settings.font_style != '') css+= "font-style: " + settings.font_style + "; ";
                if (typeof settings.text_decoration !== 'undefined' && settings.text_decoration != '') css+= "text-decoration: " + settings.text_decoration + "; ";
                if (typeof settings.background_color !== 'undefined' && settings.background_color_enabled) {
                    if (settings.background_color.hex != '') css+= "background-color: " + settings.background_color.hex + "; ";
                }
                if (typeof settings.background_image !== 'undefined' && settings.background_image != '' && settings.background_image != '/img/img-placeholder.png') css+= "background-image: url(" + settings.background_image + "); ";
                if (typeof settings.background_size !== 'undefined' && settings.background_size != '') css+= "background-size: " + settings.background_size + "; ";
                if (typeof settings.background_position !== 'undefined' && settings.background_position != '') css+= "background-position: " + settings.background_position + "; ";
                if (typeof settings.background_repeat !== 'undefined' && settings.background_repeat != '') css+= "background-repeat: " + settings.background_repeat + "; ";
                if (typeof settings.background_attachment !== 'undefined' && settings.background_attachment != '') css+= "background-attachment: " + settings.background_attachment + "; ";
                if (typeof settings.box_shadow !== 'undefined' && settings.box_shadow != '') css+= "box-shadow: " + settings.box_shadow + "; ";

                if (typeof settings.border_radius !== 'undefined' && settings.border_radius > 0) css+= "border-radius: " + settings.border_radius + settings.border_radius_unit + "; ";
                if (typeof settings.border_width !== 'undefined' && settings.border_width > 0) {
                    css+= "border-width: " + settings.border_width + settings.border_width_unit + "; ";
                    if (typeof settings.border_style !== 'undefined' && settings.border_style != '') css+= "border-style: " + settings.border_style + "; ";
                    if (typeof settings.border_color !== 'undefined' && settings.border_color_enabled) {
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
            setBackgroundImage(image) {
                this.settings.background_image = image;
                this.generateCss();
                this.file_modal = false;

            },
        },
        watch: {
            row_settings: function(newVal, oldVal) {
                this.row_settings = false;
            },
        },
    }
</script>

<style lang="scss">

    @import '../../scss/components.scss';

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
        border: 3px dashed rgba(255, 255, 255, 0.7);
        text-align: center;

        > .container {
            position: relative;
        }
    }

    #content-editor > button {
        margin: 0 auto;
        margin-top: 5rem;
        margin-bottom: 5rem;
    }

    .component {

        position: relative;

        .component-block,
        .component-text,
        .component-title,
        .component-title2,
        .component-title3,
        .component-title4,
        .component-textarea,
        .component-textarea2,
        .component-textarea3,
        .component-textarea4,
        .component-button,
        .component-button2,
        .component-button3,
        .component-button4,
        .component-image,
        .component-image2 {
            position: relative;
            border: 1px dashed #c4c3c4 !important;
        }

        .component-image,
        .component-image2,
        .component-image3,
        .component-image4 {
            background-image: url(/img/img-placeholder.png);
        }

        .btn-settings {
            position: absolute;
            right: -16px;
            top: -16px;
        }
    }

    .component.active,
    .component-text.active,
    .component-block.active,
    .component-title.active,
    .component-title2.active,
    .component-title3.active,
    .component-title4.active,
    .component-textarea.active,
    .component-textarea2.active,
    .component-textarea3.active,
    .component-textarea4.active,
    .component-button.active,
    .component-button2.active,
    .component-button3.active,
    .component-button4.active,
    .component-image.active,
    .component-image2.active {
        border: 2px dashed red !important;
    }

    .component-html {

        position: relative;
        min-height: 200px;

        .component-html-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.2);
            text-align: center;
        }

    }

    .remove-element {
        display: none;
    }

    .component-functions {
        display: inline-block;
        position: absolute;
        top: -32px;
        right: 0;
        width: 100%;
        text-align: center;
        z-index: 2;
    }

    .v-chip .v-icon {
        font-size: 16px !important;
    }
    .v-slider__thumb-label {
        color: #000000;
    }

    .settings-block {
        vertical-align: top;
        text-align: left;

        label {
            display: block !important;
            text-align: left !important;
        }

        .v-color-picker {
            width: 300px;
        }

        .CodeMirror {
            height: 200px !important;
        }
    }

    .spacing-grid {
        background-color: #404040;
        padding: 5px;
        padding-left: 20px;
        padding-right: 20px;

        input {
            background-color: transparent !important;
            color: white !important;
            border: none !important;
            text-align: center;
            font-size: 0.7rem !important;
            width: 100% !important;
            padding-top: 7px !important;
            padding-bottom: 7px !important;
        }

        .col-2, .col-4 {
            padding: 0 !important;
        }

        .row .col-2:first-child {
        }

        .row .col-2:last-child {
        }

        .row:first-child {

            .col-2, .col-4 {
                background-color: rgb(94, 94, 94);
            }
        }

        .row:nth-child(2) {
            .col-2, .col-4 {
                background-color: rgb(124, 124, 124);
            }
            .col-2:first-child, .col-2:last-child {
                background-color: rgb(94, 94, 94);
            }
        }

        .row:nth-child(3) {
            .col-2 {
                background-color: rgb(124, 124, 124);
            }
            .col-2:first-child, .col-2:last-child {
                background-color: rgb(94, 94, 94);
            }
        }

        .row:nth-child(4) {
            .col-2, .col-4 {
                background-color: rgb(124, 124, 124);
            }
            .col-2:first-child, .col-2:last-child {
                background-color: rgb(94, 94, 94);
            }
        }

        .row:last-child {

            .col-2, .col-4 {
                background-color: rgb(94, 94, 94);
            }

        }
    }

    .v-application .v-slide-group.pa-4 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .v-sheet {

        position: relative;

        .close-bottom-sheet {
            position: absolute;
            top: -20px;
            right: 20px;
            z-index: 2;
        }
    }


</style>
