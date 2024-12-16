<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://biteship.com/
 * @since      1.0.0
 *
 * @package    Biteship
 * @subpackage Biteship/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Biteship
 * @subpackage Biteship/includes
 * @author     Biteship
 */
class Biteship_Deactivator
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */

    public static function deactivate()
    {
        include_once ABSPATH . "wp-admin/includes/plugin.php";
        if (is_plugin_active("woocommerce/woocommerce.php")) {
            $shipping_methods = WC()->shipping()->get_shipping_methods();
            $biteship_shipping = $shipping_methods["biteship"];
            $license = isset($biteship_shipping->settings->license) ? $biteship_shipping->settings->license : "";
            $adapter = new Biteship_Rest_Adapter($license);
            // Send Tracking
            $adapter->http_post($adapter->base_url . "/v1/woocommerce/plugins/trackings", [
                "domain" => $_SERVER["HTTP_HOST"],
                "plugin" => "woocomerce",
                "status" => "deactivated",
                "licence" => $license,
            ]);
        }
    }
}
