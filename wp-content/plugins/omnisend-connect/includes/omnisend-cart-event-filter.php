<?php
/**
 * Omnisend Cart Event Filters
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'omnisend_cart_line_item', 'omnisend_wpc_checkout_item', 1, 2 );

/**
 * @param $line_item
 * @param $cart_item
 *
 * @return mixed
 */
function omnisend_wpc_checkout_item( $line_item, $cart_item ) {
	if ( ! class_exists( 'WPCleverWoosb' ) && ! class_exists( 'WPCleverWooco' ) ) {
		return $line_item;
	}

	if ( isset( $cart_item['woosb_keys'] ) ) {
		$line_item['_bundle_child_keys'] = $cart_item['woosb_keys'];
	}

	if ( isset( $cart_item['woosb_parent_key'] ) ) {
		$line_item['_bundle_parent_key'] = $cart_item['woosb_parent_key'];
	}

	if ( isset( $cart_item['woosb_ids'] ) ) {
		$line_item['_bundle_woosb_ids'] = $cart_item['woosb_ids'];
	}

	if ( isset( $cart_item['wooco_keys'] ) ) {
		$line_item['_bundle_child_keys'] = $cart_item['wooco_keys'];
	}

	if ( isset( $cart_item['wooco_parent_key'] ) ) {
		$line_item['_bundle_parent_key'] = $cart_item['wooco_parent_key'];
	}

	if ( isset( $cart_item['wooco_ids'] ) ) {
		$line_item['_bundle_wooco_ids'] = $cart_item['wooco_ids'];
	}

	return $line_item;
}
