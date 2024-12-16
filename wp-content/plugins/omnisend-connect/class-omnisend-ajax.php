<?php
/**
 * Omnisend AJAX Functions
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_omnisend_identify', 'omnisend_hook_omnisend_ajax_save_email' );
add_action( 'wp_ajax_nopriv_omnisend_identify', 'omnisend_hook_omnisend_ajax_save_email' );

function omnisend_hook_omnisend_ajax_save_email() {
	check_ajax_referer( 'omnisend-front-script-nonce' );

	Omnisend_Logger::hook();
	$email      = ! empty( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
	$contact_id = ! empty( $_GET['contact_id'] ) ? sanitize_text_field( wp_unslash( $_GET['contact_id'] ) ) : '';
	Omnisend_Logger::debug( "omnisend_hook_omnisend_ajax_save_email - received email: $email, contact_id: $contact_id" );
	Omnisend_Ajax::identify_by_email( $email, $contact_id );

	exit;
}

add_action( 'wp_ajax_omnisend_track_started_checkout_event', 'omnisend_track_started_checkout_event' );
add_action( 'wp_ajax_nopriv_omnisend_track_started_checkout_event', 'omnisend_track_started_checkout_event' );

function omnisend_track_started_checkout_event() {
	check_ajax_referer( 'omnisend-checkout-script-nonce' );

	Omnisend_Logger::hook();
	$email = ! empty( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
	Omnisend_Logger::debug( "omnisend_track_started_checkout_event - received email: $email" );
	Omnisend_Cart_Event::started_checkout( $email, gmdate( DATE_ATOM, time() ) );

	exit;
}

add_action( 'wp_ajax_omnisend_update_plugin_setting', 'omnisend_update_plugin_setting' );

function omnisend_update_plugin_setting() {
	check_ajax_referer( 'omnisend-settings-script-nonce' );

	Omnisend_Logger::hook();
	$setting_name  = isset( $_POST['setting_name'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_name'] ) ) : '';
	$setting_value = isset( $_POST['setting_value'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_value'] ) ) : '';
	Omnisend_Logger::info( "omnisend_update_plugin_setting - received setting_name: $setting_name, setting_value: $setting_value" );

	$setting_update_source = 'admin';

	switch ( $setting_name ) {
		case 'contact_tag':
			Omnisend_Settings::set_contact_tag( $setting_value, $setting_update_source );
			break;
		case 'contact_tag_status':
			Omnisend_Settings::set_contact_tag_status( $setting_value, $setting_update_source );
			break;
		case 'checkout_opt_in_status':
			Omnisend_Settings::set_checkout_opt_in_status( $setting_value, $setting_update_source );
			break;
		case 'checkout_opt_in_text':
			Omnisend_Settings::set_checkout_opt_in_text( $setting_value, $setting_update_source );
			break;
		case 'checkout_opt_in_preselected_status':
			Omnisend_Settings::set_checkout_opt_in_preselected_status( $setting_value, $setting_update_source );
			break;
		default:
			break;
	}

	exit;
}

class Omnisend_Ajax {
	/**
	 * @return Omnisend_Operation_Status
	 */
	public static function identify_by_email( $email, $contact_id ) {
		if ( ! Omnisend_Manager::is_setup() ) {
			return Omnisend_Operation_Status::error( 'Omnisend is not setup' );
		}

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return Omnisend_Operation_Status::error( 'Incorrect request (email)' );
		}

		if ( Omnisend_Contact_Resolver::update_by_email( $email ) ) {
			return Omnisend_Operation_Status::success();
		}

		$api_url     = OMNISEND_API_URL . '/v3/contacts';
		$curl_result = Omnisend_Helper::omnisend_api( $api_url, 'POST', self::generate_contact_payload( $email, $contact_id ) );
		if ( $curl_result['code'] < 200 || $curl_result['code'] >= 300 ) {
			Omnisend_Logger::log( 'warn', 'contacts', $api_url, 'Unable to push contact ' . $email . ' to Omnisend.' . $curl_result['response'] );

			return Omnisend_Operation_Status::error( 'Unable to create contact (api error)' );
		}
		Omnisend_Logger::log( 'info', 'contacts', $api_url, 'Contact ' . $email . ' was successfully pushed to Omnisend.' );

		$response = json_decode( $curl_result['response'], true );
		if ( empty( $response['contactID'] ) ) {
			Omnisend_Logger::log( 'warn', 'contacts', $api_url, 'Unable to push contact ' . $email . ' to Omnisend. Unexpected API response: ' . $curl_result['response'] );

			return Omnisend_Operation_Status::error( 'Unable to identify contact (api error)' );
		}

		Omnisend_Contact_Resolver::update_by_email_and_contact_id( $email, $response['contactID'] );

		return Omnisend_Operation_Status::success();
	}

	private static function generate_contact_payload( $email, $contact_id ) {
		$tags = array( 'source: woocommerce' );
		$tag  = Omnisend_Settings::get_contact_tag_value();

		if ( $tag ) {
			$tags[] = $tag;
		}

		$payload = array(
			'email'      => $email,
			'status'     => 'nonSubscribed',
			'statusDate' => gmdate( DATE_ATOM ),
			'tags'       => $tags,
		);
		if ( $contact_id ) {
			$payload['contactID'] = $contact_id;
		}
		return $payload;
	}
}
