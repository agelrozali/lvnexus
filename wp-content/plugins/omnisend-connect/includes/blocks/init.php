<?php
// phpcs:ignoreFile Squiz.Commenting.FileComment.Missing

/**
 * WooCommerce Blocks Initializer
 *
 * @package OmnisendPlugin
 */

const OMNISEND_CHECKOUT_PLUGIN_NAME = 'omnisend_consent';

add_action(
	'woocommerce_blocks_loaded',
	function () {
		if (!class_exists( '\Automattic\WooCommerce\Blocks\Package' ) ||
        !class_exists('\Automattic\WooCommerce\StoreApi\StoreApi') ||
	    !interface_exists('\Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface')) {
			return;
		}

		require_once 'class-omnisend-checkout-block-integration.php';
		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function ( $integration_registry ) {
				$integration_registry->register( new Omnisend_Checkout_Block_Integration() );
			}
		);

		require_once 'class-omnisend-checkout-block-extend-store-endpoint.php';
		Omnisend_Checkout_Block_Extend_Store_Endpoint::init();

		require_once 'class-omnisend-checkout-block-extend-woo-core.php';
		Omnisend_Checkout_Block_Extend_Woo_Core::init();
	}
);
