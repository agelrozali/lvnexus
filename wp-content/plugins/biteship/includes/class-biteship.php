<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://biteship.com/
 * @since      1.0.0
 *
 * @package    Biteship
 * @subpackage Biteship/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Biteship
 * @subpackage Biteship/includes
 * @author     Biteship
 */
class Biteship
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Biteship_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;
    protected $plugin_public;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined("BITESHIP_VERSION")) {
            $this->version = BITESHIP_VERSION;
        } else {
            $this->version = "1.0.0";
        }
        $this->plugin_name = "biteship";

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->notification();
        $this->define_controller_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Biteship_Loader. Orchestrates the hooks of the plugin.
     * - Biteship_i18n. Defines internationalization functionality.
     * - Biteship_Admin. Defines all hooks for the admin area.
     * - Biteship_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . "includes/class-biteship-loader.php";

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . "includes/class-biteship-i18n.php";

        require_once plugin_dir_path(dirname(__FILE__)) . "includes/class-biteship-rest-adapter.php";

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . "admin/class-biteship-admin.php";

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . "admin/class-biteship-controller.php";

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . "public/class-biteship-public.php";

        $this->loader = new Biteship_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Biteship_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Biteship_i18n();

        $this->loader->add_action("plugins_loaded", $plugin_i18n, "load_plugin_textdomain");
    }

    /**
     * Register all of the hooks related to the controller area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */

    private function define_controller_hooks()
    {
        $plugin_controller = new Biteship_Controller($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action("rest_api_init", $plugin_controller, "register_api_route");
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Biteship_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action("admin_enqueue_scripts", $plugin_admin, "enqueue_styles");
        $this->loader->add_action("admin_enqueue_scripts", $plugin_admin, "enqueue_scripts");
        $this->loader->add_action("admin_footer", $plugin_admin, "include_modal_order_biteship");
        $this->loader->add_action("admin_notices", $plugin_admin, "biteship_admin_order_notice");
        $this->loader->add_action("plugins_loaded", $plugin_admin, "on_loaded");
        $this->loader->add_action("woocommerce_order_item_add_line_buttons", $plugin_admin, "order_item_add_line_buttons");
        $this->loader->add_action("wp_ajax_biteship_admin_add_biteship_order_shipping", $plugin_admin, "add_biteship_order_shipping");
        $this->loader->add_action("wp_ajax_biteship_admin_fetch_shipping_rates", $plugin_admin, "fetch_shipping_rates");
        $this->loader->add_action("wp_ajax_biteship_admin_order_biteship", $plugin_admin, "order_biteship");
        $this->loader->add_action("wp_ajax_biteship_admin_shop_information", $plugin_admin, "get_shop_information");
        $this->loader->add_action("wp_ajax_biteship_admin_delete_order_biteship", $plugin_admin, "delete_order_biteship");
        $this->loader->add_action("wp_ajax_biteship_admin_get_order_trackings", $plugin_admin, "get_order_trackings");
        $this->loader->add_action("woocommerce_before_order_itemmeta", $plugin_admin, "show_order_biteship_shipping_button", 10, 3);
        $this->loader->add_action("manage_shop_order_posts_custom_column", $plugin_admin, "custom_order_list_column_content", 10, 2);
        $this->loader->add_action("manage_woocommerce_page_wc-orders_custom_column", $plugin_admin, "custom_order_list_column_content", 10, 2); //HPOS based order
        $this->loader->add_action("woocommerce_admin_order_data_after_billing_address", $plugin_admin, "custom_district_field_admin");

        $this->loader->add_filter("woocommerce_admin_shipping_fields", $this, "custom_address_field_admin");
        // $this->loader->add_filter ('woocommerce_default_address_fields', $this, 'override_default_address', 9999999);
        $this->loader->add_filter("manage_edit-shop_order_columns", $plugin_admin, "custom_order_list_column");
        $this->loader->add_filter("manage_woocommerce_page_wc-orders_columns", $plugin_admin, "custom_order_list_column"); //HPOS based order
        $this->loader->add_filter("bulk_actions-edit-shop_order", $plugin_admin, "custom_admin_order_list_bulk_actions");
        $this->loader->add_filter("bulk_actions-woocommerce_page_wc-orders", $plugin_admin, "custom_admin_order_list_bulk_actions"); //HPOS based order
        $this->loader->add_filter("handle_bulk_actions-edit-shop_order", $plugin_admin, "handle_bulk_create_biteship_order", 10, 3);
        $this->loader->add_filter("handle_bulk_actions-woocommerce_page_wc-orders", $plugin_admin, "handle_bulk_create_biteship_order", 10, 3); //HPOS based order
        $this->loader->add_filter("handle_bulk_actions-edit-shop_order", $plugin_admin, "handle_bulk_delete_biteship_order", 10, 3);
        $this->loader->add_filter("handle_bulk_actions-woocommerce_page_wc-orders", $plugin_admin, "handle_bulk_delete_biteship_order", 10, 3); //HPOS based order

        $this->loader->add_action("add_meta_boxes", $plugin_admin, "add_meta_box");
        $this->loader->add_filter("default_checkout_billing_country", $this, "default_checkout_country");
        $this->loader->add_filter("default_checkout_shipping_country", $this, "default_checkout_country");
        $this->loader->add_action("wp_ajax_biteship_admin_get_biteship_order_status", $plugin_admin, "get_biteship_order_status");
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Biteship_Public($this->get_plugin_name(), $this->get_version());
        $this->plugin_public = $plugin_public;
        $this->loader->add_action("wp_enqueue_scripts", $plugin_public, "enqueue_styles");
        $this->loader->add_action("wp_enqueue_scripts", $plugin_public, "enqueue_scripts");
        $this->loader->add_action("woocommerce_cart_calculate_fees", $plugin_public, "cart_calculate_fees");
        $this->loader->add_action("woocommerce_review_order_after_shipping", $plugin_public, "insurance_option_view");
        $this->loader->add_filter("woocommerce_available_payment_gateways", $plugin_public, "available_payment_gateway");
        $this->loader->add_action("woocommerce_before_checkout_form", $plugin_public, "add_checkout_loader");

        $this->loader->add_action("woocommerce_shipping_init", $this, "load_shipping_method");
        $this->loader->add_filter("woocommerce_shipping_methods", $this, "register_shipping_methods");

        $this->loader->add_filter("woocommerce_checkout_fields", $this, "custom_position_field");
        $this->loader->add_filter("woocommerce_default_address_fields", $this, "custom_address_field_customer");
        $this->loader->add_filter("woocommerce_states", $this, "custom_state");

        $this->loader->add_filter("woocommerce_cart_no_shipping_available_html", $this, "override_no_shipping_text");
        $this->loader->add_filter("woocommerce_no_shipping_available_html", $this, "override_no_shipping_text");
        $this->loader->add_filter("woocommerce_cart_shipping_packages", $this, "handle_shipping_position");
        $this->loader->add_action("woocommerce_thankyou", $this, "order_after_order_complete");

        //woocommerce_package_rates
        $this->loader->add_filter("plugin_action_links_biteship/biteship.php", $this, "action_links");
        $this->loader->add_filter("plugin_action_links_" . plugin_basename(plugin_dir_path(dirname(__FILE__))) . "/biteship.php", $this, "action_links"); // ilyasa - bugfix settings button dont show
        $this->loader->add_filter("gettext", $this, "translate_reply");
        $this->loader->add_filter("ngettext", $this, "translate_reply");
        $this->loader->add_filter("default_checkout_billing_country", $this, "default_checkout_country");
        $this->loader->add_filter("default_checkout_shipping_country", $this, "default_checkout_country");

        $this->loader->add_action("wp_ajax_get_order_detail", $this, "get_order_detail");

        //woocommerce_multiple_origin
        $this->loader->add_action("wp_ajax_biteship_public_get_multiple_origin", $this, "get_multiple_origin");
        $this->loader->add_action("wp_ajax_nopriv_biteship_public_get_multiple_origin", $this, "get_multiple_origin");
        $this->loader->add_action("woocommerce_checkout_update_order_review", $this, "action_woocommerce_checkout_update_order_review");
        $this->loader->add_action("woocommerce_after_checkout_validation", $this, "checkout_validation");

        //Ilyasa - Register REST API for Automate Update Tracking Status
        // $this->loader->add_action('rest_api_init', $this, 'register_tracking_api_endpoint');
    }

    //Ilyasa - Generate Token Security
    // function generate_token()
    // {
    // 	$token_to_encrypt = "B1T35H1P2023-SecretToken";
    // 	$encryption_key = "B1T35H1P2023-SecretKey";
    // 	$iv_length = openssl_cipher_iv_length('aes-256-cbc');
    // 	$iv = openssl_random_pseudo_bytes($iv_length);
    // 	$encrypted_token = openssl_encrypt($token_to_encrypt, 'aes-256-cbc', $encryption_key, 0, $iv);
    // 	update_option('iv_encryption', base64_encode($iv));
    // 	update_option('token_encryption', $encrypted_token);
    // 	return $encryption_key;
    // }

    //Ilyasa - REST API for Create Shipment
    // function create_shipment_api()
    // {
    // 	register_rest_route('wc-biteship/v1', '/orders/(?P<id>\d+)/shipments', array(
    // 		'methods' => 'POST',
    // 		'callback' => function (WP_REST_Request $request) {
    // 			$logger = wc_get_logger();
    // 			date_default_timezone_set("Asia/Jakarta");
    // 			$data = $request->get_json_params();
    // 			$order_id = $request->get_param('id');
    // 			$logger->info("Order Number to Create : " . $order_id, array("source" => "biteship-create-orders"));

    // 			//Get Webhook URL for Update Status Order Biteship
    // 			$plugin_dir_url = plugin_dir_url(__DIR__);
    // 			$parsed_url = parse_url($plugin_dir_url);
    // 			$webhook_url = $parsed_url['scheme'] . "://" . $parsed_url['host'] . "/wp-json/custom/v1/tracking-orders";

    // 			try {
    // 				$plugin_admin = new Biteship_Admin($this->get_plugin_name(), $this->get_version());
    // 				$biteship_shipping = $this->get_biteship_shipping();
    // 				$biteship_options = $biteship_shipping->get_options();
    // 				$order = wc_get_order($order_id);
    // 				$order_status = $order->get_status();
    // 				$existing_biteship_order_id = $order->get_meta('biteship_order_id');
    // 				$items = $this->map_order_items_to_biteship_items($order);
    // 				$selected_shipping = $this->get_selected_biteship_shipping_from_order($order);

    // 				if ($selected_shipping == null) {
    // 					wp_send_json_error(array('error' => 'no biteship shipping selected'));
    // 				}

    // 				$biteship_order = array(
    // 					'shipper_contact_name' => $biteship_shipping->settings['shipper_name'],
    // 					'shipper_contact_phone' => $biteship_shipping->settings['shipper_phone_no'],
    // 					'shipper_contact_email' => $biteship_shipping->settings['shipper_email'],
    // 					'shipper_organization' => $biteship_shipping->settings['store_name'],
    // 					'origin_contact_name' => isset($data['origin_contact_name']) ? $data['origin_contact_name'] : $biteship_shipping->settings['shipper_name'],
    // 					'origin_contact_phone' => isset($data['origin_contact_phone']) ? $data['origin_contact_phone'] : $biteship_shipping->settings['shipper_phone_no'],
    // 					'origin_address' => $biteship_options['new_address'],
    // 					'origin_note' => '',
    // 					'origin_postal_code' => $biteship_options['new_zipcode'],
    // 					'origin_coordinate_latitude' => $biteship_shipping->get_store_latitude(),
    // 					'origin_coordinate_longitude' => $biteship_shipping->get_store_longitude(),
    // 					'destination_contact_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
    // 					'destination_contact_phone' => $this->get_contact_phone($order),
    // 					'destination_contact_email' => '',
    // 					'destination_address' => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
    // 					'destination_postal_code' => $order->get_shipping_postcode(),
    // 					'destination_note' => '',
    // 					'destination_coordinate_latitude' => $this->get_latitude($order->get_meta('_shipping_biteship_location_coordinate')),
    // 					'destination_coordinate_longitude' => $this->get_longitude($order->get_meta('_shipping_biteship_location_coordinate')),
    // 					'courier_company' => $selected_shipping->get_meta('courier_code'),
    // 					'courier_type' => $selected_shipping->get_meta('courier_service_code'),
    // 					'delivery_type' => "now", //$_POST['deliveryTimeOption']
    // 					'delivery_date' => date("Y-m-d"), //$_POST['deliveryDate']
    // 					'delivery_time' => date("H:m"), //$_POST['deliveryTime']
    // 					'order_note' => '',
    // 					'items' => $items,
    // 					'shipping_biteship_multi_origins' => $order->get_meta('_shipping_biteship_multi_origins'),
    // 					'webhook_url' => $webhook_url
    // 				);

    // 				if ($this->order_has_fee($order, 'Biaya COD')) {
    // 					$item_total_price =  0;
    // 					foreach ($order->get_items() as  $item_id => $item) {
    // 						$active_price   = $item->get_total();
    // 						$item_total_price += $active_price;
    // 					}
    // 					$biteship_order['destination_cash_on_delivery'] = $item_total_price + $order->get_shipping_total();
    // 				}

    // 				if ($this->order_has_fee($order, 'Biaya asuransi')) {
    // 					$biteship_order['courier_insurance'] = $order->get_subtotal();
    // 				}

    // 				// multiple origins order
    // 				$biteship_order = $plugin_admin->set_order_for_multi_origin($biteship_options, $biteship_order);

    // 				if ($order_status == "processing" && empty($existing_biteship_order_id)) {
    // 					$result = $biteship_shipping->rest_adapter->create_order($biteship_order);
    // 				} else {
    // 					wp_send_json($order->get_data(), 200);
    // 				}

    // 				if (!$result['success']) {
    // 					return new WP_Error('woocommerce_rest_cannot_update', $result['error'], array('status' => 400));
    // 				}

    // 				$selected_shipping->add_meta_data('biteship_order_id', $result['data']['order_id']);
    // 				$selected_shipping->add_meta_data('tracking_waybill_id', $result['data']['waybill_id']);
    // 				$selected_shipping->save_meta_data();

    // 				$order->update_meta_data('biteship_order_id', $result['data']['order_id']);
    // 				$order->update_meta_data('tracking_waybill_id', $result['data']['waybill_id']);
    // 				$order->update_meta_data('tracking_status', $result['data']['status']);

    // 				//Ilyasa  - Tempel Tracking History
    // 				$history_status = [
    // 					"status" => $result['data']['status'],
    // 					"note" 		=> $result['data']['note'],
    // 					"updated_at" => $result['data']['updated_at']
    // 				];

    // 				$existing_history_status = $order->get_meta('tracking_history');

    // 				if (empty($existing_history_status)) {
    // 					$existing_history_status = [$history_status];
    // 				} else {
    // 					$existing_history_status[] = $history_status;
    // 				}

    // 				$order->update_meta_data('tracking_history', $existing_history_status);
    // 				$order->save();

    // 				$response = $order->get_data();

    // 				$logger->info("Data Response : " . print_r($response), array("source" => "biteship-create-orders"));
    // 				wp_send_json($response, 200);
    // 			} catch (Exception $e) {
    // 				$logger->info("Data Response : " . $e->getMessage(), array("source" => "biteship-create-orders"));
    // 				return new WP_Error('woocommerce_rest_cannot_update', $e->getMessage(), array('status' => 400));
    // 			}
    // 		},
    // 		'permission_callback' => function () {
    // 			if ( ! wc_rest_check_post_permissions( "shop_order", 'create' ) ) {
    // 				return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
    // 			}
    // 			return true;
    // 		}
    // 	));
    // }

    //Ilyasa - REST API for Automate Update Tracking Status
    // function register_tracking_api_endpoint()
    // {
    // 	register_rest_route('custom/v1', '/tracking-orders', array(
    // 		'methods'  => 'POST',
    // 		'callback' => function (WP_REST_Request $request) {
    // 			try {
    // 				$logger = wc_get_logger();

    // 				$body = $request->get_body();
    // 				$body_data = json_decode($body);
    // 				$logger->info("Data Request : " . print_r($body), array("source" => "biteship-tracking-orders"));

    // 				$biteship_order_id = $body_data->order_id;
    // 				$biteship_tracking_status = $body_data->status;
    // 				$biteship_waybill_id = $body_data->courier_waybill_id;

    // 				$meta_key = 'biteship_order_id';
    // 				$meta_value = $biteship_order_id;

    // 				$args = array(
    // 					'post_type'      => 'shop_order',
    // 					'meta_key'       => $meta_key,
    // 					'meta_value'     => $meta_value,
    // 					'meta_compare'   => '=',
    // 				);

    // 				$orders = wc_get_orders($args);
    // 				if (!empty($orders)) {
    // 					foreach ($orders as $order) {
    // 						$woocommerce_order_id = $order->get_id();
    // 					}
    // 				}

    // 				$woocommerce_order = wc_get_order($woocommerce_order_id);
    // 				$woocommerce_order->update_meta_data('tracking_status', $biteship_tracking_status);

    // 				$history_status = [
    // 					"status" => $biteship_tracking_status,
    // 					"note" 		=> "notes",
    // 					"updated_at" => "2023-12-01T00:00:00+07:00"
    // 				];
    // 				$existing_history_status = $woocommerce_order->get_meta('tracking_history');
    // 				if (empty($existing_history_status)) {
    // 					$existing_history_status = [$history_status];
    // 				} else {
    // 					$existing_history_status[] = $history_status;
    // 				}
    // 				$woocommerce_order->update_meta_data('tracking_history', $existing_history_status);
    // 				$woocommerce_order->save();

    // 				//refresh order
    // 				do_action('woocommerce_update_order', $woocommerce_order->get_id(), $woocommerce_order);

    // 				$result = $woocommerce_order->get_data();
    // 				$logger->info("Data Response : " . print_r($result), array("source" => "biteship-tracking-orders"));
    // 				wp_send_json($result, 200);
    // 			} catch (Exception $e) {
    // 				$logger->info("Data Response : " . $e->getMessage(), array("source" => "biteship-create-orders"));
    // 				return new WP_Error('woocommerce_rest_cannot_update', $e->getMessage(), array('status' => 400));
    // 			}
    // 		},
    // 		'permission_callback' => function () {
    // 			if ( ! wc_rest_check_post_permissions( "shop_order", 'create' ) ) {
    // 				return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
    // 			}
    // 			return true;
    // 		}
    // 	));
    // }
    //End

    function checkout_validation()
    {
        if (isset($_POST["billing_phone"])) {
            if (strlen($_POST["billing_phone"]) > 0 && preg_match("# #", $_POST["billing_phone"])) {
                return wc_add_notice(__("Billing No Tlp is invalid, need remove space.", "woocommerce"), "error");
            }
        }
        if (isset($_POST["shipping_phone"])) {
            if (strlen($_POST["shipping_phone"]) > 0 && preg_match("# #", $_POST["shipping_phone"])) {
                return wc_add_notice(__("Shipping No Tlp is invalid, need remove space.", "woocommerce"), "error");
            }
        }
        if (isset($_POST["billing_biteship_multi_origins"])) {
            if (strlen($_POST["billing_biteship_multi_origins"]) > 0 && strtolower($_POST["billing_biteship_multi_origins"]) === "none") {
                return wc_add_notice(__("Billing Pilih Alamat Toko is a required field.", "woocommerce"), "error");
            }
        }
        if (isset($_POST["shipping_biteship_multi_origins"])) {
            if (strlen($_POST["shipping_biteship_multi_origins"]) > 0 && strtolower($_POST["shipping_biteship_multi_origins"]) === "none") {
                return wc_add_notice(__("Shipping Pilih Alamat Toko is a required field.", "woocommerce"), "error");
            }
        }
    }

    function action_woocommerce_checkout_update_order_review()
    {
        $this->plugin_public->recalculate_shipping();
        return;
    }

    public function get_multiple_origin()
    {
        $this->plugin_public->get_multiple_origin();
    }

    function get_order_detail()
    {
        $this->plugin_public->get_order_detail();
    }

    function default_checkout_country()
    {
        return "ID";
    }

    function translate_reply($translated)
    {
        $list_prefix = ["Enter your address to view shipping options", "There are no shipping options available", "invalid or missing"];
        foreach ($list_prefix as $prefix) {
            if (preg_match("/" . $prefix . "/", $translated)) {
                $translated = "Masukkan alamat lengkap untuk menghitung ongkos kirim";
            }
        }
        return $translated;
    }

    function biteship_notification_menu()
    {
        $date_now = date("Y-m-d");
        if ($date_now < "2022-11-31") {
            echo '<div class="notice notice-info is-dismissible" data-nsldismissable="nsl_bf_2022" style="display:grid;grid-template-columns: 100px auto;padding-top: 7px;">
                <img alt="Nextend Social Login" src="https://biteship.com/images/logos/biteship.png" width="75" height="35" style="grid-row: 1 / 4; align-self: center;justify-self: center">
                <h3 style="margin:0;">Promo Biteship November 2022</h3>
                <p style="margin:0 0 2px;">Ada paket premium gratis untuk kamu sampai 31 November 2022, cek ongkir dan kirim barang dengan Gojek/Grab atau layanan kilat jadi lebih mudah dan murah. Buat kunci API Premium dan klaim promo sekarang <a href="https://bit.ly/3TRBAbP" target="_blank">disini</a>.</p>
				</div>';
        }
        if (!in_array("woocommerce/woocommerce.php", apply_filters("active_plugins", get_option("active_plugins")))) {
            echo '<div class="notice notice-error is-dismissible" data-nsldismissable="nsl_bf_2022" style="display:grid;grid-template-columns: 100px auto;padding-top: 7px;">
                <img alt="Nextend Social Login" src="https://biteship.com/images/logos/biteship.png" width="75" height="35" style="grid-row: 1 / 4; align-self: center;justify-self: center">
                <h3 style="margin:0;">Biteship Plugin Telah Aktif</h3>
                <p style="margin:0 0 2px;">Plugin Biteship untuk WooCommerce telah diinstal! Tapi kami mendeteksi tidak ada plugin WooCommerce yang terpasang. </br>
				Pastikan Anda telah menginstal plugin <a href="//' .
                $_SERVER["HTTP_HOST"] .
                '/wp-admin/plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=600&height=550" class="thickbox">WooCommerce</a> untuk mengunakan biteship.</p>
				</div>';
        }
    }

    private function notification()
    {
        $this->loader->add_action("admin_notices", $this, "biteship_notification_menu");
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Biteship_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    public function load_shipping_method()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . "includes/class-biteship-shipping-method.php";
    }

    public function register_shipping_methods($methods)
    {
        $methods["biteship"] = "Biteship_Shipping_Method";
        return $methods;
    }

    public function override_no_shipping_text($default)
    {
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $biteship_shipping = $shipping_methods["biteship"];
        if ($biteship_shipping != null) {
            $error = $biteship_shipping->shipping_calculation_error;
            if ($error != "") {
                return $error;
            }
        }

        return $default;
    }

    public function custom_address_field_admin($fields)
    {
        return $this->custom_address_field($fields, "admin");
    }

    public function custom_address_field_customer($fields)
    {
        return $this->custom_address_field($fields, "customer");
    }

    public function handle_shipping_position($packages)
    {
        $latitude = "";
        $longitude = "";
        if (isset($_POST["post_data"])) {
            parse_str($_POST["post_data"], $post_data);
        } else {
            $post_data = $_POST; // fallback for final checkout (non-ajax)
        }

        if (isset($post_data["position"])) {
            $position = sanitize_text_field($post_data["position"]);
            $tmp = explode(",", $position);
            if (count($tmp) > 1) {
                $latitude = $tmp[0];
                $longitude = $tmp[1];
            }
        }

        $enriched_packages = [];
        foreach ($packages as $package) {
            $enriched_package = $package;
            $enriched_package["destination"]["latitude"] = $latitude;
            $enriched_package["destination"]["longitude"] = $longitude;
            array_push($enriched_packages, $enriched_package);
        }

        return $enriched_packages;
    }

    public function action_links($links)
    {
        $setting_link = get_admin_url() . "admin.php?page=wc-settings&tab=shipping&section=biteship";
        $plugin_links = [
            '<a href="' . $setting_link . '">' . __("Settings", "biteship") . "</a>",
            '<a href="https://help.biteship.com/hc/id/sections/9968775316761-WooCommerce" target="_blank">' . __("Support", "support") . "</a>",
        ];
        return array_merge($plugin_links, $links);
    }

    public function custom_position_field($fields)
    {
        $fields["shipping"]["position"] = [
            "type" => "text",
            "class" => ["input-position"],
            "class" => ["hidden"],
        ];
        return $fields;
    }

    private function set_session_for_address_field()
    {
        // check is user already register . . .
        $biteship_new_district = ["" => __("Search Town / City", "pok")];
        $biteship_multi_origins = [
            "" => __("Pilih province dulu", "multi_origin"),
        ];
        $biteship_province = $this->load_province();
        $biteship_city = ["" => "Pilih Kota"];
        $biteship_district = ["" => "Pilih Kecamatan"];
        $biteship_zipcode = ["" => "Pilih Kode Pos"];

        if (isset(WC()->customer) && strlen(WC()->customer->get_billing()["email"]) > 0) {
            foreach (WC()->customer->get_meta_data() as $item) {
                if ($item->get_data()["key"] === "billing_biteship_new_district") {
                    $district_code = $item->get_data()["value"];
                    $district_code_name = $this->plugin_public->get_detail_info_new_district($district_code);
                    $biteship_new_district = [
                        $district_code => __($district_code_name, "pok"),
                    ];
                }
                if ($item->get_data()["key"] === "billing_biteship_multi_origins") {
                    $multi_origins_id = $item->get_data()["value"];
                    $multi_origin_name = $this->plugin_public->get_detail_multiple_origin_by_id($multi_origins_id);
                    $biteship_multi_origins = [
                        $district_code => __($multi_origin_name, "multi_origin"),
                    ];
                }

                if ($item->get_data()["key"] === "billing_biteship_city") {
                    $biteship_city = [
                        $item->get_data()["value"] => $item->get_data()["value"],
                    ];
                }

                if ($item->get_data()["key"] === "billing_biteship_district") {
                    $biteship_district = [
                        $item->get_data()["value"] => $item->get_data()["value"],
                    ];
                }

                if ($item->get_data()["key"] === "billing_biteship_zipcode") {
                    $biteship_zipcode = [
                        $item->get_data()["value"] => $item->get_data()["value"],
                    ];
                }
            }
        }
        return [$biteship_new_district, $biteship_multi_origins, $biteship_province, $biteship_city, $biteship_district, $biteship_zipcode];
    }

    // custom_address_field - for checkout page
    private function custom_address_field($fields, $user_type)
    {
        $biteship_shipping = $this->get_biteship_shipping();
        $list_of_provinsi = [];
        if ($biteship_shipping != null) {
            $show_gmap = true;
            $biteship_options = $biteship_shipping->get_options();

            $checkout_type = $biteship_options["checkout_type"];
            $multiple_origins_isactive = isset($biteship_options["multiple_origins_isactive"]) ? $biteship_options["multiple_origins_isactive"] : false;
            $shipping_service_enabled = is_bool($biteship_options["shipping_service_enabled"]) ? [] : $biteship_options["shipping_service_enabled"];
            if (isset($biteship_options["informationLicence"])) {
                $biteship_type = $biteship_options["informationLicence"]["type"];
                if (in_array($biteship_type, ["woocommerceFree", "woocommerceEssentials"])) {
                    $show_gmap = false;
                }
            }

            // check is user already register . . .
            list($biteship_new_district, $biteship_multi_origins, $biteship_province, $biteship_city, $biteship_district, $biteship_zipcode) = $this->set_session_for_address_field();

            if ($user_type === "customer") {
                $fields["first_name"]["label"] = "Nama Depan";
                $fields["last_name"]["label"] = "Nama Belakang";

                $fields["postcode"]["priority"] = [
                    "class" => ["hidden"],
                ];
                $fields["biteship_address"] = [
                    "label" => __("Alamat Jalan"),
                    "placeholder" => __("Jl. Apel No. 2, RT 4 RW 5"),
                    "required" => true,
                    "input_class" => ["form-control"],
                    "class" => ["form-row-wide"],
                    "priority" => 41,
                ];

                if ($checkout_type === "dropdown" || strlen($checkout_type) === 0) {
                    // All field keys in this array
                    $key_fields = ["company", "city", "state"];

                    // Loop through each address fields (billing and shipping)
                    foreach ($key_fields as $key_field) {
                        $fields[$key_field]["type"] = "textarea";
                        $fields[$key_field]["required"] = false;
                    }

                    $fields["biteship_province"] = [
                        "type" => "select",
                        "label" => __("Provinsi"),
                        "placeholder" => "Pilih Provinsi",
                        "required" => true,
                        "options" => $biteship_province,
                        "input_class" => ["form-control", "state_select"],
                        "class" => ["form-row-wide"],
                        "autocomplete" => "address-level1",
                        "priority" => 43,
                    ];
                    $fields["biteship_city"] = [
                        "type" => "select",
                        "label" => __("Kota"),
                        "placeholder" => "Pilih Kota",
                        "required" => true,
                        "options" => $biteship_city,
                        "input_class" => ["form-control", "state_select"],
                        "class" => ["form-row-wide"],
                        "autocomplete" => "address-level1",
                        "priority" => 44,
                    ];
                    $fields["biteship_district"] = [
                        "type" => "select",
                        "label" => __("Kecamatan"),
                        "placeholder" => "Pilih Kecamatan",
                        "required" => true,
                        "options" => $biteship_district,
                        "input_class" => ["form-control", "state_select"],
                        "class" => ["form-row-first"],
                        "autocomplete" => "address-level1",
                        "priority" => 45,
                    ];
                    $fields["biteship_zipcode"] = [
                        "type" => "select",
                        "label" => __("Kode Pos"),
                        "placeholder" => "Pilih Kode Pos",
                        "required" => true,
                        "options" => $biteship_zipcode,
                        "input_class" => ["form-control", "state_select"],
                        "class" => ["form-row-last"],
                        "autocomplete" => "address-level1",
                        "priority" => 46,
                    ];
                } elseif ($checkout_type === "smartsearch") {
                    $fields["biteship_new_district"] = [
                        "label" => __("Provinsi, Kota, Kecamatan, dan Kode Pos"),
                        "placeholder" => __("Ketik beberapa kata untuk membuka pilihan"),
                        "type" => "select",
                        "options" => $biteship_new_district,
                        "class" => ["update_totals_on_change", "address-field", "select2-ajax"],
                        "custom_attributes" => [
                            "data-action" => "pok_search_simple_address",
                            "data-nonce" => wp_create_nonce("search_city"),
                        ],
                        "required" => true,
                        "priority" => 43,
                    ];

                    $fields["state"]["required"] = true;
                    $fields["state"]["type"] = "textarea";
                    $fields["state"]["class"] = ["hidden"];

                    //TODO: not ussing this anymore, if wanna remove need remove in js too
                    $fields["biteship_district_info"] = [
                        "required" => false,
                        "type" => "textarea",
                        "class" => ["hidden"],
                        "priority" => 44,
                    ];
                }

                if ($multiple_origins_isactive && !preg_match("#edit\-address#", $_SERVER["REQUEST_URI"])) {
                    $fields["biteship_multi_origins"] = [
                        "type" => "select",
                        "label" => __("Pilih Alamat Toko"),
                        "required" => true,
                        "options" => $biteship_multi_origins,
                        "input_class" => ["form-control", "state_select"],
                        "class" => ["form-row-wide"],
                        "autocomplete" => "address-level1",
                        "priority" => 47,
                    ];
                }

                if ($show_gmap && count($shipping_service_enabled) > 0) {
                    $fields["biteship_location"] = [
                        "label" => __("Pin Alamat ( Opsional - Untuk pilihan kurir instan atau same-day )"),
                        "placeholder" => __("Pin Lokasimu"),
                        "type" => "textarea",
                        "input_class" => ["form-control"],
                        "class" => ["form-row-wide"],
                        "priority" => 49,
                    ];
                    $fields["biteship_location_coordinate"] = [
                        "label" => "Location",
                        "required" => false,
                        "priority" => 50,
                        "class" => ["hidden"],
                    ];
                }
            }
        }
        return $fields;
    }

    function custom_state($states)
    {
        return $this->plugin_public->get_list_state($states);
    }

    private function load_province()
    {
        $biteship_province = [
            "" => "Pilih Provinsi",
        ];
        $load_state = $this->plugin_public->get_list_state([]);
        if (count($load_state) > 0) {
            foreach ($load_state["ID"] as $stateID => $stateName) {
                $biteship_province[$stateID] = $stateName;
            }
        }
        return $biteship_province;
    }

    private function get_biteship_shipping()
    {
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $biteship_shipping = $shipping_methods["biteship"];
        return $biteship_shipping;
    }

    public function order_after_order_complete($order_id)
    {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        $products = [];
        foreach ($order->get_items() as $item_key => $item) {
            $product = $item->get_product();
            $products[] = [
                "price" => $product->price,
                "productName" => $product->name,
            ];
        }

        $order_data = $order->get_data();
        $order_date_created = $order_data["date_created"]->date("Y-m-d H:i:s");
        $payload = [
            "products" => $products,
            "orderNumber" => "$order_id",
            "createdDate" => $order_date_created,
            "shippingMethod" => $order->get_shipping_method(),
            "paymentMethod" => $order->get_payment_method_title(),
            "billingAddressPostalCode" => $order->get_billing_postcode(),
            "billingAddressKecamatan" => $order->get_meta("_billing_biteship_new_district"),
            "totalPayment" => $order->get_total(), // (Total Product Price + Ongkir)
            "totalProductPrice" => $item->get_subtotal(),
            "totalOngkir" => $order->get_total() - $item->get_subtotal(),
        ];
        $this->plugin_public->save_order($payload);
    }
}
