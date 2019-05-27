<template>
    <div>
        <h4>{{translations.size || 'Size'}}</h4>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.width || 'Width'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.width"></div>
            <div class="col-3"><label>{{translations.height || 'Height'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.height"></div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.min_w || 'Min W'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.min_width"></div>
            <div class="col-3"><label>{{translations.min_h || 'MinH'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.min_height"></div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.max_w || 'Max W'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.max_width"></div>
            <div class="col-3"><label>{{translations.max_h || 'Max H'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.max_height"></div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.overflow || 'Overflow'}}</label></div>
            <div class="col-9">
                <div class="btn-group w-100" role="group" aria-label="Basic example">
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.overflow == 'visible' }" v-on:click.prevent="settings.overflow = 'visible'"><i class="fal fa-eye"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.overflow == 'hidden' }" v-on:click.prevent="settings.overflow = 'hidden'"><i class="fal fa-eye-slash"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.overflow == 'scroll' }" v-on:click.prevent="settings.overflow = 'scroll'"><i class="fal fa-arrows-v"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.overflow == 'auto' }" v-on:click.prevent="settings.overflow = 'auto'">Auto</button>
                </div>
            </div>
        </div>

        <h4>{{translations.spacing || 'Spacing'}}</h4>
        <div v-if="typeof settings.margin !== 'undefined' && settings.padding !== 'undefined'" class="spacing-grid mb-3">
            <div class="row">
                <div class="col-2"></div>
                <div class="col-2"></div>
                <div class="col-4"><input type="int" class="form-control form-control-sm" v-model="settings.margin.top" v-on:change="generateCss"></div>
                <div class="col-2"></div>
                <div class="col-2"></div>
            </div>
            <div class="row">
                <div class="col-2"></div>
                <div class="col-2"></div>
                <div class="col-4"><input type="int" class="form-control form-control-sm" v-model="settings.padding.top"></div>
                <div class="col-2"></div>
                <div class="col-2"></div>
            </div>
            <div class="row">
                <div class="col-2"><input type="int" class="form-control form-control-sm" v-model="settings.margin.left"></div>
                <div class="col-2"><input type="int" class="form-control form-control-sm" v-model="settings.padding.left"></div>
                <div class="col-4"></div>
                <div class="col-2"><input type="int" class="form-control form-control-sm" v-model="settings.padding.right"></div>
                <div class="col-2"><input type="int" class="form-control form-control-sm" v-model="settings.margin.right"></div>
            </div>
            <div class="row">
                <div class="col-2"></div>
                <div class="col-2"></div>
                <div class="col-4"><input type="int" class="form-control form-control-sm" v-model="settings.padding.bottom"></div>
                <div class="col-2"></div>
                <div class="col-2"></div>
            </div>
            <div class="row">
                <div class="col-2"></div>
                <div class="col-2"></div>
                <div class="col-4"><input type="int" class="form-control form-control-sm" v-model="settings.margin.bottom"></div>
                <div class="col-2"></div>
                <div class="col-2"></div>
            </div>
        </div>



        <h4>{{translations.typography || 'Typography'}}</h4>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.weight || 'Weight'}}</label></div>
            <div class="col-9">
                <div class="input-group">
                    <select class="form-control form-control-sm" v-model="settings.font_weight">
                        <option value="inherit">{{translations.inherit || 'Inherit'}}</option>
                        <option value="300">{{translations.light || 'Light'}}</option>
                        <option value="500">{{translations.normal || 'Normal'}}</option>
                        <option value="700">{{translations.medium || 'Medium'}}</option>
                        <option value="900">{{translations.Bold || 'Bold'}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.size || 'Size'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.font_size"></div>
            <div class="col-3"><label>{{translations.height || 'Height'}}</label></div>
            <div class="col-3"><input type="int" class="form-control form-control-sm" v-model="settings.line_height"></div>
        </div>

        <div v-if="typeof settings.color !== 'undefined'" class="row mb-1" style="z-index: 2;">
            <div class="col-3"><label>{{translations.color || 'Color'}}</label></div>
            <div class="col-9 color-picker-container">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1" :style="'background-color: ' + settings.color.hex" v-on:click.prevent='toggleTextColorPicker()'></span>
                    </div>
                    <input type="text" class="form-control form-control-sm" v-model="settings.color.hex">
                </div>
                <color-picker v-if="showTextColorPicker" v-model="settings.color" />
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-3"><label>{{translations.align || 'Align'}}</label></div>
            <div class="col-9">
                <div class="btn-group w-100" role="group" aria-label="Basic example">
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_align == 'left' }" v-on:click.prevent="settings.text_align = 'left'"><i class="fal fa-align-left"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_align == 'center' }" v-on:click.prevent="settings.text_align = 'center'"><i class="fal fa-align-center"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_align == 'right' }" v-on:click.prevent="settings.text_align = 'right'"><i class="fal fa-align-right"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_align == 'justify' }" v-on:click.prevent="settings.text_align = 'justify'"><i class="fal fa-align-justify"></i></button>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-3"><label>{{translations.style || 'Style'}}</label></div>
            <div class="col-9">
                <div class="btn-group w-40" role="group" aria-label="Basic example" style="width: 40%;">
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.font_style == 'normal' }" v-on:click.prevent="settings.font_style = 'normal'"><i class="fal fa-font"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.font_style == 'italic' }" v-on:click.prevent="settings.font_style = 'italic'"><i class="fal fa-italic"></i></button>
                </div>
                <div class="btn-group w-60 float-right" role="group" aria-label="Basic example" style="width: 58%;">
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_decoration == 'none' }" v-on:click.prevent="settings.text_decoration = 'inherit'"><i class="fal fa-times"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_decoration == 'strikethrough' }" v-on:click.prevent="settings.text_decoration = 'strikethrough'"><i class="fal fa-strikethrough"></i></button>
                    <button type="button" v-bind:class="{ btn: true, 'btn-sm': true, 'btn-secondary': true, active: settings.text_decoration == 'underline' }" v-on:click.prevent="settings.text_decoration = 'underline'"><i class="fal fa-underline"></i></button>
                </div>
            </div>
        </div>

        <h4>{{translations.background || 'Background'}}</h4>
        <div class="row mb-1" style="z-index: 2;">
            <div class="col-3"><label>{{translations.color || 'Color'}}</label></div>
            <div class="col-9 color-picker-container">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1" :style="'background-color: ' + settings.background_color.hex" v-on:click.prevent='toggleBackgroundColorPicker()'></span>
                    </div>
                    <input type="text" class="form-control form-control-sm" v-html="settings.background_color.hex">
                </div>
                <color-picker v-if="showBackgroundColorPicker" v-model="settings.background_color" />
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.Size || 'Size'}}</label></div>
            <div class="col-9">
                <div class="input-group">
                    <select class="form-control form-control-sm" v-html="settings.background_size">
                        <option value=""></option>
                        <option value="cover">{{translations.cover || 'Cover'}}</option>
                        <option value="contain">{{translations.contain || 'Contain'}}</option>
                        <option value="auto">{{translations.auto || 'Auto'}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.position || 'Position'}}</label></div>
            <div class="col-9">
                <div class="input-group">
                    <select class="form-control form-control-sm" v-html="settings.background_position">
                        <option value="top left">{{translations.top_left || 'top left'}}</option>
                        <option value="top center">{{translations.top_center || 'top center'}}</option>
                        <option value="top right">{{translations.top_right || 'top right'}}</option>
                        <option value="center left">{{translations.center_left || 'center left'}}</option>
                        <option value="center center">{{translations.center_center || 'center center'}}</option>
                        <option value="center right">{{translations.center_right || 'center right'}}</option>
                        <option value="bottom left">{{translations.bottom_left || 'bottom left'}}</option>
                        <option value="bottom center">{{translations.bottom_center || 'bottom center'}}</option>
                        <option value="bottom right">{{translations.bottom_right || 'bottom right'}}</option>
                        <option value="custom">{{translations.bottom_right || 'bottom right'}}</option>
                        <option value="custom">{{translations.custom || 'Custom'}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div v-if="settings.background_position == 'custom'" class="row mb-1">
            <div class="col-3"></div>
            <div class="col-9">
                <div class="row">
                    <div class="col-3"><label>{{translations.top || 'Top'}}</label></div>
                    <div class="col-3"><input type="int" class="form-control form-control-sm" v-html="settings.background_position_x"></div>
                    <div class="col-3"><label>{{translations.left || 'Left'}}</label></div>
                    <div class="col-3"><input type="int" class="form-control form-control-sm" v-html="settings.background_position_y"></div>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-3"><label>{{translations.tile || 'Tile'}}</label></div>
            <div class="col-9">
                <div class="input-group">
                    <select class="form-control form-control-sm" v-html="settings.background_repeat">
                        <option value=""></option>
                        <option value="repeat">{{translations.repeat || 'Repeat'}}</option>
                        <option value="repeat-x">{{translations.horizontal || 'Horizontal'}}</option>
                        <option value="repeat-y">{{translations.vertical || 'Vertical'}}</option>
                        <option value="no-repeat">{{translations.no_repeat || 'No repeat'}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-3"><label>{{translations.fixed || 'Fixed'}}</label></div>
            <div class="col-9">
                <div class="input-group">
                    <select class="form-control form-control-sm" v-html="settings.background_attachment">
                        <option value="scroll">{{translations.no || 'No'}}</option>
                        <option value="fixed">{{translations.yes || 'Yes'}}</option>
                    </select>
                </div>
            </div>
        </div>
        <h4>{{translations.border || 'Border'}}</h4>
    </div>
</template>

<script>
    import { Sketch } from 'vue-color'

    export default {
        name: "settings",
        components: {
            'color-picker': Sketch
        },
        props: ['settings'],
        data() {
            return {
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    "Authorization" : "Bearer " + this.$store.state.apikey
                },
                component: {},
                showTextColorPicker: false,
                showBackgroundColorPicker: false,
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

        },
        methods: {
            generateCss: function() {
                this.$parent.generateCss();

            },
            toggleTextColorPicker: function(){
                this.showTextColorPicker = !this.showTextColorPicker;
            },
            toggleBackgroundColorPicker: function(){
                this.showBackgroundColorPicker = !this.showBackgroundColorPicker;
            },

        }
    }
</script>

<style lang="scss">

.color-picker-container {
    position: relative;
}
.vc-sketch {
    position: absolute !important;
    top: 40px;
    z-index: 2;
}

.component-settings {

    font-size: 0.75rem;

    h4 {
        font-size: 1rem;
    }

    label {
        line-height: 31px;
    }

    input, select {
        background-color: #DDD !important;
        border: none !important;
        text-align: center;
    }

    .row {
        .col-3:first-child, .col-3:nth-child(3) {
            padding-right: 0;
        }
        .col-3:nth-child(2), .col-9:nth-child(2), .col-3:last-child {
            padding-left: 0
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
}
</style>
