<?php
/**
 * Omnisend Notifications Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Notifications {

	private const NOTIFICATION_NOT_SHOWN = 'shown';
	private const NOTIFICATION_DELAYED   = 'delayed';
	private const NOTIFICATION_DISABLED  = 'disabled';

	private const OPTION_CONNECTION_PAGE_VISIT_LAST_TIME    = 'omnisend_notification_connection_page_last_visit_time';
	private const OPTION_CONNECTION_PAGE_NOTIFICATION_STATE = 'omnisend_notification_connection_page_notification_state';
	private const CONNECTION_PAGE_NOTIFICATION_DELAY_TIME   = 21 * DAY_IN_SECONDS;

	const CONNECTION_NOTIFICATION  = 'connect_account';
	const WOOCOMMERCE_NOTIFICATION = 'fix_woocommerce';
	const NOTIFICATIONS            = array( self::CONNECTION_NOTIFICATION, self::WOOCOMMERCE_NOTIFICATION );

	/**
	 *
	 * @return void
	 */
	public static function set_connection_notification() {
		$current_time       = time();
		$notification_state = get_option( self::OPTION_CONNECTION_PAGE_NOTIFICATION_STATE, self::NOTIFICATION_NOT_SHOWN );

		if ( $notification_state === self::NOTIFICATION_NOT_SHOWN ) {
			$notification_state = self::NOTIFICATION_DELAYED;
		} elseif ( self::show_delayed_notification() ) {
			$notification_state = self::NOTIFICATION_DISABLED;
		}

		update_option( self::OPTION_CONNECTION_PAGE_NOTIFICATION_STATE, $notification_state );
		update_option( self::OPTION_CONNECTION_PAGE_VISIT_LAST_TIME, $current_time );
	}


	/**
	 * @param string $notification
	 *
	 * @return void
	 */
	public static function set_viewed( $notification ) {
		if ( self::is_valid( $notification ) && ! self::get_viewed( $notification ) ) {
			update_option( "omnisend_notification_{$notification}_viewed", true );
		}
	}

	/**
	 * @param string $notification
	 *
	 * @return bool
	 */
	public static function get_viewed( $notification ) {
		return self::is_valid( $notification ) && get_option( "omnisend_notification_{$notification}_viewed", null );
	}

	/**
	 * @return int
	 */
	public static function get_count() {
		$count = 0;

		foreach ( self::NOTIFICATIONS as $notification ) {
			if ( self::skip_notification( $notification ) ) {
				continue;
			}

			if ( get_option( "omnisend_notification_{$notification}_viewed", null ) ) {
				continue;
			}

			++$count;
		}

		if ( ! Omnisend_Helper::is_omnisend_connected() && self::show_delayed_notification() ) {
			++$count;
		}

		return $count;
	}

	/**
	 * @return bool
	 */
	private static function skip_notification( $notification ) {
		if ( $notification === self::WOOCOMMERCE_NOTIFICATION && ! Omnisend_Helper::is_woocommerce_plugin_activated() ) {
			return false;
		}

		if ( $notification === self::CONNECTION_NOTIFICATION && ! Omnisend_Helper::is_omnisend_connected() ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private static function is_valid( $notification ) {
		return in_array( $notification, self::NOTIFICATIONS );
	}

	/**
	 *
	 * @return bool
	 */
	private static function show_delayed_notification() {
		$current_time       = time();
		$last_visit_time    = get_option( self::OPTION_CONNECTION_PAGE_VISIT_LAST_TIME, $current_time );
		$notification_state = get_option( self::OPTION_CONNECTION_PAGE_NOTIFICATION_STATE, self::NOTIFICATION_NOT_SHOWN );

		if ( $notification_state === self::NOTIFICATION_DELAYED && ( $current_time - $last_visit_time ) > self::CONNECTION_PAGE_NOTIFICATION_DELAY_TIME ) {
			return true;
		}
		return false;
	}
}
