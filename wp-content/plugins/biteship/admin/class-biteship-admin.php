<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://biteship.com/
 * @since      1.0.0
 *
 * @package    Biteship
 * @subpackage Biteship/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Biteship
 * @subpackage Biteship/admin
 * @author     Biteship
 */
class Biteship_Admin
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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style("datetimepicker", plugin_dir_url(__FILE__) . "css/jquery.datetimepicker.css", [], $this->version, "all");
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . "css/biteship-admin.css", [], $this->version, "all");
    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script("datetimepicker", plugin_dir_url(__FILE__) . "js/jquery.datetimepicker.full.min.js", ["jquery"], $this->version, false);
        wp_enqueue_script("moment", plugin_dir_url(__FILE__) . "js/moment.min.js", [], $this->version, false);
        wp_enqueue_script($this->plugin_name . "-admin", plugin_dir_url(__FILE__) . "js/biteship-admin.js", ["jquery", "moment"], $this->version, false);

        $biteship_shipping = $this->get_biteship_shipping();
        $adapter = new Biteship_Rest_Adapter("");
        $res = $adapter->getGmapAPI();
        $gmap_api_key = isset($res["success"]) ? $res["data"] : "";
        $map_type = "";
        $message_var = "";
        $checkout_type = "dropdown"; // default
        $origin_position = "";
        $biteship_base_url = "";
        $biteship_license_key = "";
        $bitesPoint = "Rp 0";
        $trackingPageUrl = "";
        $trackingPageIsactive = 0;
        $multipleOriginsIsactive = 0;
        $customer_address_type = false;
        if ($biteship_shipping != null) {
            $biteship_options = $biteship_shipping->get_options();
            $customer_address_type = $biteship_options["customer_address_type"];
            $map_type = $biteship_options["map_type"];
            $checkout_type = $biteship_options["checkout_type"];
            $origin_position = $biteship_options["new_position"];
            $biteship_base_url = $biteship_shipping->rest_adapter->base_url;
            $biteship_license_key = $biteship_shipping->rest_adapter->license_key;
            if (preg_match("/order/", $_SERVER["REQUEST_URI"])) {
                $bitesPoint = $adapter->getBitepoints($biteship_license_key);
            }
            $biteship_shipper_name = $biteship_options["shipper_name"];
            $biteship_shipper_phone_no = $biteship_options["shipper_phone_no"];
            $trackingPageUrl = isset($biteship_options["tracking_page_url"]) ? $biteship_options["tracking_page_url"] : "";
            $trackingPageIsactive = isset($biteship_options["tracking_page_isactive"]) ? $biteship_options["tracking_page_isactive"] : 0;
            $multipleOriginsIsactive =
                isset($biteship_options["multiple_origins_isactive"]) && strlen($biteship_options["multiple_origins_isactive"]) > 0 ? $biteship_options["multiple_origins_isactive"] : 0;
        }
        /* Update state new position */
        if (isset($_POST["new_position"])) {
            $origin_position = strlen($_POST["new_position"]) > 0 ? $_POST["new_position"] : $origin_position;
        }
        $data = [
            "apiKey" => $gmap_api_key,
            "courier" => $message_var,
            "bitesPoint" => $bitesPoint,
            "origin_position" => $origin_position,
            "biteshipBaseUrl" => $biteship_base_url,
            "biteshipLicenseKey" => $biteship_license_key,
            "biteshipShipperName" => $biteship_shipper_name,
            "biteshipShipperPhoneNo" => $biteship_shipper_phone_no,
            "shouldUseDistricPostalCode" => $customer_address_type == "district_postal_code",
            "shouldUseMapModal" => $map_type == "modal" || $map_type == "",
            "checkoutType" => $checkout_type,
            "trackingPageUrl" => $trackingPageUrl,
            "trackingPageIsactive" => $trackingPageIsactive,
            "getShippingRatesNonce" => wp_create_nonce("biteship_admin_fetch_shipping_rates"),
            "orderBiteshipNonce" => wp_create_nonce("biteship_admin_order_biteship"),
            "multipleOriginsIsactive" => $multipleOriginsIsactive,
        ];
        wp_localize_script($this->plugin_name . "-admin", "phpVars", $data);
    }

    //Ilyasa - Get Biteship Order Status from Meta Data Order Woocommerce
    public function get_biteship_order_status()
    {
        check_ajax_referer("biteship_admin_order_biteship", "security");

        if (!current_user_can("edit_shop_orders")) {
            wp_send_json_error(["error" => "unauthorized"]);
            return;
        }
        $biteship_status = "";
        $biteship_waybill_id = "";
        $response = [
            "status" => "",
            "courier" => [
                "waybill_id" => "",
            ],
        ];
        $biteship_order_id = $_POST["biteship_order_id"];
        $meta_key = "biteship_order_id";
        $meta_value = $biteship_order_id;

        $args = [
            "post_type" => "shop_order",
            "meta_key" => $meta_key,
            "meta_value" => $meta_value,
            "meta_compare" => "=",
        ];

        $orders = wc_get_orders($args);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order_meta = $order->get_meta_data();
                foreach ($order_meta as $meta) {
                    if ($meta->key == "tracking_waybill_id") {
                        $biteship_waybill_id = $meta->value;
                        $response["courier"]["waybill_id"] = $biteship_waybill_id;
                    }
                    if ($meta->key == "tracking_status") {
                        $biteship_status = $meta->value;
                        $response["status"] = $biteship_status;
                    }
                }
                break;
            }
        }
        wp_send_json_success($response);
    }

    public function on_loaded()
    {
        // add_action('admin_menu', array($this, 'menu'));
    }

    public function menu()
    {
        add_menu_page("Biteship", "Biteship", "manage_woocommerce", "biteship", [$this, "render_admin_home_page"], null, "55.6");
    }

    public function render_admin_home_page()
    {
        include_once plugin_dir_path(__FILE__) . "views/home.php";
    }

    public function order_item_add_line_buttons($order)
    {
        include_once plugin_dir_path(__FILE__) . "views/order_item_add_line_buttons.php";
    }

    public function fetch_shipping_rates()
    {
        check_ajax_referer("biteship_admin_fetch_shipping_rates", "security");

        if (!current_user_can("edit_shop_orders")) {
            wp_die(-1);
        }

        $response = [];
        $biteship_shipping = $this->get_biteship_shipping();
        $shipping_service_enabled = $biteship_shipping->get_shipping_service_enabled();
        if (!is_array($shipping_service_enabled)) {
            wp_send_json_error(["message" => "no shipping enabled"]);
            return;
        }

        $order_id = $_POST["orderId"];
        $order = wc_get_order($order_id);

        $items = $this->map_order_items_to_biteship_items($order);

        $rest_adapter = $biteship_shipping->rest_adapter;
        $query = [
            "origin_latitude" => $biteship_shipping->get_store_latitude(),
            "origin_longitude" => $biteship_shipping->get_store_longitude(),
            "destination_latitude" => $_POST["destinationLatitude"],
            "destination_longitude" => $_POST["destinationLongitude"],
            "requested_services" => $shipping_service_enabled,
            "origin_postal_code" => $biteship_shipping->get_store_zipcode(),
            "destination_postal_code" => $_POST["destinationZipcode"],
            "couriers" => $biteship_shipping->get_couriers($shipping_service_enabled),
            "items" => $items,
        ];

        $rates = $biteship_shipping->rest_adapter->get_pricing($query);
        if (!is_array($rates)) {
            wp_send_json_error(["error" => $rates]);
        }
        $response = ["rates" => $rates];
        wp_send_json_success($response);
    }

    public function get_shop_information()
    {
        check_ajax_referer("biteship_admin_order_biteship", "security");
        if (!current_user_can("edit_shop_orders")) {
            wp_send_json_error(["error" => "unauthorized"]);
            return;
        }

        $shop_name = "";
        $adapter = new Biteship_Rest_Adapter("");
        $order_id = $_POST["orderId"];
        $order = wc_get_order($order_id);
        $get_multiple_origin_id = $order->get_meta("_shipping_biteship_multi_origins");
        $biteship_shipping = $this->get_biteship_shipping();
        $biteship_options = $biteship_shipping->get_options();

        if ($biteship_options["multiple_origins_isactive"]) {
            $multiple_addresses = $biteship_options["multiple_addresses"];
            foreach ($multiple_addresses as $multiple_address) {
                if ($multiple_address["id"] === $get_multiple_origin_id) {
                    $shop_name = $multiple_address["shopname"] . " - " . $adapter->list_province_code[$multiple_address["province"]];
                    break;
                }
            }
        }

        $response = [
            "id" => $get_multiple_origin_id,
            "shop_name" => $shop_name,
        ];
        wp_send_json_success($response);
    }

    public function has_payment_method($order)
    {
        $payment_method = $order->get_payment_method();
        return $payment_method;
    }

    public function order_biteship()
    {
        check_ajax_referer("biteship_admin_order_biteship", "security");

        if (!current_user_can("edit_shop_orders")) {
            wp_send_json_error(["error" => "unauthorized"]);
            return;
        }

        $response = [];
        $biteship_shipping = $this->get_biteship_shipping();
        $biteship_options = $biteship_shipping->get_options();
        $order_id = $_POST["orderId"];
        $order = wc_get_order($order_id);
        $items = $this->map_order_items_to_biteship_items($order);

        $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
        if ($selected_shipping == null) {
            wp_send_json_error(["error" => "no biteship shipping selected"]);
            return;
        }

        $biteship_order = [
            "shipper_contact_name" => $biteship_shipping->settings["shipper_name"],
            "shipper_contact_phone" => $biteship_shipping->settings["shipper_phone_no"],
            "shipper_contact_email" => $biteship_shipping->settings["shipper_email"],
            "shipper_organization" => $biteship_shipping->settings["store_name"],
            "origin_contact_name" => $_POST["senderName"],
            "origin_contact_phone" => $_POST["senderPhoneNo"],
            "origin_address" => $biteship_options["new_address"],
            "origin_note" => "",
            "origin_postal_code" => $biteship_options["new_zipcode"],
            "origin_coordinate_latitude" => $biteship_shipping->get_store_latitude(),
            "origin_coordinate_longitude" => $biteship_shipping->get_store_longitude(),
            "destination_contact_name" => $order->get_shipping_first_name() . " " . $order->get_shipping_last_name(),
            "destination_contact_phone" => $this->get_contact_phone($order),
            "destination_contact_email" => "",
            "destination_address" => $order->get_shipping_address_1() . " " . $order->get_shipping_address_2(),
            "destination_postal_code" => $order->get_shipping_postcode(),
            "destination_note" => "",
            "destination_coordinate_latitude" => $this->get_latitude($order->get_meta("_shipping_biteship_location_coordinate")),
            "destination_coordinate_longitude" => $this->get_longitude($order->get_meta("_shipping_biteship_location_coordinate")),
            "courier_company" => $selected_shipping->get_meta("courier_code"),
            "courier_type" => $selected_shipping->get_meta("courier_service_code"),
            "delivery_type" => $_POST["deliveryTimeOption"],
            "delivery_date" => $_POST["deliveryDate"],
            "delivery_time" => $_POST["deliveryTime"],
            "order_note" => "",
            "items" => $items,
            "shipping_biteship_multi_origins" => $order->get_meta("_shipping_biteship_multi_origins"),
        ];

        $logger = wc_get_logger();

        if ($biteship_options["order_status_update"]) {
            $host_name = $_SERVER["SERVER_NAME"];
            $plugin_dir_url = plugin_dir_url(__DIR__);
            $parsed_url = parse_url($plugin_dir_url);
            $webhook_url = $parsed_url["scheme"] . "://" . $parsed_url["host"] . "/wp-json/wc-biteship/v1/orders/" . $order_id . "/shipment-status";
            $biteship_webhook_secret_key = $biteship_shipping->get_webhook_secret_key();
            $biteship_order["webhooks"] = [
                [
                    "name" => "Woocommerce Order Shipment Status",
                    "events" => ["order.status"],
                    "signature_key" => "X-WC-Biteship-Webhook-Secret-Key",
                    "signature_secret" => $biteship_webhook_secret_key,
                    "url" => $webhook_url,
                ],
            ];
        }

        $logger->info(json_encode($biteship_options), [
            "source" => "order_biteship",
        ]);
        $logger->info(json_encode($biteship_order), [
            "source" => "order_biteship",
        ]);

        if ($this->has_payment_method($order) == "cod") {
            $is_cod_available = $selected_shipping->get_meta("is_cod_available");
            if ($is_cod_available) {
                $item_total_price = 0;
                foreach ($order->get_items() as $item_id => $item) {
                    $active_price = $item->get_total();
                    $item_total_price += $active_price;
                }
                $biteship_order["destination_cash_on_delivery"] = $item_total_price + $order->get_shipping_total();
            }
        }

        if ($this->order_has_fee($order, "Biaya asuransi")) {
            $biteship_order["courier_insurance"] = $order->get_subtotal();
        }

        // multiple origins order
        $biteship_order = $this->set_order_for_multi_origin($biteship_options, $biteship_order);

        $result = $biteship_shipping->rest_adapter->create_order($biteship_order); 

        if (!$result["success"]) {
            wp_send_json_error(["error" => $result["error"]]);
            return;
        }

        $response = [
            "order_id" => $result["data"]["order_id"],
            "status" => $result["data"]["status"],
            "note" => $result["data"]["note"],
            "updated_at" => $result["data"]["updated_at"],
            "waybill_id" => $result["data"]["waybill_id"],
        ];

        $selected_shipping->add_meta_data("biteship_order_id", $response["order_id"]);
        $selected_shipping->add_meta_data("tracking_waybill_id", $response["waybill_id"]);
        $selected_shipping->save_meta_data();

        $order->update_meta_data("biteship_order_id", $response["order_id"]);
        $order->update_meta_data("tracking_waybill_id", $response["waybill_id"]);
        $order->update_meta_data("tracking_status", $response["status"]);

        //Ilyasa  - Tempel Tracking History
        $history_status = [
            "status" => $response["status"],
            "note" => $response["note"],
            "updated_at" => $response["updated_at"],
        ];
        $existing_history_status = $order->get_meta("tracking_history");
        if (empty($existing_history_status)) {
            $existing_history_status = [$history_status];
        } else {
            $existing_history_status[] = $history_status;
        }
        $order->update_meta_data("tracking_history", $existing_history_status);
        $order->save();

        wp_send_json_success($response);
    }

    public function set_order_for_multi_origin($biteship_options, $biteship_order)
    {
        if ($biteship_options["multiple_origins_isactive"]) {
            $multiple_addresses = $biteship_options["multiple_addresses"];
            foreach ($multiple_addresses as $multiple_address) {
                if ($multiple_address["id"] === $biteship_order["shipping_biteship_multi_origins"]) {
                    $adapter = new Biteship_Rest_Adapter("");
                    $biteship_order["origin_address"] = $multiple_address["shopname"] . " - " . $adapter->list_province_code[$multiple_address["province"]];
                    $biteship_order["origin_postal_code"] = $multiple_address["zipcode"];
                    if (strlen($multiple_address["position"]) > 0) {
                        list($latitude, $longitude) = explode(",", $multiple_address["position"]);
                        $biteship_order["origin_coordinate_latitude"] = $latitude;
                        $biteship_order["origin_coordinate_longitude"] = $longitude;
                    }
                    break;
                }
            }
        }
        return $biteship_order;
    }

    public function delete_order_biteship()
    {
        check_ajax_referer("biteship_admin_order_biteship", "security");

        if (!current_user_can("edit_shop_orders")) {
            wp_send_json_error(["error" => "unauthorized"]);
            return;
        }

        $biteship_shipping = $this->get_biteship_shipping();
        $order_id = $_POST["orderId"];
        $order = wc_get_order($order_id);
        $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
        if ($selected_shipping == null) {
            wp_send_json_error(["error" => "biteship shipping not selected"]);
            return;
        }

        $biteship_order_id = $selected_shipping->get_meta("biteship_order_id");
        $result = $biteship_shipping->rest_adapter->delete_order($biteship_order_id);
        if (!$result["success"]) {
            wp_send_json_error(["error" => $result["error"]]);
            return;
        }

        $selected_shipping->delete_meta_data("biteship_order_id");
        $selected_shipping->delete_meta_data("tracking_waybill_id");
        $selected_shipping->save();
        wp_send_json_success([]);
    }

    public function add_biteship_order_shipping()
    {
        check_ajax_referer("order-item", "security");

        if (!current_user_can("edit_shop_orders")) {
            wp_die(-1);
        }

        $response = [];

        $order_id = $_POST["orderId"];
        $order = wc_get_order($order_id);

        $item = new WC_Order_Item_Shipping();
        $item->set_props([
            "method_title" => $_POST["methodTitle"],
            "method_id" => "biteship",
            "total" => wc_format_decimal($_POST["rate"]),
        ]);
        $item->add_meta_data("courier_code", $_POST["courierCode"]);
        $item->add_meta_data("courier_service_code", $_POST["courierServiceCode"]);
        $item->set_order_id($order_id);
        $item_id = $item->save();

        ob_start();
        include WC_ABSPATH . "includes/admin/meta-boxes/views/html-order-shipping.php";
        $response["html"] = ob_get_clean();
        wp_send_json_success($response);
    }

    public function show_order_biteship_shipping_button($item_id, $item, $product)
    {
        if (!$item instanceof WC_Order_Item_Shipping) {
            return;
        }

        // multiple origin showing at the detail order
        $shop_name = "";
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $biteship_shipping = $shipping_methods["biteship"]->get_options();
        if ($biteship_shipping["multiple_origins_isactive"]) {
            $adapter = new Biteship_Rest_Adapter("");
            $multiple_addresses = $biteship_shipping["multiple_addresses"];
            $order = $item->get_order();
            $get_multiple_origin_id = $order->get_meta("_shipping_biteship_multi_origins");
            foreach ($multiple_addresses as $multiple_address) {
                if ($multiple_address["id"] === $get_multiple_origin_id) {
                    $shop_name = $multiple_address["shopname"] . " - " . $adapter->list_province_code[$multiple_address["province"]];
                    break;
                }
            }
        }

        include_once plugin_dir_path(__FILE__) . "views/order_item_biteship_action.php";
    }

    public function custom_order_list_column($columns)
    {
        $reordered_columns = [];
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $biteship_shipping = $shipping_methods["biteship"]->get_options();

        // Inserting columns to a specific location
        foreach ($columns as $key => $column) {
            $reordered_columns[$key] = $column;
            if ($key == "order_status") {
                if ($biteship_shipping["multiple_origins_isactive"]) {
                    $reordered_columns["biteship_shop_origin"] = __("Asal Toko", "biteship");
                }
                // Inserting after "Status" column
                $reordered_columns["biteship_waybill"] = __("Resi", "biteship");
                $reordered_columns["biteship_status"] = __("Status Biteship", "biteship");
                $reordered_columns["biteship_action"] = __("Aksi Pengiriman", "biteship");
            }
        }
        return $reordered_columns;
    }

    public function custom_order_list_column_content($column, $post_id)
    {
        if ($column != "biteship_waybill" && $column != "biteship_action") {
            return;
        }

        if ($column == "biteship_action") {
            echo '<a class="button" disabled>' . __("Loading...", "biteship") . "</a>";
            return;
        }

        $order = wc_get_order($post_id);
        $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
        if ($selected_shipping == null) {
            echo " - ";
            return;
        }
        $biteship_order_id = $selected_shipping->get_meta("biteship_order_id");
        if ($column == "biteship_waybill") {
            echo '<span style="display: none">' . $biteship_order_id . "</span>";
            echo '<span style="display: none">' . $order->get_status() . "</span>";
            return;
        }
    }

    public function include_modal_order_biteship()
    {
        include_once plugin_dir_path(__FILE__) . "views/modal_order_biteship.php";
        include_once plugin_dir_path(__FILE__) . "views/modal_tracking_biteship.php";
        include_once plugin_dir_path(__FILE__) . "views/modal_multiorigin_biteship.php";
    }

    public function custom_admin_order_list_bulk_actions($actions)
    {
        $actions["create_biteship_shipment"] = __("Create Biteship Shipment", "biteship");
        $actions["delete_biteship_shipment"] = __("Cancel Biteship Shipment", "biteship");
        return $actions;
    }

    public function handle_bulk_create_biteship_order($redirect_to, $action, $post_ids)
    {
        if ($action !== "create_biteship_shipment") {
            return $redirect_to;
        }

        $biteship_shipping = $this->get_biteship_shipping();
        $store_address = $biteship_shipping->get_store_active_address();
        $biteship_options = $biteship_shipping->get_options();
        $error_message = "";
        $success_message = "";

        $shipper = [
            "contact_name" => $biteship_shipping->settings["shipper_name"],
            "contact_phone" => $biteship_shipping->settings["shipper_phone_no"],
            "contact_email" => $biteship_shipping->settings["shipper_email"],
            "organization" => $biteship_shipping->settings["store_name"],
        ];
        $origin = [
            "contact_name" => $_REQUEST["sender_name"],
            "contact_phone" => $_REQUEST["sender_phone_no"],
            "address" => $store_address["address"],
            "note" => "",
            "postal_code" => $store_address["zipcode"],
            "coordinate_latitude" => $biteship_shipping->get_store_latitude(),
            "coordinate_longitude" => $biteship_shipping->get_store_longitude(),
        ];
        $biteship_bulk_orders = [];
        foreach ($post_ids as $order_id) {
            $order = wc_get_order($order_id);
            $items = $this->map_order_items_to_biteship_items($order);

            $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
            if ($selected_shipping == null) {
                $error_message = $error_message . "Order #" . $order_id . " does not have shipping selected; ";
                continue;
            }

            $biteship_bulk_order = [
                "destination_contact_name" => $order->get_shipping_first_name() . " " . $order->get_shipping_last_name(),
                "destination_contact_phone" => $this->get_contact_phone($order),
                "destination_contact_email" => "",
                "destination_address" => $order->get_shipping_address_1() . " " . $order->get_shipping_address_2(),
                "destination_postal_code" => $order->get_shipping_postcode(),
                "destination_note" => "",
                "destination_coordinate_latitude" => $this->get_latitude($order->get_meta("_shipping_biteship_location_coordinate")),
                "destination_coordinate_longitude" => $this->get_longitude($order->get_meta("_shipping_biteship_location_coordinate")),
                "courier_company" => $selected_shipping->get_meta("courier_code"),
                "courier_type" => $selected_shipping->get_meta("courier_service_code"),
                "courier_insurance" => $order->get_subtotal(),
                "delivery_type" => $_REQUEST["delivery_time_option"],
                "delivery_date" => $_REQUEST["delivery_date"],
                "delivery_time" => $_REQUEST["delivery_time"],
                "order_note" => "",
                "items" => $items,
                "reference_id" => $order_id,
                "shipping_biteship_multi_origins" => $order->get_meta("_shipping_biteship_multi_origins"),
            ];

            // if ($this->order_has_fee($order, 'Biaya COD')) {
            // 	$item_total_price =  0;
            // 	foreach ( $order->get_items() as  $item_id => $item ) {
            // 		$active_price   = $item->get_total();
            // 		$item_total_price += $active_price;
            // 	}
            // 	$biteship_bulk_order['cash_on_delivery'] = $item_total_price + $order->get_shipping_total();
            // }

            //Ilyasa - new version of detecting cod or not
            if ($this->has_payment_method($order) == "cod") {
                $is_cod_available = $selected_shipping->get_meta("is_cod_available");
                if ($is_cod_available) {
                    $item_total_price = 0;
                    foreach ($order->get_items() as $item_id => $item) {
                        $active_price = $item->get_total();
                        $item_total_price += $active_price;
                    }
                    $biteship_bulk_order["cash_on_delivery"] = $item_total_price + $order->get_shipping_total();
                }
            }

            if ($this->order_has_fee($order, "Biaya asuransi")) {
                $biteship_bulk_order["courier_insurance"] = $order->get_subtotal();
            }

            // multiple origins order
            $biteship_bulk_order = $this->set_order_for_multi_origin($biteship_options, $biteship_bulk_order);

            array_push($biteship_bulk_orders, $biteship_bulk_order);
        }

        $result = $biteship_shipping->rest_adapter->bulk_create_order($shipper, $origin, $biteship_bulk_orders);
        if ($result["success"]) {
            $success_message = $success_message . "result success ";
            foreach ($result["data"] as $biteship_order) {
                $wc_order_id = $biteship_order["reference_id"];
                $success_message = $success_message . $biteship_order["id"] . " ";
                $order = wc_get_order($wc_order_id);
                $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
                $selected_shipping->add_meta_data("biteship_order_id", $biteship_order["id"]);
                $selected_shipping->add_meta_data("tracking_waybill_id", $biteship_order["waybill_id"]);
                $selected_shipping->save_meta_data();

                //update meta data order woocommerce
                // update_post_meta($order_id, 'biteship_order_id', $biteship_order['id']);
                // update_post_meta($order_id, 'tracking_waybill_id', $biteship_order['waybill_id']);
                // update_post_meta($order_id, 'tracking_status', $biteship_order['status']);

                $order->update_meta_data("biteship_order_id", $biteship_order["id"]);
                $order->update_meta_data("tracking_waybill_id", $biteship_order["waybill_id"]);
                $order->update_meta_data("tracking_status", $biteship_order["status"]);

                //Ilyasa  - Tempel Tracking History
                $history_status = [
                    "status" => $biteship_order["status"],
                    "note" => $biteship_order["note"],
                    "updated_at" => "2023-12-01T00:00:00+07:00",
                ];
                $existing_history_status = $order->get_meta("tracking_history");
                if (empty($existing_history_status)) {
                    $existing_history_status = [$history_status];
                } else {
                    $existing_history_status[] = $history_status;
                }
                $order->update_meta_data("tracking_history", $existing_history_status);
                $order->save();
            }
        } else {
            // TODO: check how biteship return bulk error
            $error_message = $error_message . $result["error"] . "; ";
            // $error_message = $error_message . 'Failed to cancel Order #'. $order_id . ': '. $result['error'] .'; ';
        }

        if ($error_message == "") {
            $success_message = "Bulk create success";
        }

        return $redirect_to = add_query_arg(
            [
                "biteship_operation" => true,
                "biteship_error" => $error_message,
                "biteship_message" => $success_message,
            ],
            $redirect_to
        );
    }

    public function handle_bulk_delete_biteship_order($redirect_to, $action, $post_ids)
    {
        if ($action !== "delete_biteship_shipment") {
            return $redirect_to;
        }

        $biteship_shipping = $this->get_biteship_shipping();
        $biteship_order_ids = [];
        $error_message = "";
        $success_message = "";

        foreach ($post_ids as $order_id) {
            $order = wc_get_order($order_id);
            $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
            if ($selected_shipping == null) {
                $error_message = $error_message . "Order #" . $order_id . " does not have shipping selected; ";
                continue;
            }

            $biteship_order_id = $selected_shipping->get_meta("biteship_order_id");
            array_push($biteship_order_ids, $biteship_order_id);
        }

        if (count($biteship_order_ids) > 0) {
            $result = $biteship_shipping->rest_adapter->bulk_delete_order($biteship_order_ids);
            if ($result["success"]) {
                foreach ($post_ids as $order_id) {
                    $order = wc_get_order($order_id);
                    $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
                    $selected_shipping->delete_meta_data("biteship_order_id");
                    $selected_shipping->save();
                }
            } else {
                // TODO: check how biteship return bulk error
                $error_message = $error_message . $result["error"] . "; ";
                // $error_message = $error_message . 'Failed to cancel Order #'. $order_id . ': '. $result['error'] .'; ';
            }
        }

        if ($error_message == "") {
            $success_message = "Bulk delete success";
        }

        return $redirect_to = add_query_arg(
            [
                "biteship_operation" => true,
                "biteship_error" => $error_message,
                "biteship_message" => $success_message,
            ],
            $redirect_to
        );
    }

    public function biteship_admin_order_notice()
    {
        if (empty($_REQUEST["biteship_operation"])) {
            return;
        }

        if ($_REQUEST["biteship_error"] != "") {
            echo '<div class="error"><p>' . esc_html($_REQUEST["biteship_error"]) . "</p></div>";
        }

        if ($_REQUEST["biteship_message"] != "") {
            echo '<div class="updated"><p>' . esc_html($_REQUEST["biteship_message"]) . "</p></div>";
        }
    }

    private function get_biteship_shipping()
    {
        $biteship_shipping = [];
        include_once ABSPATH . "wp-admin/includes/plugin.php";
        if (is_plugin_active("woocommerce/woocommerce.php")) {
            $shipping_methods = WC()->shipping()->get_shipping_methods();
            $biteship_shipping = $shipping_methods["biteship"];
        }
        return $biteship_shipping;
    }

    private function get_latitude($coordinate)
    {
        $tmp = explode(",", $coordinate);
        if (count($tmp) > 1) {
            return $tmp[0];
        }

        return "";
    }

    private function get_longitude($coordinate)
    {
        $tmp = explode(",", $coordinate);
        if (count($tmp) > 1) {
            return $tmp[1];
        }

        return "";
    }

    private function get_contact_phone($order)
    {
        $data = $order->get_data();
        try {
            if (strlen($data["billing"]["phone"]) > 0) {
                return $data["billing"]["phone"];
            }
            return $data["shipping"]["phone"];
        } catch (Exception $e) {
            return $data["billing"]["phone"];
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

    private function map_order_items_to_biteship_items($order)
    {
        $biteship_shipping = $this->get_biteship_shipping();
        $items = [];
        $default_weight = $biteship_shipping->get_default_weight();
        foreach ($order->get_items() as $item_id => $item) {
            $item_product = new WC_Order_Item_Product($item->get_id());
            $product = $item_product->get_product();
            $weight = $product->has_weight() ? $product->get_weight() : $default_weight;
            array_push($items, [
                "name" => $product->get_name(),
                "sku" => $product->get_sku(),
                "length" => $biteship_shipping->get_dimension_in_cm($product->get_length()),
                "width" => $biteship_shipping->get_dimension_in_cm($product->get_width()),
                "height" => $biteship_shipping->get_dimension_in_cm($product->get_height()),
                "weight" => $biteship_shipping->get_weight_in_gram($weight),
                "quantity" => $item->get_quantity(),
                "value" => $product->get_price(),
            ]);
        }

        return $items;
    }

    private function order_has_fee($order, $fee_name)
    {
        foreach ($order->get_fees() as $fee) {
            if ($fee->get_name() == $fee_name) {
                return true;
            }
        }

        return false;
    }

    public function add_meta_box()
    {
        $biteship_shipping = $this->get_biteship_shipping();
        if ($biteship_shipping != null) {
            $biteship_options = $biteship_shipping->get_options();
            $trackingPageUrl = isset($biteship_options["tracking_page_url"]) ? $biteship_options["tracking_page_url"] : "";
            $trackingPageIsactive = isset($biteship_options["tracking_page_isactive"]) ? $biteship_options["tracking_page_isactive"] : 0;
            if ($trackingPageIsactive && strlen($trackingPageUrl) > 0) {
                add_meta_box("woocommerce-biteship", __("Tracking Page", "woocommerce"), [$this, "meta_box"], "shop_order", "side", "high");
                // add_meta_box('woocommerce-biteship', __('Tracking Page', 'woocommerce'), array($this, 'meta_box'), wc_get_page_screen_id( 'shop-order' ), 'side', 'high');
            }
        }
    }

    /**
     * Show the meta box for shipment info on the order page
     */
    public function meta_box()
    {
        global $post;
        $order = wc_get_order($post->ID);
        $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
        if ($selected_shipping !== null) {
            $order_id = $selected_shipping->get_meta("biteship_order_id");
            $biteship_shipping = $this->get_biteship_shipping();
            $waybill_id = $selected_shipping->get_meta("tracking_waybill_id");
            if ($biteship_shipping !== null) {
                $waybill_id = $biteship_shipping->rest_adapter->get_biteship_order($order_id)["courier"]["waybill_id"];
            }
            $wording =
                '<div>Buat pengiriman lewat Biteship untuk membuat Tracking Page</div><div style="padding: 12px;"><a class="button btn-tracking" id="order_biteship" href="#">Kirim Barang</a></div>';
            if (strlen($waybill_id) > 0) {
                $wording =
                    '<div>Buat pengiriman lewat Biteship untuk membuat Tracking Page</div><div style="padding: 12px;"><a id="tracking-order" class="button btn-tracking" href="#">Cek Order</a></div>';
            }
            echo "<div> $wording </div>";
        }
    }

    public function get_order_trackings()
    {
        check_ajax_referer("biteship_admin_order_biteship", "security");

        if (!current_user_can("edit_shop_orders")) {
            wp_die(-1);
        }

        $response = [];
        $order_id = $_POST["orderId"];
        $order = wc_get_order($order_id);
        $items = $this->map_order_items_to_biteship_items($order);
        $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);
        if ($selected_shipping !== null) {
            $order_id = $selected_shipping->get_meta("biteship_order_id");
            $waybill_id = $selected_shipping->get_meta("tracking_waybill_id");
            $biteship_shipping = $this->get_biteship_shipping();
            if ($biteship_shipping != null) {
                $waybill_id = $biteship_shipping->rest_adapter->get_biteship_order($order_id)["courier"]["waybill_id"];
            }
            $response["items"] = $items;
            $response["order_id"] = $order_id;
            $response["waybill_id"] = $waybill_id;
        }

        wp_send_json_success($response);
    }

    public function custom_district_field_admin($order)
    {
        // $value = get_post_meta( $order->get_id(), '_billing_biteship_district', true );
        $value = $order->get_meta("_billing_biteship_district", true);
        echo "<p><strong>" . __("Kecamatan ", "woocommerce") . ":</strong> " . $value . "</p>";
    }
}
