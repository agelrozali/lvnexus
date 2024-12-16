<?php
/*
Plugin Name: Barcode Generator - create barcodes for products, orders, emails - (demo)
Description: Embedding barcodes into products, orders, email or on any posts or pages.
Text Domain: wpbcu-barcode-generator
Version: 2.0.2
Author: UkrSolution
Plugin URI: https://www.ukrsolution.com/Joomla/A4-BarCode-Generator-For-Wordpress
Author URI: http://www.ukrsolution.com
License: GPL2
WC requires at least: 4.0.0
-WC tested up to: 8.9.*
 */



if (!defined('ABSPATH')) {
    exit;
}



define('USWBG_PLUGIN_BASE_NAME', plugin_basename(__FILE__));

global $BarcodePrinting_Settings;

require_once plugin_dir_path(__FILE__) . 'constants.php';

require_once __DIR__ . '/lib/autoload.php';

$load_on_frontend = false;

if (isset($BarcodePrinting_Settings) && is_array($BarcodePrinting_Settings) && isset($BarcodePrinting_Settings['load_on_frontend'])) {
    $load_on_frontend = $BarcodePrinting_Settings['load_on_frontend'];
}

$load_on_frontend = true;

$load_on_url = false;

if (preg_match('/\/barcodes-print\/(.*?)\/(.*?)\/(.*?).svg(.*?)?$/', $_SERVER["REQUEST_URI"])) {
    $load_on_url = true;
}
else if (preg_match('/\/dashboard\/products/', $_SERVER["REQUEST_URI"])) {
    $load_on_url = true;
}

$a4bcConfig = require UkrSolution\BarcodesDigital\Helpers\Variables::$A4B_PLUGIN_BASE_PATH . 'config/config.php';

$BarcodesDigitalFrontend = new UkrSolution\BarcodesDigital\Frontend($a4bcConfig);

if (is_admin() || $load_on_frontend == true || $load_on_url == true) {

    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    if (!is_plugin_active(plugin_basename(__FILE__))) {
        $activePlugins = is_multisite() ? get_site_option('active_sitewide_plugins') : get_option('active_plugins');
        foreach ($activePlugins as $sitewideActivePlugin => $activePlugin) {

            if (false !== strpos($activePlugin, 'a4-barcode-generator-digital') || false !== strpos($sitewideActivePlugin, 'a4-barcode-generator')) {
                die(__('Please deactivate any other version of the plugin before you can activate this one.', 'wpbcu-barcode-generator'));
            }
        }
    }

    if (version_compare(phpversion(), '5.6.0', '<')) {
        $errorMsg = '<span>' . 'Barcode Generator - The minimum PHP version required for this plugin is 5.6.0.  Please update PHP version.' . '</span>';

        if (isset($_GET['action']) && 'error_scrape' == sanitize_key($_GET['action'])) {
            die('<span>' . $errorMsg . '</span>');
        } else {
            trigger_error($errorMsg, E_USER_ERROR);
        }
    }


    require_once __DIR__ . '/class/functions.php';

    try {
        if (is_admin()) {
            UkrSolution\BarcodesDigital\Database::checkTables();
        }
    } catch (\Throwable $th) {
    }

    register_activation_hook(__FILE__, function ($network_wide) {
        UkrSolution\BarcodesDigital\Database::setupTables($network_wide);
    });

    add_action('wpmu_new_blog', function ($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        $A4B_PLUGIN_BASE_NAME = USWBG_PLUGIN_BASE_NAME;
        if (is_plugin_active_for_network($A4B_PLUGIN_BASE_NAME)) {
            switch_to_blog($blog_id);
            UkrSolution\BarcodesDigital\Database::createTables();
            restore_current_blog();
        }
    }, 10, 6);

    add_action('plugins_loaded', function () {
        $pluginRelPath = basename(dirname(__FILE__)) . '/languages';
        load_plugin_textdomain('wpbcu-barcode-generator', false, $pluginRelPath);

        $pluginName = UkrSolution\BarcodesDigital\Helpers\Variables::$A4B_PLUGIN_BASE_NAME;
        $lastVersion = get_option("active-digital-barcodes-version", "");
        if ($lastVersion !== "2.0.2") {
            UkrSolution\BarcodesDigital\Database::createTables();
            update_option("active-digital-barcodes-version", "2.0.2");
        }
    });

    add_action('admin_notices', function () {
        uswbg_a4bShowNotices();
    });

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {


        $url = get_admin_url() . "admin.php?page=wpbcu-digital-settings";
        $settings_link = '<a href="' . $url . '">' . __('Settings', 'wpbcu-barcode-generator') . '</a>';
        $links[] = $settings_link;


        return $links;
    });


    add_action('admin_init', function () {
        if (defined('DOING_AJAX') && isset($_POST['action']) && false !== strpos(sanitize_key($_POST['action']), 'a4barcode_')) {
            if (version_compare(phpversion(), '7.1', '>=')) {
                ini_set('precision', 17);
                ini_set('serialize_precision', -1);
            }
        }
    }, 10, 2);

    add_action('admin_init', 'uswbg_a4bOldPostInitialization');


    $BarcodesDigitalCore = new UkrSolution\BarcodesDigital\Core($a4bcConfig);

}
