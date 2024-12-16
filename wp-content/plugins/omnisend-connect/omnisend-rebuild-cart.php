<?php
/**
 * Omnisend Cart Rebuild Functions
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_rebuild_cart_page() {
	if ( is_admin() ) {
		return;
	}

	if ( is_omnisend_cart_rebuild_url() ) {
		Omnisend_Logger::hook();
		omnisend_rebuild_cart();
	}
}

function is_omnisend_cart_rebuild_url() {
	// Nonce verification is not required here.
	// phpcs:ignore WordPress.Security.NonceVerification
	return isset( $_GET['action'] ) && $_GET['action'] === 'rebuildCart';
}

function omnisend_rebuild_cart() {
	global $woocommerce;

	// Nonce verification is not required here.
	// phpcs:ignore WordPress.Security.NonceVerification
	$encoded_omni_cart = isset( $_GET['omniCart'] ) ? sanitize_text_field( wp_unslash( $_GET['omniCart'] ) ) : '';
	if ( empty( $encoded_omni_cart ) ) {
		exit;
	}

	$woocommerce->cart->empty_cart( true );
	$woocommerce->cart->get_cart();

	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$omni_cart = json_decode( base64_decode( $encoded_omni_cart ), true );

	$cart_products = $omni_cart['products'];

	foreach ( $cart_products as $product ) {
		if ( isset( $product['woosb_ids'] ) ) {
			$_REQUEST['woosb_ids'] = $product['woosb_ids'];
		}
		if ( isset( $product['wooco_ids'] ) ) {
			$_REQUEST['wooco_ids'] = $product['wooco_ids'];
		}
		$woocommerce->cart->add_to_cart(
			$product['product_id'],
			$product['quantity'],
			$product['variation_id'],
			$product['variation']
		);
	}

	$redirect_url = wc_get_cart_url();

	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		$redirect_url = add_query_arg( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), '', $redirect_url );
	}

	$redirect_url = remove_query_arg( array( 'action', 'omniCart' ), $redirect_url );
	wp_safe_redirect( esc_url( $redirect_url ) );
	exit;
}

add_action( 'wp_loaded', 'omnisend_rebuild_cart_page' );
