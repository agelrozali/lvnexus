<?php
/**
 * Omnisend Cart Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Cart {
	private const META_KEY = 'omnisend_cartID';

	public static function get_or_set_cart_id() {
		$cart_id = self::get_cart_id();
		self::set_cart_id( $cart_id );
		return $cart_id;
	}

	private static function get_cart_id() {
		$session_cart_id = self::get_session_cart_id();
		if ( $session_cart_id ) {
			return $session_cart_id;
		}

		$user_cart_id = self::get_user_cart_id();
		if ( $user_cart_id ) {
			return $user_cart_id;
		}

		return self::generate_cart_id();
	}

	private static function get_session_cart_id() {
		$cart_id = Omnisend_Server_Session::get( self::META_KEY );

		return $cart_id ? $cart_id : '';
	}

	private static function get_user_cart_id() {
		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			$cart_id = get_user_meta( $user_id, self::META_KEY, true );

			return $cart_id ? $cart_id : '';
		}

		return '';
	}

	private static function generate_cart_id() {
		return 'wc_cart_' . get_current_user_id() . '_' . time() . '_' . wp_rand( 1000, 9999 );
	}

	private static function set_cart_id( $cart_id ) {
		Omnisend_Server_Session::set( self::META_KEY, $cart_id );

		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			update_user_meta( $user_id, self::META_KEY, $cart_id );
		}
	}

	public static function reset() {
		Omnisend_Server_Session::set( self::META_KEY, '' );

		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			delete_user_meta( $user_id, self::META_KEY );
		}
	}
}
