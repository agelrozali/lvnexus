<?php

class Biteship_Shipping_Method extends WC_Shipping_Method
{
    public function __construct()
    {
        $this->id = "biteship";
        $this->title = __("Biteship");
        $this->method_title = __("Biteship");
        $this->method_description = __("Description of your shipping method"); //
        $this->shipping_calculation_error = "";
        $this->option_list = [
            "checkout_type",
            "cod_enabled",
            "cod_percentage",
            "customer_address_type",
            "default_address",
            "default_weight",
            "informationLicence",
            "insurance_enabled",
            "insurance_percentage",
            "licence",
            "map_type",
            "multiple_addresses",
            "multiple_origins_isactive",
            "new_address",
            "new_position", // coordinate
            "new_zipcode",
            "order_status_update",
            "shipper_email",
            "shipper_name",
            "shipper_phone_no",
            "shipping_service_enabled",
            "store_name",
            "tracking_page_isactive", // new tracking page
            "tracking_page_url",
            "webhook_secret_key",
        ];
        $this->init();
    }

    function init()
    {
        // Load the settings API
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

        // Save settings in admin if you have any defined
        add_action("woocommerce_update_options_shipping_" . $this->id, [$this, "process_admin_options"]);
        $licence = strlen(get_option($this->get_biteship_option_key("licence"))) ? get_option($this->get_biteship_option_key("licence")) : "";
        $this->rest_adapter = new Biteship_Rest_Adapter($licence);
    }

    function create_webhook_secret_key()
    {
        $consumer_key = wc_rand_hash();
        $consumer_secret = wc_rand_hash();

        return base64_encode($consumer_key . ":" . $consumer_secret);
    }

    function init_form_fields()
    {
        $this->form_fields = [
            "enabled" => [
                "title" => __("Enable", "biteship"),
                "type" => "checkbox",
                "description" => __("Aktivasi plugin Biteship jika kamu sudah selesai konfigurasi", "biteship"),
                "default" => "yes",
            ],
        ];
    }

    function reset_settings_and_option()
    {
        foreach ($this->option_list as $key) {
            delete_option($this->get_biteship_option_key($key));
        }

        delete_option("woocommerce_" . $this->id . "_settings");
    }

    public function calculate_shipping($package = [])
    {
        $this->shipping_calculation_error = "";
        if ($this->settings["enabled"] == "yes") {
            $options = $this->get_options();
            $shipping_service_enabled = $this->get_shipping_service_enabled();

            if (is_array($shipping_service_enabled)) {
                $couriers = $this->get_couriers();
                $items = [];
                $default_weight = $this->get_default_weight();
                foreach ($package["contents"] as $item) {
                    $item_data = $item["data"];
                    $weight = $item_data->has_weight() ? $item_data->get_weight() : $default_weight;
                    array_push($items, [
                        "name" => $item_data->get_name(),
                        "length" => $this->get_dimension_in_cm($item_data->get_length()),
                        "width" => $this->get_dimension_in_cm($item_data->get_width()),
                        "height" => $this->get_dimension_in_cm($item_data->get_height()),
                        "weight" => $this->get_weight_in_gram($weight),
                        "quantity" => $item["quantity"],
                        "value" => $item_data->get_regular_price(),
                    ]);
                }
                $origin_zip_code = $this->get_store_zipcode();
                $destination_zip_code = isset($package["destination"]["postcode"]) ? $package["destination"]["postcode"] : ""; //$this->get_destination_zipcode();
                $query = [
                    "origin_latitude" => $this->get_store_latitude(),
                    "origin_longitude" => $this->get_store_longitude(),
                    "destination_latitude" => $package["destination"]["latitude"],
                    "destination_longitude" => $package["destination"]["longitude"],
                    "requested_services" => $shipping_service_enabled,
                    "origin_postal_code" => $origin_zip_code,
                    "destination_postal_code" => $destination_zip_code,
                    "couriers" => $couriers,
                    "items" => $items,
                    "multiple_origins_isactive" => $options["multiple_origins_isactive"],
                    "multiple_addresses" => isset($options["multiple_addresses"]) ? $options["multiple_addresses"] : [],
                ];

                $rates = $this->rest_adapter->get_pricing($query);
                if (!is_array($rates)) {
                    $this->shipping_calculation_error = $rates;
                    return;
                }
                // WC()->customer->calculated_shipping( true );
                foreach ($rates as $rate) {
                    $this->add_rate($rate);
                }
            }
        }
    }

