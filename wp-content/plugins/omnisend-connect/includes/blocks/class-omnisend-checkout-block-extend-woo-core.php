<?php
/**
 * Omnisend Checkout Block Extend Woo Core
 *
 * @package OmnisendPlugin
 */

use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

class Omnisend_Checkout_Block_Extend_Woo_Core {

	/**
	 * Bootstraps the class and hooks required data.
	 */
	public static function init() {
		add_action(
			'woocommerce_store_api_checkout_update_order_from_request',
			function ( \WC_Order $order, \WP_REST_Request $request ) {
				self::update_contact_status( $order, $request );
			},
			10,
			2
		);
	}

	private static function update_contact_status( \WC_Order $order, \WP_REST_Request $request ) {
		$email_consent = $request['extensions'][ OMNISEND_CHECKOUT_PLUGIN_NAME ]['optin'];
		if ( ! $email_consent ) {
			return; // consent was not provided.
		}

		$order->add_meta_data( 'marketing_opt_in_consent', 'checkout', true );
		$order->save();
		$status_date = gmdate( DATE_ATOM, $order->get_date_created()->getTimestamp() ?? time() );
		$identifiers = array();

		$email = $order->get_billing_email();
		if ( $email != '' ) {
			$email_identifier = array(
				'type'     => 'email',
				'id'       => $email,
				'channels' => array(
					'email' => array(
						'status'     => 'subscribed',
						'statusDate' => $status_date,
					),
				),
			);
			array_push( $identifiers, $email_identifier );
		}

		$phone = $order->get_billing_phone();
		if ( $phone != '' ) {
			$phone_identifier = array(
				'type'     => 'phone',
				'id'       => $phone,
				'channels' => array(
					'sms' => array(
						'status'     => 'nonSubscribed',
						'statusDate' => $status_date,
					),
				),
			);
			array_push( $identifiers, $phone_identifier );
		}

		if ( count( $identifiers ) === 0 ) {
			return;
		}

		$tags = array( 'source: woocommerce' );
		$tag  = Omnisend_Settings::get_contact_tag_value();

		if ( $tag ) {
			$tags[] = $tag;
		}

		$prepared_contact = array(
			'identifiers' => $identifiers,
			'tags'        => $tags,
		);

		$link = OMNISEND_API_URL . '/v3/contacts';
		Omnisend_Helper::omnisend_api( $link, 'POST', $prepared_contact );
	}
}
