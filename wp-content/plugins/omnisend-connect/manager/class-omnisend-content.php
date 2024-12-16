<?php
/**
 * Omnisend Landing Page Content Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Content {
	const DEFAULT_CONTENT = array(
		'current_ecommerce_brands' => '100_000+',
	);


	private static $loaded_content = null;

	public static function get_prop( $prop ) {
		self::$loaded_content = self::get_content();

		return self::$loaded_content[ $prop ];
	}

	private static function get_content() {
		if ( self::$loaded_content ) {
			return self::$loaded_content;
		}

		$stored_content = json_decode( get_option( 'omnisend_landing_page_content', 'null' ), true );

		$api_content = self::get_content_from_api( ! $stored_content );

		if ( $api_content ) {
			update_option( 'omnisend_landing_page_content', wp_json_encode( $api_content ) );
		}

		if ( $stored_content ) {
			return $stored_content;
		}

		return self::DEFAULT_CONTENT;
	}

	private static function get_content_from_api( $init ) {
		$response = wp_remote_get(
			OMNISEND_LANDING_PAGE_CONTENT_URL . '?' . http_build_query( array( 'init' => $init ? 'true' : 'false' ) )
		);

		$response_code    = intval( wp_remote_retrieve_response_code( $response ) );
		$response_success = $response_code >= 200 && $response_code < 300;

		if ( is_wp_error( $response ) || ! $response_success ) {
			Omnisend_Logger::debug( $response->get_error_message() );

			return null;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
