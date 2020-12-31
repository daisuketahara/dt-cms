<?php

namespace App\Service;

class ConstructService
{

    public function getHTML($construct)
    {







        return false;
    }

    public function getCss($construct)
    {
        $css = '';

        foreach($construct as $item) {

            $id = $item->id;
            $settings = $item->settings;
            $parts = $item->parts;

            $properties = $this->getCssproperties($settings);

            foreach($parts as $key => $part) {

                $element = 'component-' . $key;
                $properties = $this->getCssproperties($part->settings);
                if (!empty($properties)) $css = '#' . $id . ' .component-' . $key . ' {' . $properties . '}';
            }

        }

        return $css;
    }

    public function getCssproperties($settings)
    {
        $css = "";

        if (!empty($settings->width)) $css .= "width: " . $settings->width . "; ";
        if (!empty($settings->height)) $css .= "height: " . $settings->height . "; ";
        if (!empty($settings->min_width)) $css .= "min-width: " . $settings->min_width . "; ";
        if (!empty($settings->min_height)) $css .= "min-height: " . $settings->min_height . "; ";
        if (!empty($settings->max_width)) $css .= "max-width: " . $settings->max_width . "; ";
        if (!empty($settings->max_height)) $css .= "max-height: " . $settings->max_height . "; ";
        if (!empty($settings->overflow)) $css .= "overflow: " . $settings->overflow . "; ";
        if (!empty($settings->padding)) {
            if (!empty($settings->padding->top)) $css .= "padding-top: " . $settings->padding->top . "; ";
            if (!empty($settings->padding->right)) $css .= "padding-right: " . $settings->padding->right . "; ";
            if (!empty($settings->padding->bottom)) $css .= "padding-bottom: " . $settings->padding->bottom . "; ";
            if (!empty($settings->padding->left)) $css .= "padding-left: " . $settings->padding->left . "; ";
        }
        if (!empty($settings->margin !== 'undefined')) {
            if (!empty($settings->margin->top)) $css .= "margin-top: " . $settings->margin->top . "; ";
            if (!empty($settings->margin->right)) $css .= "margin-right: " . $settings->margin->right . "; ";
            if (!empty($settings->margin->bottom)) $css .= "margin-bottom: " . $settings->margin->bottom . "; ";
            if (!empty($settings->margin->left)) $css .= "margin-left: " . $settings->margin->left . "; ";
        }
        if (!empty($settings->font_weight)) $css .= "font-weight: " . $settings->font_weight . "; ";
        if (!empty($settings->font_size)) {
            $css .= "font-size: " . $settings->font_size . "px; ";
            if (!empty($settings->line_height)) $css .= "line-height: " . ($settings->font_size + $settings->line_height) . "px; ";
        }
        if (!empty($settings->color) && $settings->color_enabled) {
            if (!empty($settings->color->hex)) $css .= "color: " . $settings->color->hex . "; ";
        }
        if (!empty($settings->text_shadow)) $css .= "text-shadow: " . $settings->text_shadow . "; ";
        if (!empty($settings->text_align)) $css .= "text-align: " . $settings->text_align . "; ";
        if (!empty($settings->font_style)) $css .= "font-style: " . $settings->font_style . "; ";
        if (!empty($settings->text_decoration)) $css .= "text-decoration: " . $settings->text_decoration . "; ";
        if (!empty($settings->background_color) && $settings->background_color_enabled) {
            if (!empty($settings->background_color->hex != '')) $css .= "background-color: " . $settings->background_color->hex . "; ";
        }
        if (!empty($settings->background_image)) $css .= "background-image: url(" . $settings->background_image . "); ";
        if (!empty($settings->background_size)) $css .= "background-size: " . $settings->background_size . "; ";
        if (!empty($settings->background_position)) $css .= "background-position: " . $settings->background_position . "; ";
        if (!empty($settings->background_repeat)) $css .= "background-repeat: " . $settings->background_repeat . "; ";
        if (!empty($settings->background_attachment)) $css .= "background-attachment: " . $settings->background_attachment . "; ";
        if (!empty($settings->box_shadow)) $css .= "box-shadow: " . $settings->box_shadow . "; ";

        if (!empty($settings->border_radius)) $css .= "border-radius: " . $settings->border_radius . $settings->border_radius_unit . "; ";
        if (!empty($settings->border_width)) {
            $css .= "border-width: " . $settings->border_width . $settings->border_width_unit . "; ";
            if (!empty($settings->border_style)) $css .= "border-style: " . $settings->border_style . "; ";
            if (!empty($settings->border_color) && $settings->border_color_enabled) {
                if (!empty($settings->border_color->hex)) $css .= "border-color: " . $settings->border_color->hex . "; ";
            }
        }

        if (!empty($settings->custom_css)) $css .= $settings->custom_css;

        return $css;
    }
}
