<?php

namespace AgeGate\Integration;

use AgeGate\Common\Settings;
use AgeGate\Common\Integration;

class Offload extends Integration
{
    public function exists()
    {
        return class_exists('Amazon_S3_And_CloudFront');
    }

    public function init()
    {
        if ($this->exists()) {
            $settings = Settings::getInstance();
            global $wp_filter;

            // TODO: when deprecated items no longer used, switch to has_filter('age_gate/logo/src');
            if ($settings->logoId && count($wp_filter['age_gate/logo/src']->callbacks ?? []) < 2) {

                $settings->logo = wp_get_attachment_url($settings->logoId);

            }

            if ($settings->backgroundImageId) {
                $settings->backgroundImage = wp_get_attachment_url($settings->backgroundImageId);
            }
        }
    }
}