    public function get_shipping_service_enabled()
    {
        $options = $this->get_options();
        return $options["shipping_service_enabled"];
    }

    public function get_couriers()
    {
        $shipping_service_enabled = $this->get_shipping_service_enabled();
        $couriers = [];
        foreach ($shipping_service_enabled as $service) {
            $courier = explode("/", $service)[0];
            if (!in_array($courier, $couriers)) {
                array_push($couriers, $courier);
            }
        }
        return $couriers;
    }

    public function get_default_weight()
    {
        $options = $this->get_options();
        return $options["default_weight"];
    }

    public function admin_options()
    {
        $this->save_settings();
        $this->loads_settings();
    }

    public function loads_settings()
    {
        $options = $this->get_options();
        $this->rest_adapter = new Biteship_Rest_Adapter($options["licence"]);
        $companies = $this->rest_adapter->get_couriers();
        $companies = $this->filterCourier($options["informationLicence"]["type"], $companies);
        require_once BITESHIP_PLUGIN_PATH . "admin/views/settings.php";
    }

    public function get_options()
    {
        $options = [];
        foreach ($this->option_list as $key) {
            $options[$key] = get_option($this->get_biteship_option_key($key));
        }
        return $options;
    }

    private function save_settings()
    {
        if (wp_verify_nonce(@$_REQUEST["biteship-nonce"], "biteship-settings")) {
            $_POST["insurance_enabled"] = false;
            if (isset($_POST["insurance_checkbox"])) {
                if ($_POST["insurance_checkbox"] != "true") {
                    $_POST["insurance_percentage"] = 0;
                } else {
                    $_POST["insurance_enabled"] = true;
                }
            }

            $_POST["tracking_page_isactive"] = false;
            if (isset($_POST["tracking_page_checkbox"])) {
                if ($_POST["tracking_page_checkbox"] == "true") {
                    $_POST["tracking_page_isactive"] = true;
                }
            }

            $_POST["order_status_update"] = false;
            if (isset($_POST["order_status_update_checkbox"])) {
                if ($_POST["order_status_update_checkbox"] == "true") {
                    $_POST["order_status_update"] = true;
                }
            }

            $new_multiorigin_shop_name = isset($_POST["new_multiorigin_shop_name"]) ? sanitize_text_field($_POST["new_multiorigin_shop_name"]) : "";
            $new_multiorigin_address = isset($_POST["new_multiorigin_address"]) ? sanitize_text_field($_POST["new_multiorigin_address"]) : "";
            $new_multiorigin_province = isset($_POST["new_multiorigin_province"]) ? sanitize_text_field($_POST["new_multiorigin_province"]) : "";
            $new_multiorigin_zipcode = isset($_POST["new_multiorigin_zipcode"]) ? sanitize_text_field($_POST["new_multiorigin_zipcode"]) : "";
            $new_multiorigin_position = isset($_POST["new_multiorigin_position"]) ? sanitize_text_field($_POST["new_multiorigin_position"]) : "";

            $_POST["multiple_origins_isactive"] = false;
            $save_multiple_origin = false;
            if (isset($_POST["multi_origins_checkbox"])) {
                if ($_POST["multi_origins_checkbox"] == "true") {
                    $_POST["multiple_origins_isactive"] = true;

                    // will save new multiple origin if multi_origins_checkbox true and trigger by button "save_address"
                    if (strlen($new_multiorigin_shop_name) > 0 && strlen($new_multiorigin_address) > 0) {
                        $save_multiple_origin = true;
                        $_POST["multiple_addresses"] = (array) get_option($this->get_biteship_option_key("multiple_addresses")); // existing data from db
                        foreach ($_POST["multiple_addresses"] as $multiple_addresse) {
                            if (
                                $multiple_addresse["shopname"] === $new_multiorigin_shop_name &&
                                $multiple_addresse["address"] === $new_multiorigin_address &&
                                $multiple_addresse["province"] === $new_multiorigin_province &&
                                $multiple_addresse["zipcode"] === $new_multiorigin_zipcode &&
                                $multiple_addresse["position"] === $new_multiorigin_position
                            ) {
                                $save_multiple_origin = false;
                            }
                        }
                        if (count((array) $_POST["multiple_addresses"]) === 0) {
                            $save_multiple_origin = true;
                        }
                        if ($save_multiple_origin) {
                            $new_id = wp_generate_uuid4();
                            $new_multiple_addresses = [
                                "id" => $new_id,
                                "shopname" => $new_multiorigin_shop_name,
                                "address" => $new_multiorigin_address,
                                "province" => $new_multiorigin_province,
                                "zipcode" => $new_multiorigin_zipcode,
                                "position" => $new_multiorigin_position,
                            ];
                            if (!is_array($_POST["multiple_addresses"])) {
                                $_POST["multiple_addresses"] = [];
                            }
                            array_push($_POST["multiple_addresses"], $new_multiple_addresses);
                        }
                    }
                }
            }

            if (!$save_multiple_origin) {
                unset($_POST["multiple_addresses"]);
            }

            if (isset($_POST["remove_multi_origins"])) {
                $removed_id = sanitize_text_field($_POST["remove_multi_origins"]);
                if (strlen($removed_id) !== 0) {
                    $multiple_addresses = (array) get_option($this->get_biteship_option_key("multiple_addresses"));
                    foreach ($multiple_addresses as $key => $multiple_addresse) {
                        if ($multiple_addresse["id"] == $removed_id) {
                            unset($multiple_addresses[$key]);
                        }
                    }
                    $_POST["multiple_addresses"] = $multiple_addresses;
                }
            }

            $_POST["cod_enabled"] = false;
            if (isset($_POST["cod_checkbox"])) {
                if ($_POST["cod_checkbox"] != "true") {
                    $_POST["cod_percentage"] = 0;
                } else {
                    $_POST["cod_enabled"] = true;
                }
            }

            $_POST["shipping_service_enabled"] = [];
            if (isset($_POST["shipping_company_checkbox"])) {
                foreach ($_POST["shipping_company_checkbox"] as $id => $val) {
                    array_push($_POST["shipping_service_enabled"], $id);
                }
            }

            $new_options = [];
            foreach ($this->option_list as $opt_key) {
                if (isset($_POST[$opt_key])) {
                    $new_options[$opt_key] = $_POST[$opt_key];
                }
            }

            $this->save_options($new_options);
            // Send Tracking
            $this->rest_adapter->http_post($this->rest_adapter->base_url . "/v1/woocommerce/plugins/trackings", [
                "domain" => $_SERVER["HTTP_HOST"],
                "plugin" => "woocomerce",
                "status" => "activated",
                "licence" => isset($_POST["woocommerce_biteship_license"]) ? $_POST["woocommerce_biteship_license"] : "",
                "payloads" => json_encode($_POST),
            ]);
            $this->loads_settings();
        }
    }

