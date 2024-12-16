<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://biteship.com/
 * @since             1.0.0
 * @package           Biteship
 *
 * @wordpress-plugin
 * Plugin Name:       Biteship for WooCommerce
 * Plugin URI:        https://biteship.com/
 * Description:       Pengiriman menjadi lebih mudah dengan layanan cek ongkos kirim dan penjemputan barang langsung ke lokasi yang dibantu oleh lebih dari 20 ekspedisi. Nikmati fitur ini hanya di Biteship for WooCommerce.
 * Version:           3.1.0
 * Author:            Biteship
 * Author URI:        https://biteship.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       biteship
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined("WPINC")) {
    die();
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define("BITESHIP_VERSION", "3.1.0");

define("BITESHIP_PLUGIN_PATH", plugin_dir_path(__FILE__));
define("BITESHIP_PLUGIN_URL", plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-biteship-activator.php
 */
function activate_biteship()
{
    require_once plugin_dir_path(__FILE__) . "includes/class-biteship-activator.php";
    Biteship_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-biteship-deactivator.php
 */
function deactivate_biteship()
{
    require_once plugin_dir_path(__FILE__) . "includes/class-biteship-deactivator.php";
    Biteship_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstall.
 * This action is documented in includes/class-biteship-uninstall.php
 */
function uninstall_biteship()
{
    require_once plugin_dir_path(__FILE__) . "includes/class-biteship-uninstall.php";
    Biteship_Uninstall::uninstall();
}

register_activation_hook(__FILE__, "activate_biteship");
register_deactivation_hook(__FILE__, "deactivate_biteship");
register_uninstall_hook(__FILE__, "uninstall_biteship");

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . "includes/class-biteship.php";

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_biteship()
{
    $plugin = new Biteship();
    $plugin->run();
}
run_biteship();
