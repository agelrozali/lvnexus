<?php
/**
 * Omnisend Sync Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Sync {

	const FIELD_NAME     = 'omnisend_last_sync';
	const STATUS_ERROR   = 'error';
	const STATUS_SKIPPED = 'skipped';

	public static function was_order_synced_before( $order_id ) {
		$last_sync = self::get_order_sync_status( $order_id );
		return ! empty( $last_sync ) && ! in_array( $last_sync, array( self::STATUS_ERROR, self::STATUS_SKIPPED ) );
	}

	public static function get_order_sync_status( $order_id ) {
		return self::get_order_meta_data( $order_id, self::FIELD_NAME );
	}

	public static function mark_order_sync_as_error( $order_id ) {
		self::update_order_meta_data( $order_id, self::STATUS_ERROR );
	}

	public static function mark_order_sync_as_skipped( $order_id ) {
		self::update_order_meta_data( $order_id, self::STATUS_SKIPPED );
	}

	public static function mark_order_sync_as_synced( $order_id ) {
		self::update_order_meta_data( $order_id, gmdate( DATE_ATOM ) );
	}

	public static function set_order_sync_status( $order_id, $status ) {
		self::update_order_meta_data( $order_id, $status );
	}

	public static function mark_contact_as_synced( $user_id ) {
		update_user_meta( $user_id, self::FIELD_NAME, gmdate( DATE_ATOM, time() ) );
	}

	public static function mark_contact_as_error( $user_id ) {
		update_user_meta( $user_id, self::FIELD_NAME, self::STATUS_ERROR );
	}

	public static function was_category_synced_before( $category_id ) {
		$last_sync = self::get_category_sync_status( $category_id );
		return ! empty( $last_sync ) && $last_sync != self::STATUS_ERROR;
	}

	public static function get_category_sync_status( $category_id ) {
		return get_term_meta( $category_id, self::FIELD_NAME, true );
	}

	public static function mark_category_sync_as_synced( $category_id ) {
		update_term_meta( $category_id, self::FIELD_NAME, gmdate( DATE_ATOM ) );
	}

	public static function get_order_meta_data( $order_id, $key ) {
		$order = wc_get_order( $order_id );

		return $order->get_meta( $key, true );
	}

	public static function delete_order_meta_data( $order_id, $key ) {
		$order = wc_get_order( $order_id );
		$order->delete_meta_data( $key );
		$order->save();
	}

	private static function update_order_meta_data( $order_id, $value ) {
		$order = wc_get_order( $order_id );
		$order->update_meta_data( self::FIELD_NAME, $value );
		$order->save();
	}
}