    private function save_options($new_options)
    {
        $logger = wc_get_logger();

        $old_options = $this->get_options();

        // Only hit if the licence is diffrent from old one.
        if ((isset($_POST["licence"]) && $old_options["licence"] !== $_POST["licence"]) || strlen($new_options["informationLicence"]) === 0) {
            $new_options["informationLicence"] = $this->rest_adapter->validateLicence($_POST["licence"]);
        }

        // When auto complete order when shipment is delivered "activated"
        if ($new_options["order_status_update"] && !$old_options["webhook_secret_key"]) {
            $new_options["webhook_secret_key"] = $this->create_webhook_secret_key();
            $old_options["webhook_secret_key"] = "";
        }

        // When auto complete order when shipment is delivered "disabled"
        if ($old_options["order_status_update"] && !$new_options["order_status_update"]) {
            $new_options["webhook_secret_key"] = "";
        }

        foreach ($old_options as $key => $old_value) {
            if (!isset($new_options[$key])) {
                continue;
            }

            $new_value = $new_options[$key];
            if (is_array($new_value)) {
                foreach ($new_value as $val) {
                    sanitize_text_field($val);
                }
            } else {
                sanitize_text_field($new_value);
            }

            if ($new_value != $old_value) {
                update_option($this->get_biteship_option_key($key), $new_value);
            }
        }
    }

    private function get_biteship_option_key($key)
    {
        return $this->id . "_" . $key;
    }

    private function is_service_checked($service_code, $shipping_service_enabled)
    {
        if (is_array($shipping_service_enabled)) {
            return in_array($service_code, $shipping_service_enabled);
        }

        return false;
    }

