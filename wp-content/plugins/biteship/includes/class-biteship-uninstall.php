<?php

/**
 * Fired during plugin uninstall
 *
 * @link       https://biteship.com/
 * @since      1.0.0
 *
 * @package    Biteship
 * @subpackage Biteship/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Biteship
 * @subpackage Biteship/includes
 * @author     Biteship
 */
class Biteship_Uninstall
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */

    public static function uninstall()
    {
        include_once ABSPATH . "wp-admin/includes/plugin.php";
        if (is_plugin_active("woocommerce/woocommerce.php")) {
            $shipping_methods = WC()->shipping()->get_shipping_methods();
            $biteship_shipping = $shipping_methods["biteship"];
            if ($biteship_shipping != null) {
                $biteship_shipping->reset_settings_and_option();
            }
        }

        // Send Tracking
        $adapter = new Biteship_Rest_Adapter("");
        $adapter->http_post($adapter->base_url . "/v1/woocommerce/plugins/trackings", [
            "domain" => $_SERVER["HTTP_HOST"],
            "plugin" => "woocomerce",
            "status" => "uninstalled",
            "licence" => "",
        ]);
    }
}
