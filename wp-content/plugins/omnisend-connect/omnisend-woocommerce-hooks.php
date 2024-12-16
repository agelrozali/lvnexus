<?php
/**
 * Omnisend WooCommerce Hook Functions
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

$picker_product_set = false;

/* PRODUCTS */

add_action( 'woocommerce_new_product', 'omnisend_on_product_change', 100, 1 );
add_action( 'woocommerce_update_product', 'omnisend_on_product_change', 100, 1 );
add_action( 'trash_product', 'omnisend_product_delete' );

/**
 * Product create or update
 *
 * @param int $post_id Product ID.
 */
function omnisend_on_product_change( $post_id ) {
	Omnisend_Logger::hook();
	remove_action( 'woocommerce_update_product', 'omnisend_on_product_change' );
	if ( Omnisend_Helper::is_woocommerce_plugin_activated() ) {
		Omnisend_Manager::push_product_to_omnisend( $post_id );
	}
}

/**
 * Product create or update
 *
 * @param int $post_id Product ID.
 */
function omnisend_product_delete( $post_id ) {
	Omnisend_Logger::hook();
	if ( Omnisend_Helper::is_woocommerce_plugin_activated() ) {
		Omnisend_Manager::delete_product_from_omnisend( $post_id );
	}
}

/* Product page - add Product Picker */
add_action( 'woocommerce_after_single_product', 'omnisend_product_picker', 5 );
function omnisend_product_picker() {
	Omnisend_Logger::hook();
	global $picker_product_set;
	if ( ! $picker_product_set ) {
		$picker_product_set = true;
		Omnisend_Product::product_picker();
	}
}

/* PRODUCT CATEGORIES */

add_action( 'edited_product_cat', 'omnisend_on_category_change', 10, 2 );
add_action( 'create_product_cat', 'omnisend_on_category_change', 10, 2 );
add_action( 'delete_product_cat', 'omnisend_category_delete', 10, 1 );

/**
 * Category create or update
 *
 * @param int $term_id Category ID.
 */
function omnisend_on_category_change( $term_id ) {
	Omnisend_Logger::hook();
	remove_action( 'edited_product_cat', 'omnisend_on_category_change' );
	Omnisend_Manager::push_category_to_omnisend( $term_id );
}

/**
 * Category create or update
 *
 * @param int $post_id Category ID.
 */
function omnisend_category_delete( $post_id ) {
	Omnisend_Logger::hook();
	if ( Omnisend_Helper::is_woocommerce_plugin_activated() ) {
		Omnisend_Manager::delete_category_from_omnisend( $post_id );
	}
}

/* CONTACTS */

add_action( 'profile_update', 'omnisend_on_user_update', 10, 2 );
function omnisend_on_user_update( $user_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::push_contact_to_omnisend( $user_id );
	Omnisend_Contact_Resolver::update_by_user_id( $user_id );
}

add_action( 'user_register', 'omnisend_on_user_register', 10, 1 );
function omnisend_on_user_register( $user_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::push_contact_to_omnisend( $user_id );
	Omnisend_Contact_Resolver::update_by_user_id( $user_id );
}

/* ORDERS */

/* Hook for triggering action when order created */
add_action( 'woocommerce_checkout_update_order_meta', 'omnisend_order_created', 20, 2 );
function omnisend_order_created( $order_id ) {
	Omnisend_Logger::hook();

	$cart_id        = Omnisend_Cart::get_or_set_cart_id();
	$attribution_id = Omnisend_User_Storage::get_attribution_id();

	if ( $cart_id || $attribution_id ) {
		$order = wc_get_order( $order_id );

		if ( $cart_id ) {
			$order->add_meta_data( 'omnisend_cartID', $cart_id, true );
		}

		if ( $attribution_id ) {
			$order->add_meta_data( 'omnisendAttributionID', $attribution_id, true );
		}

		$order->save();
	}

	Omnisend_Manager::push_order_to_omnisend( $order_id );
	Omnisend_Cart::reset();
}

/* Hook triggered when admin updates order */
add_action( 'woocommerce_process_shop_order_meta', 'omnisend_order_updated', 50, 2 );
function omnisend_order_updated( $order_id ) {
	Omnisend_Logger::hook();
	if ( is_admin() ) {
		Omnisend_Manager::push_order_to_omnisend( $order_id );
	}
}

/*
Fulfillment statuses.
Hook for triggering action when order status is changed to Processing */
add_action( 'woocommerce_order_status_processing', 'omnisend_order_processing', 10, 1 );
function omnisend_order_processing( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'fulfillment', 'inProgress' );
}

/* Hook for triggering action when order status is changed to Completed */
add_action( 'woocommerce_order_status_completed', 'omnisend_order_completed', 10, 1 );
function omnisend_order_completed( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'fulfillment', 'fulfilled' );
}