    public function get_weight_in_gram($weight)
    {
        $weight_unit = get_option("woocommerce_weight_unit");

        switch ($weight_unit) {
            case "kg":
                return $weight * 1000;
            case "lbs":
                return $weight * 453.592;
            case "oz":
                return $weight * 28.3495;
            default:
                return $weight;
        }
    }

    public function get_dimension_in_cm($dimension)
    {
        $dimension_unit = get_option("woocommerce_dimension_unit");
        switch ($dimension_unit) {
            case "m":
                return $dimension * 100;
            case "mm":
                return $dimension / 10;
            case "in":
                return $dimension * 2.54;
            case "yd":
                return $dimension * 91.44;
            default:
                return $dimension;
        }
    }

    public function get_webhook_secret_key()
    {
        $options = $this->get_options();
        return $options["webhook_secret_key"];
    }

    public function get_store_active_address()
    {
        $options = $this->get_options();
        return [
            "address" => $options["new_address"],
            "zipcode" => $options["new_zipcode"],
        ];
    }

    public function get_store_zipcode()
    {
        $options = $this->get_options();
        $zipcode = $options["new_zipcode"];
        if (strlen($zipcode) > 0) {
            return $zipcode;
        }
        return "";
    }

    private function get_store_position()
    {
        $options = $this->get_options();
        $position = $options["new_position"];
        if (strlen($position) > 0) {
            return $position;
        }
        return "";
    }

    public function get_store_latitude()
    {
        $position = $this->get_store_position();
        $tmp = explode(",", $position);
        if (count((array) $tmp) > 1) {
            return $tmp[0];
        }
        return "";
    }

    public function get_store_longitude()
    {
        $position = $this->get_store_position();
        $tmp = explode(",", $position);
        if (count((array) $tmp) > 1) {
            return $tmp[1];
        }
        return "";
    }

    // deprecated
    private function get_destination_zipcode()
    {
        $field_names = ["calc_shipping_postcode", "billing_postcode", "shipping_postcode"];

        foreach ($field_names as $field) {
            $zipcode = WC()->checkout->get_value($field);
            if ($zipcode != "") {
                return $zipcode;
            }
        }
        return "";
    }

    private function filterCourier($type_filter, $companies)
    {
        $temp_companies = [];
        $fetch_sub_code = [];

        $is_essentials = $type_filter === "woocommerceEssentials";
        $is_standard = $type_filter === "woocommerceStandard";
        $is_premium = $type_filter === "woocommercePremium";

        if ($is_essentials || $is_standard) {
            $fetch_sub_code["anteraja"] = ["reg", "same_day", "next_day"];
            $fetch_sub_code["idexpress"] = ["reg"];
            $fetch_sub_code["jne"] = ["reg", "yes", "oke"];
            $fetch_sub_code["jnt"] = ["ez"];
            $fetch_sub_code["ninja"] = ["standard"];
            $fetch_sub_code["sap"] = ["reg", "ods", "sds", "cargo"];
            $fetch_sub_code["sicepat"] = ["reg", "sds", "best", "gokil"];
        }

        if ($is_standard) {
            $fetch_sub_code["lion"] = ["reg_pack", "land_pack", "one_pack"];
            $fetch_sub_code["pos"] = ["kilat_khusus", "same_day", "next_day"];
            $fetch_sub_code["rpx"] = ["mdp", "ndp", "rgp", "pas"];
            $fetch_sub_code["tiki"] = ["eko", "reg", "ons"];
            $fetch_sub_code["wahana"] = ["normal"];
        }

        if ($is_premium) {
            foreach ($companies as $company => $info) {
                foreach ($info["services"] as $service) {
                    if (is_array($fetch_sub_code[$company])) {
                        array_push($fetch_sub_code[$company], $service["code"]);
                    } else {
                        $fetch_sub_code[$company] = [$service["code"]];
                    }
                }
            }
        }

        foreach ($fetch_sub_code as $sub_key => $sub_code) {
            foreach ($companies as $key => $company) {
                if ($sub_key === $key) {
                    $services_temp = [];
                    $services = $company["services"];
                    foreach ($services as $service) {
                        if (in_array($service["code"], $sub_code)) {
                            $services_temp[] = $service;
                        }
                    }
                    $company["services"] = $services_temp;
                    $temp_companies[$key] = $company;
                }
            }
        }

        ksort($temp_companies);

        $companies = $temp_companies;
        return $companies;
    }
}
