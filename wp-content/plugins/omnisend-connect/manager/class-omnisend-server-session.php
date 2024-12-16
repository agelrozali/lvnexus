<?php
/**
 * Omnisend Server Session Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Server_Session {

	/**
	 * @param string $key
	 * @param mixed  $data
	 *
	 * @return bool
	 */
	public static function set( $key, $data ) {
		if ( ! self::is_session_available() ) {
			return false;
		}

		WC()->session->set( $key, $data );

		return true;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed (null if not found or session not available)
	 */
	public static function get( $key ) {
		if ( ! self::is_session_available() ) {
			return null;
		}

		return WC()->session->get( $key );
	}

	private static function is_session_available() {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		if ( WC()->session == null ) {
			return false;
		}

		return true;
	}
}
