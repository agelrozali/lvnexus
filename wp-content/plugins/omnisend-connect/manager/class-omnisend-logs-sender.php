<?php
/**
 * Omnisend Logs Sender Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Logs_Sender {
	public static function send( $logs ) {
		$headers = array(
			'KEY'          => OMNISEND_LOGS_KEY,
			'Content-Type' => 'application/json',
		);
		$body    = array(
			'brandID'       => get_option( 'omnisend_account_id', '' ),
			'ipAddress'     => Omnisend_Helper::get_client_ip(),
			'email'         => get_option( 'admin_email' ),
			'website'       => home_url(),
			'pluginVersion' => Omnisend_Helper::omnisend_plugin_version(),
			'logEntries'    => $logs,
		);

		$response         = wp_remote_post(
			OMNISEND_LOGS_URL,
			array(
				'headers' => $headers,
				'body'    => wp_json_encode( $body, JSON_UNESCAPED_SLASHES ),
			)
		);
		$response_code    = intval( wp_remote_retrieve_response_code( $response ) );
		$response_success = $response_code >= 200 && $response_code < 300;

		if ( is_wp_error( $response ) || ! $response_success ) {
			Omnisend_Logger::debug( $response->get_error_message() );

			return false;
		}

		return true;
	}
}
