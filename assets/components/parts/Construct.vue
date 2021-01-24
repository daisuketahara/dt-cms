<template>
    <transition-group name="fade-right" enter-active-class="animated fadeIn">
        <v-container class="py-0" v-for="(element, index) in construct" v-bind:key="'element_'+index">
            <v-row v-if="element.type == 'text_left_image_right' || element.type == 'text_right_image_left'" :id="element.id" class="component">
                <v-col cols="12" sm="6" class="wrap-content" :order-sm="element.type == 'text_left_image_right' ? 1 : 2">
                    <h3
                        v-if="element.parts.title.settings.display!='none'"
                        class="component-title"
                    >
                        {{ element.parts.title.content }}
                    </h3>
                    <div v-if="element.parts.text.settings.display!='none'" class="component-textarea" v-html="element.parts.text.content"></div>
                    <a
                        v-if="element.parts.button.settings.display!='none'"
                        :data-href="element.parts.button.settings.href"
                        class="component-button"
                    >
                        {{element.parts.button.content}}
                    </a>
                </v-col>
                <v-col
                    cols="12"
                    sm="6"
                    class="component-image"
                    :order-sm="element.type == 'text_left_image_right' ? 2 : 1"
                >
                </v-col>
            </v-row >
            <v-row v-else-if="element.type == 'text_text'" :id="element.id" class="component">
                <v-col cols="12" sm="6" class="wrap-content">
                    <h3
                        v-if="element.parts.title.settings.display!='none'"
                        class="component-title"
                    >
                        {{ element.parts.title.content }}
                    </h3>
                    <div v-if="element.parts.text.settings.display!='none'" class="component-textarea" v-html="element.parts.text.content"></div>
                    <a
                        v-if="element.parts.button.settings.display!='none'"
                        :data-href="element.parts.button.settings.href"
                        class="component-button"
                    >
                        {{element.parts.button.content}}
                    </a>
                </v-col>
                <v-col cols="12" sm="6" class="wrap-content">
                    <h3
                        v-if="element.parts.title2.settings.display!='none'"
                        class="component-title"
                    >
                        {{ element.parts.title2.content }}
                    </h3>
                    <div v-if="element.parts.text2.settings.display!='none'" class="component-textarea" v-html="element.parts.text2.content"></div>
                    <a
                        v-if="element.parts.button2.settings.display!='none'"
                        :data-href="element.parts.button2.settings.href"
                        class="component-button"
                    >
                        {{element.parts.button2.content}}
                    </a>
                </v-col>
            </v-row>
            <v-row v-else-if="element.type == 'image_image'" :id="element.id" class="component">
                <v-col cols="12" sm="6" class="component-image"></v-col>
                <v-col cols="12" sm="6" class="component-image"></v-col>
            </v-row>
            <v-row v-else-if="element.type == 'text'" :id="element.id" class="component component-text">
                <v-col cols="12">
                    <h3 v-if="element.parts.title.settings.display!='none'" class="component-title">
                        {{ element.parts.title.content }}
                    </h3>
                    <div v-if="element.parts.text.settings.display!='none'" class="component-textarea" v-html="element.parts.text.content"></div>
                    <a
                        v-if="element.parts.button.settings.display!='none'"
                        :data-href="element.parts.button.settings.href"
                        class="component-button"
                    >
                        {{element.parts.button.content}}
                    </a>
                </v-col>
            </v-row>
            <v-row v-else-if="element.type == 'block'" :id="element.id" class="component component-block">
                <v-col cols="12">
                </v-col>
            </v-row>
            <v-row v-else-if="element.type == 'html'" :id="element.id" class="component component-html">
                <v-col cols="12" v-html="element.html"></v-col>
            </v-row>
        </v-container>
    </transition-group>
</template>

<script>

    export default {

        name: "construct ",
        data() {
            return {
                construct: [],
                construct_css: '',
            }
        },
        computed: {
        },
        created() {

            var element = document.getElementById('page-style');
            if (element !== null) element.parentNode.removeChild(element);

            if (this.$attrs.value.length > 0) {
                var construct = JSON.parse(this.$attrs.value);
                this.construct = construct;
                this.generateCss();
            }
        },
        methods: {
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
        }
    }
</script>

<style lang="scss">
    @import '../../scss/components.scss';
</style>
