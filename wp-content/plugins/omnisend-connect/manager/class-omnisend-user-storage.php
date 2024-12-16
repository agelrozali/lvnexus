<?php
/**
 * Omnisend User Storage Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_User_Storage {

	/**
	 * @var array values set in running PHP process. When it is needed to set and get value in same process
	 */
	private static $params_set_in_process = array();

	public static function get_attribution_id() {
		return self::get( 'omnisendAttributionID' );
	}

	public static function get_contact_id() {
		return self::get( 'omnisendContactID' );
	}

	public static function set_contact_id( $contact_id ) {
		self::set( 'omnisendContactID', $contact_id );
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	private static function get( $key ) {
		if ( array_key_exists( $key, self::$params_set_in_process ) ) {
			Omnisend_Logger::debug( "Get cookie. Key $key. Value: " . self::$params_set_in_process[ $key ] );
			return self::$params_set_in_process[ $key ];
		}

		$value = ! empty( $_COOKIE[ $key ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $key ] ) ) : null;
		Omnisend_Logger::debug( "Get cookie. Key $key. Value: " . $value );

		return $value;
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 */
	private static function set( $key, $data ) {
		Omnisend_Logger::debug( "Saving to cookie '$key', value: " . wp_json_encode( $data ) );
		$host   = wp_parse_url( home_url(), PHP_URL_HOST );
		$expiry = strtotime( '+1 year' );
		setcookie( $key, $data, $expiry, '/', $host );
		self::$params_set_in_process[ $key ] = $data;
	}
}
