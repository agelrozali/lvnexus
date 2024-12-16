<?php
/**
 * Omnisend API Functions
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function is_omnisend_account_connected() {

	$response = new WP_REST_Response( (bool) Omnisend_Settings::get_brand_id() );
	$response->set_headers( array( 'Cache-Control' => 'no-cache, must-revalidate, max-age=0, no-store, private' ) );

	return $response;
}

function omnisend_get_system_status() {
	global $wp_version;
	$woocommerce_version = '0.0.0';
	if ( defined( 'WC_VERSION' ) ) {
		$woocommerce_version = WC_VERSION;
	}

	$plugin_version = Omnisend_Helper::omnisend_plugin_version();
	$web_server     = null;

	if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
		$web_server = explode( ' ', sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) )[0];
	}

	$body = array(
		'connected'         => true,
		'systemInfo'        => array(
			'webserver'          => $web_server,
			'phpVersion'         => PHP_VERSION,
			'wordpressVersion'   => $wp_version,
			'woocommerceVersion' => $woocommerce_version,
			'pluginVersion'      => $plugin_version,
		),
		'omnisend_settings' => array(
			'checkout_opt_in_status'             => Omnisend_Settings::get_checkout_opt_in_status(),
			'checkout_opt_in_text'               => Omnisend_Settings::get_checkout_opt_in_text(),
			'checkout_opt_in_preselected_status' => Omnisend_Settings::get_checkout_opt_in_preselected_status(),
			'contact_tag_status'                 => Omnisend_Settings::get_contact_tag_status(),
			'contact_tag'                        => Omnisend_Settings::get_contact_tag(),
			'logs_status'                        => Omnisend_Settings::get_logs_status(),
			'brand_id'                           => Omnisend_Settings::get_brand_id(),
		),
	);

	$response = new WP_REST_Response( $body );
	$response->set_headers( array( 'Cache-Control' => 'no-cache, must-revalidate, max-age=0, no-store, private' ) );

	return $response;
}

function omnisend_post_omnisend_settings( WP_REST_Request $request ) {
	$body = json_decode( $request->get_body(), true );

	if ( isset( $body['checkout_opt_in_status'] ) ) {
		Omnisend_Settings::set_checkout_opt_in_status( $body['checkout_opt_in_status'], Omnisend_Settings::SOURCE_API );
	}

	if ( isset( $body['checkout_opt_in_text'] ) ) {
		Omnisend_Settings::set_checkout_opt_in_text( $body['checkout_opt_in_text'], Omnisend_Settings::SOURCE_API );
	}

	if ( isset( $body['checkout_opt_in_preselected_status'] ) ) {
		Omnisend_Settings::set_checkout_opt_in_preselected_status( $body['checkout_opt_in_preselected_status'], Omnisend_Settings::SOURCE_API );
	}

	if ( isset( $body['contact_tag_status'] ) ) {
		Omnisend_Settings::set_contact_tag_status( $body['contact_tag_status'], Omnisend_Settings::SOURCE_API );
	}

	if ( isset( $body['contact_tag'] ) ) {
		Omnisend_Settings::set_contact_tag( $body['contact_tag'], Omnisend_Settings::SOURCE_API );
	}

	if ( isset( $body['logs_status'] ) ) {
		Omnisend_Settings::set_logs_status( $body['logs_status'], Omnisend_Settings::SOURCE_API );
	}
}

function omnisend_connect_account( WP_REST_Request $request ) {
	$body = json_decode( $request->get_body(), true );

	if ( ! isset( $body['brand_id'] ) || ! isset( $body['omnisend_api_key'] ) ) {
		return new WP_Error(
			'omnisend_missing_required_properties',
			'Missing required properties in request body.',
			array( 'status' => 400 )
		);
	}

	update_option( 'omnisend_connect_token', null );
	Omnisend_Settings::set_brand_id( $body['brand_id'] );
	update_option( 'omnisend_api_key', $body['omnisend_api_key'] );

	Omnisend_Logger::info( 'API KEY saved.' );
	Omnisend_Manager::update_account_info();
	Omnisend_Manager_Assistant::init_sync();

	return array( 'success' => true );
}

function omnisend_post_disconnect() {
	if ( Omnisend_Helper::is_omnisend_connected() ) {
		Omnisend_Logger::info( 'Disconnecting plugin via API' );
		Omnisend_Install::disconnect();

		$response = new WP_REST_Response();
		$response->set_status( 204 );
		return $response;
	}
}

function validate_connect_token( WP_REST_Request $request ) {
	$body = json_decode( $request->get_body(), true );

	if ( ! isset( $body['connect_token'] ) ) {
		return new WP_Error(
			'omnisend_missing_connect_token',
			'Missing connect token in request.',
			array( 'status' => 400 )
		);
	}

	$token = get_option( 'omnisend_connect_token', '' );

	if ( $token === '' ) {
		return new WP_Error(
			'omnisend_connect_denied',
			'Connect token is already used.',
			array( 'status' => 403 )
		);
	}

	if ( $token !== $request['connect_token'] ) {
		return new WP_Error(
			'omnisend_incorrect_connect_token',
			'Connect token is incorrect.',
			array( 'status' => 401 )
		);
	}

	return true;
}

function omnisend_rest_api_authorization( WP_REST_Request $request ) {
	do_action( 'litespeed_control_set_nocache' );
	$request_api_key = $request->get_header( 'x-api-key' );

	if ( ! $request_api_key ) {
		return new WP_Error(
			'requires_authentication',
			'Unauthorized',
			array( 'status' => 401 )
		);
	}

	$omnisend_api_key = get_option( 'omnisend_api_key', null );

	if ( ! $omnisend_api_key ) {
		return new WP_Error(
			'requires_authentication',
			'Unauthorized',
			array( 'status' => 401 )
		);
	}

	if ( $request_api_key !== $omnisend_api_key ) {
		return new WP_Error(
			'requires_authentication',
			'Unauthorized',
			array( 'status' => 401 )
		);
	}

	return true;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'omnisend-api/v1',
			'/connect',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'omnisend_connect_account',
				'permission_callback' => 'validate_connect_token',
			)
		);
		register_rest_route(
			'omnisend-api/v1',
			'/disconnect',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'omnisend_post_disconnect',
				'permission_callback' => 'omnisend_rest_api_authorization',
			)
		);
		register_rest_route(
			'omnisend-api/v1',
			'/connected',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'is_omnisend_account_connected',
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'omnisend-api/v1',
			'/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'omnisend_get_system_status',
				'permission_callback' => 'omnisend_rest_api_authorization',
			)
		);
		register_rest_route(
			'omnisend-api/v1',
			'/omnisend-settings',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'omnisend_post_omnisend_settings',
				'permission_callback' => 'omnisend_rest_api_authorization',
			)
		);
	}
);