/*
Payment statuses.
Hook for triggering action when order status is changed to Pending */
add_action( 'woocommerce_order_status_pending', 'omnisend_order_pending', 10, 1 );
function omnisend_order_pending( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'payment', 'awaitingPayment' );
}

/* Hook for triggering action when order status is changed to Cancelled */
add_action( 'woocommerce_order_status_cancelled', 'omnisend_order_cancelled', 10, 1 );
function omnisend_order_cancelled( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'payment', 'voided' );
}

/* Hook for triggering action when order status is changed to Refunded */
add_action( 'woocommerce_order_status_refunded', 'omnisend_order_refunded', 10, 1 );
function omnisend_order_refunded( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'payment', 'refunded' );
}

/* Hook for triggering action when order Payment is complete */
add_action( 'woocommerce_payment_complete', 'omnisend_order_payment_completed', 10, 1 );
function omnisend_order_payment_completed( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'payment', 'paid' );
}

/* Hook for triggering action when order Payment failed (order status set to Failed) */
add_action( 'woocommerce_order_status_failed', 'omnisend_order_payment_failed', 10, 1 );
function omnisend_order_payment_failed( $order_id ) {
	Omnisend_Logger::hook();
	Omnisend_Manager::update_order_status( $order_id, 'payment', 'awaitingPayment' );
}

/* CARTS */
add_action( 'woocommerce_add_to_cart', 'omnisend_track_add_to_cart_event', 25, 5 );
function omnisend_track_add_to_cart_event( $cart_item_key, $product_id, $request_quantity, $variation_id, $variation ) {
	Omnisend_Logger::hook();
	Omnisend_Cart_Event::added_product_to_cart( gmdate( DATE_ATOM, time() ), $cart_item_key, $product_id, $variation_id, $variation, $request_quantity );
}

add_action( 'wp_enqueue_scripts', 'omnisend_add_snippet_script' );
function omnisend_add_snippet_script() {
	if ( Omnisend_Helper::is_woocommerce_plugin_activated() && Omnisend_Helper::check_wp_wc_compatibility() ) {
		$omnisend_account_id = get_option( 'omnisend_account_id', null );

		if ( $omnisend_account_id !== null ) {
			Omnisend_Logger::hook();

			$file_name = 'omnisend-snippet-script.js';
			$file_path = plugin_dir_url( __FILE__ ) . 'assets/js/' . $file_name;
			wp_register_script( $file_name, $file_path, array(), '1.0.0', true );
			wp_localize_script(
				$file_name,
				'omnisend_snippet_inputs',
				array(
					'brand_id' => $omnisend_account_id,
				)
			);
			wp_enqueue_script( $file_name, $file_path, array(), '1.0.0', true );

			wp_enqueue_script( 'launcher-v2.js', OMNISEND_SNIPPET_URL, array(), '1.0.0', true );
		}
	}
}

add_action( 'wp_enqueue_scripts', 'omnisend_add_checkout_script' );
function omnisend_add_checkout_script() {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		Omnisend_Logger::hook();
		$file_name = 'omnisend-checkout-script.js';
		$file_path = plugin_dir_url( __FILE__ ) . 'assets/js/' . $file_name . '?' . time();
		wp_register_script( $file_name, $file_path, array(), '1.0.0', true );
		wp_localize_script(
			$file_name,
			'omnisend_checkout_vars',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'contact_id' => Omnisend_User_Storage::get_contact_id(),
				'nonce'      => wp_create_nonce( 'omnisend-checkout-script-nonce' ),
			)
		);
		wp_enqueue_script( $file_name, $file_path, array(), '1.0.0', true );
	}
}

/**
 * Modify order webhook payload
 *
 * Add order status URL from Order via get_view_order_url() method
 *
 * @since 1.11.7
 */
function omnisend_add_order_status_url_to_webhook_payload( $payload, $resource_type ) {
	if ( $resource_type !== 'order' ) {
		return $payload;
	}

	if ( ! isset( $payload['id'] ) ) {
		return $payload;
	}

	$order = wc_get_order( $payload['id'] );
	if ( ! empty( $order ) && $order->get_user() ) {
		$order_url                            = esc_url( $order->get_view_order_url() );
		$payload['omnisend_order_status_url'] = $order_url ? $order_url : null;
	}

	return $payload;
}
add_filter( 'woocommerce_webhook_payload', 'omnisend_add_order_status_url_to_webhook_payload', 10, 4 );


/**
 * Identify user after login - save cookie
 */
function omnisend_wplogin( $user_login, $user ) {
	Omnisend_Logger::hook();
	Omnisend_Contact_Resolver::update_by_user_id( $user->ID );
}

add_action( 'wp_login', 'omnisend_wplogin', 10, 2 );

