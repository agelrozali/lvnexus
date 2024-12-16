<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://biteship.com/
 * @since      1.0.0
 *
 * @package    Biteship
 * @subpackage Biteship/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Biteship
 * @subpackage Biteship/public
 * @author     Biteship
 */
class Biteship_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Biteship_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Biteship_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . "css/biteship-public.css", [], $this->version, "all");
        wp_enqueue_style("jquery_uid", "https://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css", [], "1.12.0", "all");
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Biteship_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Biteship_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . "js/biteship-public.js", ["jquery"], $this->version . "-" . md5(date("Y-m-d H:i:s")), false);
        wp_enqueue_script("biteship_jquery_ui", plugin_dir_url(__FILE__) . "js/jquery-ui.min.js", ["jquery"], $this->version, false);

        $gmap_api_key = "";
        $biteship_base_url = "";
        $biteship_license_key = "";
        $customer_address_type = false;
        $biteship_license_key_type = "";
        $checkout_type = "dropdown"; // default
        $listCourier = [];
        $trackingPageUrl = "";
        $trackingPageIsactive = 0;
        $multipleOriginsIsactive = 0;
        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping != null) {
            $adapter = new Biteship_Rest_Adapter($biteship_shipping->rest_adapter->license_key);
            $res = $adapter->getGmapAPI();
            $gmap_api_key = $res["success"] ? $res["data"] : "";
            $biteship_options = $biteship_shipping->get_options();
            $customer_address_type = $biteship_options["customer_address_type"];
            $shipping_service_enabled = $biteship_options["shipping_service_enabled"];
            foreach ($shipping_service_enabled as $service) {
                $courier = explode("/", $service)[0];
                if (!in_array($courier, $listCourier)) {
                    array_push($listCourier, $courier);
                }
            }
            $biteship_license_key_type = isset($biteship_options["informationLicence"]) ? $biteship_options["informationLicence"]["type"] : "";
            $map_type = $biteship_options["map_type"];
            $checkout_type = $biteship_options["checkout_type"];
            $biteship_base_url = $biteship_shipping->rest_adapter->base_url;
            $biteship_license_key = $biteship_shipping->rest_adapter->license_key;
            $trackingPageUrl = isset($biteship_options["tracking_page_url"]) ? $biteship_options["tracking_page_url"] : "";
            $trackingPageIsactive = isset($biteship_options["tracking_page_isactive"]) ? $biteship_options["tracking_page_isactive"] : 0;
            $multipleOriginsIsactive =
                isset($biteship_options["multiple_origins_isactive"]) && strlen($biteship_options["multiple_origins_isactive"]) > 0 ? $biteship_options["multiple_origins_isactive"] : 0;
        }

        $data = [
            "theme" => get_option("template"),
            "checkoutType" => $checkout_type,
            "apiKey" => $gmap_api_key,
            "listCourier" => $listCourier,
            "biteshipBaseUrl" => $biteship_base_url,
            "biteshipLicenseKey" => $biteship_license_key,
            "biteshipLicenseKeyType" => $biteship_license_key_type,
            "shouldUseDistricPostalCode" => $customer_address_type == "district_postal_code",
            "shouldUseMapModal" => $map_type == "modal" || $map_type == "",
            "trackingPageUrl" => $trackingPageUrl,
            "trackingPageIsactive" => $trackingPageIsactive,
            "multipleOriginsIsactive" => $multipleOriginsIsactive,
            "biteshipNonce" => wp_create_nonce("biteshipNonce"),
            "userSession" => [
                "clearCache" => 1,
                "coordinate" => "",
                "postcode" => "",
            ],
        ];

        //check is user/guest have a session login
        require_once plugin_dir_path(dirname(__FILE__)) . "includes/class-biteship-shipping-method.php";
        if (isset(WC()->customer) && strlen(WC()->customer->get_billing()["email"]) > 0) {
            $data["userSession"]["clearCache"] = 0;
            $data["userSession"]["postcode"] = WC()->customer->get_billing()["postcode"];
            foreach (WC()->customer->get_meta_data() as $item) {
                if ($item->get_data()["key"] === "billing_biteship_location_coordinate") {
                    $data["userSession"]["coordinate"] = $item->get_data()["value"];
                    break;
                }
            }
        }
        wp_localize_script($this->plugin_name, "phpVars", $data);
    }

    public function cart_calculate_fees()
    {
        if (is_admin() && !defined("DOING_AJAX")) {
            return;
        }

        if (isset($_POST["post_data"])) {
            parse_str($_POST["post_data"], $post_data);
        } else {
            $post_data = $_POST;
        }

        $insurance_active = false;
        if (isset($post_data["is_insurance_active"])) {
            $insurance_active = $post_data["is_insurance_active"] == "on";
        }

        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping == null) {
            return;
        }

        $subtotal = WC()->cart->cart_contents_total;

        $biteship_options = $biteship_shipping->get_options();

        if ($biteship_options["insurance_percentage"] > 0 && $insurance_active) {
            $insurance = ($subtotal * $biteship_options["insurance_percentage"]) / 100;
            WC()->cart->add_fee("Biaya asuransi", $insurance, true, "");
        }

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        if (!isset($gateways["cod"])) {
            return;
        }

        $shipping_fee = WC()->cart->shipping_total;
        $payment_method = WC()->session->get("chosen_payment_method");
        if ($payment_method === "cod") {
            $cod = (($subtotal + $shipping_fee) * $biteship_options["cod_percentage"]) / 100;
            WC()->cart->add_fee("Biaya COD", $cod, true, "");
        }
    }

    public function available_payment_gateway($available_gateways)
    {
        $chosen_method_id = $this->get_chosen_method_id();

        //Ilyasa - Get Biteship COD Option for Validation COD Gateway in checkout page
        $biteship_shipping = $this->get_biteship_shipping();
        $biteship_options = $biteship_shipping->get_options();

        if (isset($available_gateways["cod"]) && strpos($chosen_method_id, "cod") === false) {
            unset($available_gateways["cod"]);
        } elseif (empty($biteship_options["cod_enabled"])) {
            unset($available_gateways["cod"]);
        }
        return $available_gateways;
    }

    public function insurance_option_view()
    {
        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping == null) {
            return;
        }

        $biteship_options = $biteship_shipping->get_options();
        if ($biteship_options["insurance_percentage"] <= 0) {
            return;
        }

        if (strlen($biteship_options["insurance_enabled"]) === 0) {
            return;
        }

        if (isset($_POST["post_data"])) {
            parse_str($_POST["post_data"], $post_data);
        } else {
            $post_data = $_POST;
        }
        $checked = "";
        if (isset($post_data["is_insurance_active"])) {
            $checked = $post_data["is_insurance_active"] == "on" ? "checked" : "";
        }

        $checkbox =
            '<input type="checkbox" ' .
            $checked .
            ' class="shipping_method" name="is_insurance_active" id="insurance_checkbox"><label for="insurance_checkbox">' .
            __("Gunakan asuransi", "biteship") .
            "</label>";
        echo '<tr class="biteship-insurance"><th></th><td data-title="Insurance">' . $checkbox . "</td>";
    }

    private function get_chosen_method_id()
    {
        if (WC()->session == null) {
            return "";
        }

        $chosen_methods = WC()->session->get("chosen_shipping_methods");
        if (!is_array($chosen_methods)) {
            return "";
        }

        if (count($chosen_methods) <= 0) {
            return "";
        }

        return $chosen_methods[0];
    }

    private function get_biteship_shipping()
    {
        if (in_array("woocommerce/woocommerce.php", apply_filters("active_plugins", get_option("active_plugins")))) {
            $shipping_methods = WC()->shipping()->get_shipping_methods();
            $biteship_shipping = $shipping_methods["biteship"];
            return $biteship_shipping;
        }
    }

    public function get_list_state($states)
    {
        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping != null) {
            $adapter = new Biteship_Rest_Adapter($biteship_shipping->rest_adapter->license_key);
            $province = $adapter->getProvince();
            if (count($province) > 0) {
                $states["ID"] = $province;
                return $states;
            }
        }
        return $states;
    }

    public function save_order($payload)
    {
        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping != null) {
            $adapter = new Biteship_Rest_Adapter($biteship_shipping->rest_adapter->license_key);
            $adapter->saveOrder($payload);
        }
    }

    private function get_selected_biteship_shipping_from_order($order)
    {
        $shipping_methods = $order->get_shipping_methods();
        foreach ($shipping_methods as $method) {
            if ($method->get_method_id() == "biteship") {
                return $method;
            }
        }
        return null;
    }

    function get_order_detail()
    {
        $response = [];
        $order_id = (int) $_POST["orderId"];
        $waybill_id = "";
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
            if ($selected_shipping !== null) {
                $biteship_order_id = $selected_shipping->get_meta("biteship_order_id");
                if (strlen($biteship_order_id) > 0) {
                    $biteship_shipping = $this->get_biteship_shipping();
                    $waybill_id = $biteship_shipping->rest_adapter->get_biteship_order($biteship_order_id)["courier"]["waybill_id"];
                }
            }
        }
        $response["order_id"] = $order_id;
        $response["waybill_id"] = $waybill_id;
        wp_send_json_success($response);
    }

    public function get_detail_multiple_origin_by_id($id)
    {
        $biteship_shipping = $this->get_biteship_shipping();
        $biteship_options = $biteship_shipping->get_options();
        $adapter = new Biteship_Rest_Adapter($biteship_shipping->rest_adapter->license_key);
        $multiple_addresses = $biteship_options["multiple_addresses"];
        if (count($multiple_addresses) > 0) {
            foreach ($multiple_addresses as $multiple_address) {
                if ($id === $multiple_address["id"]) {
                    return $multiple_address["shopname"] . " - " . $multiple_address["address"];
                }
            }
        }
        return "";
    }

    public function get_multiple_origin()
    {
        check_ajax_referer("biteshipNonce", "security");
        $response = [];
        $response["multiple_addresses"] = [];
        $response["default_address"] = [];
        $areaID = isset($_POST["areaID"]) ? $_POST["areaID"] : "";
        $biteship_shipping = $this->get_biteship_shipping();
        $adapter = new Biteship_Rest_Adapter($biteship_shipping->rest_adapter->license_key);

        $biteship_options = $biteship_shipping->get_options();
        $multiple_addresses = $biteship_options["multiple_addresses"];
        $default_address = $biteship_options["default_address"];
        if (count($multiple_addresses) > 0) {
            foreach ($multiple_addresses as $multiple_addresse) {
                if (strlen($areaID) > 0 && $areaID === $multiple_addresse["province"]) {
                    array_push($response["multiple_addresses"], [
                        "id" => $multiple_addresse["id"],
                        "shopname" => $multiple_addresse["shopname"],
                        "address" => $multiple_addresse["address"],
                        "province_code" => $multiple_addresse["province"],
                        "province_name" => $adapter->list_province_code[$multiple_addresse["province"]],
                        "position" => $multiple_addresse["position"],
                    ]);
                }
                if ($multiple_addresse["id"] === $default_address) {
                    array_push($response["default_address"], [
                        "id" => $multiple_addresse["id"],
                        "shopname" => $multiple_addresse["shopname"],
                        "address" => $multiple_addresse["address"],
                        "province_code" => $multiple_addresse["province"],
                        "province_name" => $adapter->list_province_code[$multiple_addresse["province"]],
                        "position" => $multiple_addresse["position"],
                    ]);
                }
            }
        }
        wp_send_json_success($response);
    }

    public function recalculate_shipping()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . "includes/class-biteship-shipping-method.php";
        $packages = WC()->cart->get_shipping_packages();
        $package = [];
        if (count($packages) > 0) {
            $package = $packages[0];
        }
        $shipping_methods = new Biteship_Shipping_Method();
        $shipping_methods->calculate_shipping($package);
        // WC()->cart->calculate_shipping();
        return;
    }

    public function get_detail_info_new_district($code)
    {
        $result = "";
        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping != null) {
            $adapter = new Biteship_Rest_Adapter($biteship_shipping->rest_adapter->license_key);
            preg_match("#IDNP(.*?)IDNC#", $code, $area_id);
            $response = $adapter->getAreasV2("IDNP" . $area_id[1]);
            foreach ($response["areas"] as $area) {
                if ($area["id"] === $code) {
                    return $area["name"];
                }
            }
        }
        return $result;
    }

    public function add_checkout_loader()
    {
        include_once plugin_dir_path(__FILE__) . "views/loader.php";
        // echo '<div id="checkout_loader"><img src="URL_GAMBAR_LOADER" alt="Loading..."></div>';
    }
}
