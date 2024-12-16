<?php
/**
 * Omnisend Checkout Block Endpoint
 *
 * @package OmnisendPlugin
 */

use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;

/**
 * Pickup Date Extend Store API
 */
class Omnisend_Checkout_Block_Extend_Store_Endpoint {
	/**
	 * Stores Rest Extending instance
	 *
	 * @var ExtendSchema
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin
	 *
	 * @var string
	 */
	const IDENTIFIER = OMNISEND_CHECKOUT_PLUGIN_NAME;

	/**
	 * Bootstraps the class and hooks required data
	 */
	public static function init() {
		self::$extend = StoreApi::container()->get( ExtendSchema::class );
		self::extend_store();
	}

	/**
	 * Registers the actual data into each endpoint
	 */
	public static function extend_store() {

		if ( is_callable( array( self::$extend, 'register_endpoint_data' ) ) ) {
			self::$extend->register_endpoint_data(
				array(
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'schema_callback' => array( self::class, 'extend_checkout_schema' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}
	}


	/**
	 * Register Pickup date schema into the Checkout endpoint
	 *
	 * @return array Registered schema
	 */
	public static function extend_checkout_schema() {
		return array(
			'optin' => array(
				'description' => 'Subscribed to newsletter',
				'type'        => 'bool',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'arg_options' => array(
					'validate_callback' => function ( $value ) {
						if ( ! is_null( $value ) && ! is_bool( $value ) ) {
							return new WP_Error( 'api-error', 'value of type ' . gettype( $value ) . ' was posted to the newsletter optin callback' );
						}
						return true;
					},
					'sanitize_callback' => function ( $value ) {
						if ( is_bool( $value ) ) {
							return $value;
						}
						return false;
					},
				),
			),
		);
	}
}
