<?php

namespace AgeGate\Utility;

class Dependency
{
    public static function met($required = '3.0.0')
    {
        if (!function_exists('is_plugin_active')) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if (!is_plugin_active('age-gate/age-gate.php')) {
            return false;
        }

        if (version_compare(get_option('age_gate_version'), $required, '<')) {
            return false;
        }

        return true;
    }

    public static function message($required, $addon, $plugin = 'Age Gate')
    {
        add_action('admin_notices', function() use ($required, $addon, $plugin) {
            echo sprintf(
                '<div id="message" class="notice notice-error"><p>%s requires %s %s or higher</p></div>',
                $addon,
                $plugin,
                $required
            );
        });
    }
}