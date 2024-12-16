<?php
/**
 * Omnisend Operation Status Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Operation_Status {
	/**
	 * @var bool
	 */
	private $success;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @return Omnisend_Operation_Status
	 */
	public static function success() {
		return new Omnisend_Operation_Status( true, '' );
	}

	/**
	 * @param $message
	 *
	 * @return Omnisend_Operation_Status
	 */
	public static function error( $message ) {
		return new Omnisend_Operation_Status( false, $message );
	}

	/**
	 * @param $success bool
	 * @param $message string
	 */
	private function __construct( $success, $message ) {
		$this->success = $success;
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function to_string() {
		return wp_json_encode(
			array(
				'success' => $this->success,
				'message' => $this->message,
			)
		);
	}
}
