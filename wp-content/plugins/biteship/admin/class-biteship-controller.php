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
class Biteship_Controller
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

    // TODO: This is a temporary solution
    private function generate_webhook_secret()
    {
        $encryption_key = $this->generate_token();
        $saved_token = get_option("token_encryption");
        $iv = base64_decode(get_option("iv_encryption"));
        $decrypted_token = openssl_decrypt($saved_token, "aes-256-cbc", $encryption_key, 0, $iv);

        return $decrypted_token;
    }

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

    public function register_api_route()
    {
        register_rest_route("wc-biteship/v1", "/orders/(?P<id>\d+)/shipments", [
            "methods" => "POST",
            "callback" => [$this, "create_shipment"],
            // FIXME: Please make sure that this works
            // "permission_callback" => [$this, "authorize_shipment_request"],
        ]);

        register_rest_route("wc-biteship/v1", "/orders/(?P<id>\d+)/shipment-status", [
            "methods" => "POST",
            "callback" => [$this, "update_shipment_status"],
            // FIXME: Please make sure that this works
            // "permission_callback" => [$this, "authorize_shipment_request"],
        ]);

        register_rest_route("custom/v1", "/tracking-orders", [
            "methods" => "POST",
            "callback" => [$this, "update_shipment_status"],
            "permission_callback" => function () {
                $encryption_key = $this->generate_token();
                $saved_token = get_option("token_encryption");
                $iv = base64_decode(get_option("iv_encryption"));
                $authorization_header = $_SERVER["HTTP_AUTHORIZATION"];
                list($scheme, $token) = explode(" ", $authorization_header);
                if ($scheme === "Bearer") {
                    $decrypted_token = openssl_decrypt($saved_token, "aes-256-cbc", $encryption_key, 0, $iv);
                    if ($decrypted_token !== base64_decode($token)) {
                        return new WP_Error("token_error", "Invalid API token.", ["status" => 403]);
                    }
                    return true;
                } else {
                    return new WP_Error("token_error", "Invalid Authorization Header", ["status" => 403]);
                }
            },
        ]);
    }

    public function has_payment_method($order)
    {
        $payment_method = $order->get_payment_method();
        return $payment_method;
    }

    public function authorize_shipment_request()
    {
        $headers = getallheaders();
        $biteship_shipping = $this->get_biteship_shipping();
        $biteship_webhook_secret_key = $biteship_shipping->get_webhook_secret_key();
        $incoming_webhook_secret_key = $headers["x-wc-biteship-webhook-secret-key"];

        if (isset($incoming_webhook_secret_key) && $incoming_webhook_secret_key === $biteship_webhook_secret_key) {
            return true;
        }

        if (!wc_rest_check_post_permissions("shop_order", "create")) {
            return new WP_Error("woocommerce_rest_cannot_update", __("Sorry, you are not allowed to update resources.", "woocommerce"), ["status" => rest_authorization_required_code()]);
        }

        return true;
    }

    function create_shipment(WP_REST_Request $request)
    {
        $logger = wc_get_logger();
        date_default_timezone_set("Asia/Jakarta");
        $data = $request->get_json_params();
        $order_id = $request->get_param("id");
        $logger->info("Order Number to Create : " . $order_id, [
            "source" => "biteship-create-orders",
        ]);

        try {
            $biteship_shipping = $this->get_biteship_shipping();
            $biteship_options = $biteship_shipping->get_options();
            $order = wc_get_order($order_id);
            $order_status = $order->get_status();
            $existing_biteship_order_id = $order->get_meta("biteship_order_id");
            $items = $this->map_order_items_to_biteship_items($order);
            $selected_shipping = $this->get_selected_biteship_shipping_from_order($order);

            if ($selected_shipping == null) {
                wp_send_json_error([
                    "error" => "no biteship shipping selected",
                ]);
            }

            $biteship_order = [
                "shipper_contact_name" => $biteship_shipping->settings["shipper_name"],
                "shipper_contact_phone" => $biteship_shipping->settings["shipper_phone_no"],
                "shipper_contact_email" => $biteship_shipping->settings["shipper_email"],
                "shipper_organization" => $biteship_shipping->settings["store_name"],
                "origin_contact_name" => isset($data["origin_contact_name"]) ? $data["origin_contact_name"] : $biteship_shipping->settings["shipper_name"],
                "origin_contact_phone" => isset($data["origin_contact_phone"]) ? $data["origin_contact_phone"] : $biteship_shipping->settings["shipper_phone_no"],
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
                "delivery_type" => "now",
                "delivery_date" => date("Y-m-d"),
                "delivery_time" => date("H:m"),
                "order_note" => "",
                "items" => $items,
                "shipping_biteship_multi_origins" => $order->get_meta("_shipping_biteship_multi_origins"),
            ];

            if ($biteship_options["order_status_update"]) {
                $biteship_webhook_secret_key = $biteship_shipping->get_webhook_secret_key();
                $plugin_dir_url = plugin_dir_url(__DIR__);
                $parsed_url = parse_url($plugin_dir_url);
                $webhook_url = $parsed_url["scheme"] . "://" . $parsed_url["host"] . "/wp-json/wc-biteship/v1/orders/" . $order_id . "/shipment-status";
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

            if ($order_status == "processing" && empty($existing_biteship_order_id)) {
                $result = $biteship_shipping->rest_adapter->create_order($biteship_order);
            } else {
                wp_send_json($order->get_data(), 200);
            }

            if (!$result["success"]) {
                return new WP_Error("woocommerce_rest_cannot_update", $result["error"], ["status" => 400]);
            }

            $selected_shipping->add_meta_data("biteship_order_id", $result["data"]["order_id"]);
            $selected_shipping->add_meta_data("tracking_waybill_id", $result["data"]["waybill_id"]);
            $selected_shipping->save_meta_data();

            $order->update_meta_data("biteship_order_id", $result["data"]["order_id"]);
            $order->update_meta_data("tracking_waybill_id", $result["data"]["waybill_id"]);
            $order->update_meta_data("tracking_status", $result["data"]["status"]);

            //Ilyasa  - Tempel Tracking History
            $history_status = [
                "status" => $result["data"]["status"],
                "note" => $result["data"]["note"],
                "updated_at" => $result["data"]["updated_at"],
            ];

            $existing_history_status = $order->get_meta("tracking_history");

            if (empty($existing_history_status)) {
                $existing_history_status = [$history_status];
            } else {
                $existing_history_status[] = $history_status;
            }

            $order->update_meta_data("tracking_history", $existing_history_status);
            $order->save();

            $response = $order->get_data();

            $logger->info("Data Response : " . print_r($response, true), [
                "source" => "biteship-create-orders",
            ]);
            wp_send_json($response, 200);
        } catch (Exception $e) {
            $logger->info("Data Response : " . $e->getMessage(), [
                "source" => "biteship-create-orders",
            ]);
            return new WP_Error("woocommerce_rest_cannot_update", $e->getMessage(), ["status" => 400]);
        }
    }

    function update_shipment_status(WP_REST_Request $request)
    {
        try {
            $logger = wc_get_logger();

            $body = $request->get_body();
            $body_data = json_decode($body);
            $logger->info("Data Request : " . print_r($body, true), [
                "source" => "biteship-tracking-orders",
            ]);

            $biteship_shipping = $this->get_biteship_shipping();
            $biteship_options = $biteship_shipping->get_options();
            $biteship_order_id = $body_data->order_id;
            $biteship_tracking_status = $body_data->status;
            $biteship_updated_at = $body_data->updated_at;
            $biteship_note = isset($body_data->note) ? $body_data->note : "";
            $biteship_waybill_id = $body_data->courier_waybill_id;

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
                    $woocommerce_order_id = $order->get_id();
                }
            }

            $woocommerce_order = wc_get_order($woocommerce_order_id);
            $woocommerce_order->update_meta_data("tracking_status", $biteship_tracking_status);

            $history_status = [
                "status" => $biteship_tracking_status,
                "note" => $biteship_note,
                "updated_at" => $biteship_updated_at,
            ];
            $existing_history_status = $woocommerce_order->get_meta("tracking_history");
            if (empty($existing_history_status)) {
                $existing_history_status = [$history_status];
            } else {
                $existing_history_status[] = $history_status;
            }
            $woocommerce_order->update_meta_data("tracking_history", $existing_history_status);

            if ($biteship_options["order_status_update"]) {
                if ($biteship_tracking_status == "delivered") {
                    $woocommerce_order->update_status("completed");
                }
            }

            $woocommerce_order->save();

            do_action("woocommerce_update_order", $woocommerce_order->get_id(), $woocommerce_order);

            $result = $woocommerce_order->get_data();
            $logger->info("Data Response : " . print_r($result, true), [
                "source" => "biteship-tracking-orders",
            ]);
            wp_send_json($result, 200);
        } catch (Exception $e) {
            $logger->info("Data Response : " . $e->getMessage(), [
                "source" => "biteship-create-orders",
            ]);
            return new WP_Error("woocommerce_rest_cannot_update", $e->getMessage(), ["status" => 400]);
        }
    }

    private function generate_token()
    {
        $token_to_encrypt = "B1T35H1P2023-SecretToken";
        $encryption_key = "B1T35H1P2023-SecretKey";
        $iv_length = openssl_cipher_iv_length("aes-256-cbc");
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted_token = openssl_encrypt($token_to_encrypt, "aes-256-cbc", $encryption_key, 0, $iv);
        update_option("iv_encryption", base64_encode($iv));
        update_option("token_encryption", $encrypted_token);
        return $encryption_key;
    }

    private function get_biteship_shipping()
    {
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $biteship_shipping = $shipping_methods["biteship"];
        return $biteship_shipping;
    }

    private function set_order_for_multi_origin($biteship_options, $biteship_order)
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

    private function order_has_fee($order, $fee_name)
    {
        foreach ($order->get_fees() as $fee) {
            if ($fee->get_name() == $fee_name) {
                return true;
            }
        }

        return false;
    }
}