/* Add code snippet to the footer, if account ID is set */
add_action(
	'wp_footer',
	function () {
		global $picker_product_set;

		if ( Omnisend_Helper::is_woocommerce_plugin_activated() && Omnisend_Helper::check_wp_wc_compatibility() ) {
			$omnisend_account_id = get_option( 'omnisend_account_id', null );

			if ( $omnisend_account_id !== null ) {
				if ( is_product() && ! $picker_product_set ) {
					$picker_product_set = true;

					Omnisend_Product::product_picker();
				}
			}
		}
	}
);

/* Add verification tag */
add_action(
	'wp_head',
	function () {
		if ( Omnisend_Helper::is_woocommerce_plugin_activated() && Omnisend_Helper::check_wp_wc_compatibility() ) {

			$omnisend_account_id = get_option( 'omnisend_account_id', null );
			if ( $omnisend_account_id !== null ) {
				?>
				<meta name="omnisend-site-verification" content="<?php echo esc_attr( get_option( 'omnisend_account_id', null ) ); ?>"/>
				<?php
			}
		}
	}
);

function omnisend_checkbox_custom_checkout_field( $checkout ) {
	Omnisend_Logger::hook();

	$connected = Omnisend_Helper::is_omnisend_connected();

	if ( ! $connected ) {
		return;
	}

	woocommerce_form_field(
		'omnisend_newsletter_checkbox',
		array(
			'type'     => 'checkbox',
			'class'    => array( 'omnisend_newsletter_checkbox_field' ),
			'label'    => Omnisend_Settings::get_checkout_opt_in_text(),
			'value'    => true,
			'default'  => Omnisend_Settings::get_checkout_opt_in_preselected_status() === Omnisend_Settings::STATUS_ENABLED ? 1 : 0,
			'required' => false,
		),
		$checkout->get_value( 'omnisend_newsletter_checkbox' )
	);
}

function omnisend_update_contact_status( $order_id ) {
	Omnisend_Logger::hook();
	// Nonce verification is not required here - we listen for woocommerce hook, where woocommerce verifies nonce.
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_POST['omnisend_newsletter_checkbox'] ) && sanitize_text_field( wp_unslash( $_POST['omnisend_newsletter_checkbox'] ) ) ) {
		$order = wc_get_order( $order_id );
		$order->add_meta_data( 'marketing_opt_in_consent', 'checkout', true );
		$order->save();
		$status_date = gmdate( DATE_ATOM, $order->get_date_created()->getTimestamp() ?? time() );

		$identifiers = array();

		$billing_email = filter_input( INPUT_POST, 'billing_email', FILTER_SANITIZE_EMAIL );
		if ( $billing_email != '' ) {
			$email_identifier = array(
				'type'     => 'email',
				'id'       => $billing_email,
				'channels' => array(
					'email' => array(
						'status'     => 'subscribed',
						'statusDate' => $status_date,
					),
				),
			);
			array_push( $identifiers, $email_identifier );
		}

		$billing_phone = filter_input( INPUT_POST, 'billing_phone', FILTER_SANITIZE_NUMBER_INT );
		if ( $billing_phone != '' ) {
			$phone_identifier = array(
				'type'     => 'phone',
				'id'       => $billing_phone,
				'channels' => array(
					'sms' => array(
						'status'     => 'nonSubscribed',
						'statusDate' => $status_date,
					),
				),
			);
			array_push( $identifiers, $phone_identifier );
		}

		if ( count( $identifiers ) === 0 ) {
			return;
		}

		$tags = array( 'source: woocommerce' );
		$tag  = Omnisend_Settings::get_contact_tag_value();

		if ( $tag ) {
			$tags[] = $tag;
		}

		$prepared_contact = array(
			'identifiers' => $identifiers,
			'tags'        => $tags,
		);

		$link = OMNISEND_API_URL . '/v3/contacts';
		Omnisend_Helper::omnisend_api( $link, 'POST', $prepared_contact );
	}
}

if ( Omnisend_Settings::get_checkout_opt_in_status() === Omnisend_Settings::STATUS_ENABLED && Omnisend_Settings::get_checkout_opt_in_text() ) {
	// Add the checkbox field.
	add_action( 'woocommerce_after_checkout_billing_form', 'omnisend_checkbox_custom_checkout_field' );
	add_action( 'woocommerce_checkout_update_order_meta', 'omnisend_update_contact_status' );
}

add_action( 'omnisend_plugin_updated', 'omnisend_notify_about_plugin_update' );
add_action( 'omnisend_plugin_updated', 'omnisend_setup_omnisend_settings' );
add_action( 'omnisend_plugin_updated', 'omnisend_update_plugin_information' );
add_action( 'omnisend_plugin_updated', 'omnisend_setup_omnisend_tables' );
add_action( 'omnisend_wordpress_updated', 'omnisend_update_plugin_information' );

