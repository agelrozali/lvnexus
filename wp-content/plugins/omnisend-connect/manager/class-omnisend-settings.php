<?php
/**
 * Omnisend Settings Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Settings {
	const SOURCE_DEFAULT = 'default';
	const SOURCE_ADMIN   = 'admin';
	const SOURCE_API     = 'api';

	const STATUS_ENABLED  = 'enabled';
	const STATUS_DISABLED = 'disabled';

	private const OPTION_CHECKOUT_OPT_IN_STATUS             = 'omnisend_checkout_opt_in_status';
	private const OPTION_CHECKOUT_OPT_IN_TEXT               = 'omnisend_checkout_opt_in_text';
	private const OPTION_CHECKOUT_OPT_IN_PRESELECTED_STATUS = 'omnisend_checkout_opt_in_preselected_status';

	private const OPTION_CONTACT_TAG_STATUS = 'omnisend_contact_tag_status';
	private const OPTION_CONTACT_TAG        = 'omnisend_contact_tag';

	/**
	 * @deprecated since version 1.14.0 use OPTION_LOGS_STATUS instead
	 */
	private const OPTION_LOG_ENABLED = 'omnisend_logEnabled';
	private const OPTION_LOGS_STATUS = 'omnisend_logs_status';

	private const OPTION_BRAND_ID = 'omnisend_account_id';

	private static $statuses = array( self::STATUS_ENABLED, self::STATUS_DISABLED );

	/**
	 * @return string
	 */
	public static function get_checkout_opt_in_status() {
		$status = get_option( self::OPTION_CHECKOUT_OPT_IN_STATUS, '' );

		if ( self::is_status( $status ) ) {
			return $status;
		}

		$text = self::get_checkout_opt_in_text();

		if ( $text ) {
			return self::STATUS_ENABLED;
		}

		return self::STATUS_DISABLED;
	}

	/**
	 * @param string $status
	 * @param string $source
	 */
	public static function add_checkout_opt_in_status( $status, $source ) {
		return self::add_option( self::OPTION_CHECKOUT_OPT_IN_STATUS, $status, $source, array( self::class, 'is_status' ) );
	}

	/**
	 * @param string $status
	 * @param string $source
	 */
	public static function set_checkout_opt_in_status( $status, $source ) {
		self::set_option( self::OPTION_CHECKOUT_OPT_IN_STATUS, $status, $source, array( self::class, 'is_status' ) );
	}

	/**
	 * @return string
	 */
	public static function get_checkout_opt_in_text() {
		return get_option( self::OPTION_CHECKOUT_OPT_IN_TEXT, '' );
	}

	/**
	 * @param string $value
	 * @param string $source
	 */
	public static function add_checkout_opt_in_text( $value, $source ) {
		return self::add_option( self::OPTION_CHECKOUT_OPT_IN_TEXT, $value, $source, 'is_string' );
	}

	/**
	 * @param string $value
	 * @param string $source
	 */
	public static function set_checkout_opt_in_text( $value, $source ) {
		self::set_option( self::OPTION_CHECKOUT_OPT_IN_TEXT, $value, $source, 'is_string' );
	}

	/**
	 * @return string
	 */
	public static function get_checkout_opt_in_preselected_status() {
		return get_option( self::OPTION_CHECKOUT_OPT_IN_PRESELECTED_STATUS, self::STATUS_DISABLED );
	}

	/**
	 * @param string $value
	 */
	public static function add_checkout_opt_in_preselected_status( $value ) {
		return self::add_option( self::OPTION_CHECKOUT_OPT_IN_PRESELECTED_STATUS, $value, self::SOURCE_DEFAULT, array( self::class, 'is_status' ) );
	}

	/**
	 * @param string $value
	 * @param string $source
	 */
	public static function set_checkout_opt_in_preselected_status( $value, $source ) {
		self::set_option( self::OPTION_CHECKOUT_OPT_IN_PRESELECTED_STATUS, $value, $source, array( self::class, 'is_status' ) );
	}

	/**
	 * @return string
	 */
	public static function get_contact_tag_status() {
		$status = get_option( self::OPTION_CONTACT_TAG_STATUS, '' );

		if ( self::is_status( $status ) ) {
			return $status;
		}

		$tag = self::get_contact_tag();

		if ( $tag ) {
			return self::STATUS_ENABLED;
		}

		return self::STATUS_DISABLED;
	}

	/**
	 * @param string $status
	 * @param string $source
	 */
	public static function add_contact_tag_status( $status, $source ) {
		return self::add_option( self::OPTION_CONTACT_TAG_STATUS, $status, $source, array( self::class, 'is_status' ) );
	}

	/**
	 * @param string $status
	 * @param string $source
	 */
	public static function set_contact_tag_status( $status, $source ) {
		self::set_option( self::OPTION_CONTACT_TAG_STATUS, $status, $source, array( self::class, 'is_status' ) );
	}

	/**
	 * @return string
	 */
	public static function get_contact_tag() {
		return get_option( self::OPTION_CONTACT_TAG, '' );
	}

	/**
	 * @return string
	 */
	public static function get_contact_tag_value() {
		if ( self::get_contact_tag_status() === self::STATUS_ENABLED ) {
			return self::get_contact_tag();
		}
		return '';
	}

	/**
	 * @param string $value
	 * @param string $source
	 */
	public static function add_contact_tag( $value, $source ) {
		return self::add_option( self::OPTION_CONTACT_TAG, $value, $source, 'is_string' );
	}

	/**
	 * @param string $value
	 * @param string $source
	 */
	public static function set_contact_tag( $value, $source ) {
		self::set_option( self::OPTION_CONTACT_TAG, $value, $source, 'is_string' );
	}

	/**
	 * @return bool
	 */
	public static function get_logs_status() {
		$status = get_option( self::OPTION_LOGS_STATUS, '' );

		if ( self::is_status( $status ) ) {
			return $status;
		}

		$legacy_status = get_option( self::OPTION_LOG_ENABLED, self::STATUS_DISABLED );

		if ( $legacy_status === '1' ) {
			return self::STATUS_ENABLED;
		}

		return self::STATUS_DISABLED;
	}

	/**
	 * @param string $value
	 * @param string $source
	 */
	public static function set_logs_status( $value, $source ) {
		self::set_option( self::OPTION_LOGS_STATUS, $value, $source, array( self::class, 'is_status' ) );
	}

	/**
	 * @return mixed
	 */
	public static function get_brand_id() {
		return get_option( self::OPTION_BRAND_ID, null );
	}

	/**
	 * @param string $value
	 */
	public static function set_brand_id( $value ) {
		if ( ! is_string( $value ) ) {
			return;
		}

		update_option( self::OPTION_BRAND_ID, $value );
	}

	private static function add_option( $option_name, $option_value, $source, $validator ) {
		if ( ! $validator( $option_value ) ) {
			return false;
		}

		Omnisend_Logger::info( sprintf( 'action: add %s as %s from %s', $option_name, $option_value, $source ) );

		$added = add_option( $option_name, $option_value );

		return $added;
	}

	private static function set_option( $option_name, $option_value, $source, $validator ) {
		if ( ! $validator( $option_value ) ) {
			return;
		}

		Omnisend_Logger::info( sprintf( 'action: update %s as %s from %s', $option_name, $option_value, $source ) );

		update_option( $option_name, $option_value );
	}

	private static function is_status( $status ) {
		return in_array( $status, self::$statuses );
	}
}
