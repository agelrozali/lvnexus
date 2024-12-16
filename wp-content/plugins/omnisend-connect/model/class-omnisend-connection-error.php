<?php
/**
 * Omnisend Connection Error Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Connection_Error {

	const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
	const UNAUTHORIZED          = 'UNAUTHORIZED';
	const BRAND_ALREADY_LINKED  = 'BRAND_ALREADY_LINKED';
	const SHOP_ALREADY_LINKED   = 'SHOP_ALREADY_LINKED';

	private $message;

	public function __construct( $code = '' ) {
		switch ( $code ) {
			case self::UNAUTHORIZED:
				$this->message = 'We couldn’t connect your account. Give it another try.';
				break;
			case self::BRAND_ALREADY_LINKED:
				$switch_url    = OMNISEND_SWITCH_BRANDS_URL . '?url=' . admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE );
				$this->message = 'This Omnisend store is already connected. <a id="connection-error-switch-brand" target="_self" href="' . $switch_url . '">Select another store from your account</a> and try again.';
				break;
			case self::SHOP_ALREADY_LINKED:
				$this->message = 'This store is already connected to another Omnisend account. Contact our support to help.';
				break;
			case self::INTERNAL_SERVER_ERROR:
			default:
				$this->message = 'We’re having some technical issues on our end. Try again in a bit.';
				break;
		}
	}

	public function get_message() {
		return $this->message;
	}
}