function omnisend_notify_about_plugin_update() {
	Omnisend_Logger::info( 'notifying about update' );
	Omnisend_Install::notify_about_plugin_update();
}

function omnisend_setup_omnisend_settings() {
	Omnisend_Settings::add_contact_tag( '', Omnisend_Settings::SOURCE_DEFAULT );
	Omnisend_Settings::add_contact_tag_status( omnisend_map_value_to_status( Omnisend_Settings::get_contact_tag() ), Omnisend_Settings::SOURCE_DEFAULT );

	Omnisend_Settings::add_checkout_opt_in_text( '', Omnisend_Settings::SOURCE_DEFAULT );
	Omnisend_Settings::add_checkout_opt_in_status( omnisend_map_value_to_status( Omnisend_Settings::get_checkout_opt_in_text() ), Omnisend_Settings::SOURCE_DEFAULT );
	Omnisend_Settings::add_checkout_opt_in_preselected_status( false );
}

function omnisend_update_plugin_information() {
	if ( ! empty( get_option( 'omnisend_api_key', null ) ) ) {
		Omnisend_Manager::update_account_info();
	}
}

function omnisend_setup_omnisend_tables() {
	Omnisend_Logger::ensure_table_exists();
	Omnisend_Contact_Cache::ensure_table_exists();
}

function omnisend_map_value_to_status( $value ) {
	return $value ? Omnisend_Settings::STATUS_ENABLED : Omnisend_Settings::STATUS_DISABLED;
}

add_action( 'plugins_loaded', 'omnisend_detect_environment_changes' );
function omnisend_detect_environment_changes() {
	$option_name = 'omnisend_environment';

	$current_environment  = wp_get_environment_type();
	$previous_environment = get_option( $option_name );

	if ( ! $previous_environment ) {
		Omnisend_Logger::info(
			'detected environment: ' . $current_environment . '. site: ' . Omnisend_Helper::get_domain( home_url() )
		);
	}

	if ( $previous_environment && $previous_environment !== $current_environment ) {
		Omnisend_Logger::info(
			"detected environment change $previous_environment -> $current_environment. site: " . Omnisend_Helper::get_domain( home_url() )
		);
		Omnisend_Install::disconnect();
	}

	update_option( $option_name, $current_environment );
}

add_action( 'plugins_loaded', 'omnisend_detect_domain_change' );
function omnisend_detect_domain_change() {
	$connected_domain_host = Omnisend_Helper::get_domain( get_option( 'omnisend_connected_domain' ) );
	$current_domain        = home_url();
	$current_domain_host   = Omnisend_Helper::get_domain( $current_domain );

	if ( $connected_domain_host && $connected_domain_host !== $current_domain_host ) {
		Omnisend_Logger::info( "detected site domain change $connected_domain_host -> $current_domain_host" );
	}

	update_option( 'omnisend_connected_domain', $current_domain );
}

add_action( 'plugins_loaded', 'omnisend_detect_plugin_updates' );
function omnisend_detect_plugin_updates() {

	$omnisend_plugin_version = Omnisend_Helper::omnisend_plugin_version();
	$version_db              = get_option( 'omnisend_plugin_version', '0.0.0' );

	if ( ! $omnisend_plugin_version ) {
		Omnisend_Logger::warning( "Cannot get Omnisend plugin version from file - version db: $version_db" );
		return;
	}

	if ( $omnisend_plugin_version != $version_db ) {
		update_option( 'omnisend_plugin_version', $omnisend_plugin_version );
		Omnisend_Logger::info( "Omnisend plugin updated - version db: $version_db  -> plugin version: $omnisend_plugin_version" );
	}

	if ( version_compare( $version_db, $omnisend_plugin_version, '<' ) ) {
		wp_schedule_single_event( time() + 60, 'omnisend_plugin_updated', array( $version_db, $omnisend_plugin_version ) );
	}
}

add_action( 'plugins_loaded', 'detect_omnisend_wordpress_updates' );
function detect_omnisend_wordpress_updates() {
	$omnisend_wordpress_version_db = get_option( 'omnisend_wp_version', null );
	$wordpress_version             = get_bloginfo( 'version' );

	if ( $omnisend_wordpress_version_db != $wordpress_version ) {
		update_option( 'omnisend_wp_version', $wordpress_version );
		Omnisend_Logger::info( "WordPress version updated $wordpress_version" );
		do_action( 'omnisend_wordpress_updated' );
	}
}


add_action( 'in_admin_header', 'omnisend_control_notices' );
function omnisend_control_notices(): void {
		$screen = get_current_screen();
	if ( $screen && ( $screen->id === 'toplevel_page_omnisend-woocommerce' ) ) {
		echo '<style>[class*="notice"]:not([class*="components"], [class*="omnisend-notice"], .notice), .notice:not([class*="omnisend-notice"]) { display: none !important; }</style>';
	}
}

?>
