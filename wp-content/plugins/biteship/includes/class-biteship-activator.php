<?php

/**
 * Fired during plugin activation
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
class Biteship_Activator
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */

    public static function activate()
    {
        // Send Tracking
        $adapter = new Biteship_Rest_Adapter("");
        $adapter->http_post($adapter->base_url . "/v1/woocommerce/plugins/trackings", [
            "domain" => $_SERVER["HTTP_HOST"],
            "plugin" => "woocomerce",
            "status" => "installed",
            "licence" => "",
        ]);
    }
}
