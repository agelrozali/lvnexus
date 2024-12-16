<?php
/**
 * Omnisend Sync Manager Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Utilities\OrderUtil;

class Omnisend_Sync_Manager {

	public static function start_contacts() {
		delete_option( 'omnisend_sync_contacts_finished' );
		self::start_contacts_if_not_finished();
	}

	public static function start_contacts_if_not_finished() {
		$is_finished = get_option( 'omnisend_sync_contacts_finished', 0 ) == 1;

		if ( $is_finished ) {
			Omnisend_Logger::info( 'Contact sync is already finished' );
			return;
		}

		if ( wp_next_scheduled( 'omnisend_init_contacts_sync' ) ) {
			Omnisend_Logger::info( 'Contact sync is already started' );
			return;
		}

		Omnisend_Logger::info( 'Contact sync started' );
		wp_schedule_event( time(), 'omnisend_every_two_minutes', 'omnisend_init_contacts_sync' );

		self::start_check_batches_if_not_started();
	}

	public static function finish_contacts() {
		Omnisend_Logger::info( 'Contact sync finished' );
		update_option( 'omnisend_sync_contacts_finished', 1 );

		if ( wp_next_scheduled( 'omnisend_init_contacts_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_contacts_sync' );
		}
	}

	public static function stop_contacts( $reason ) {
		Omnisend_Logger::warning( 'Contact sync stopped: ' . $reason );

		if ( wp_next_scheduled( 'omnisend_init_contacts_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_contacts_sync' );
		}
	}

	public static function are_contacts_syncing() {
		if ( wp_next_scheduled( 'omnisend_init_contacts_sync' ) ) {
			return true;
		}

		return false;
	}

	public static function is_contacts_finished() {
		if ( get_option( 'omnisend_sync_contacts_finished' ) == 1 ) {
			return true;
		}

		return false;
	}

	public static function start_resync_contacts() {
		delete_metadata( 'user', '0', Omnisend_Sync::FIELD_NAME, '', true );
		self::start_contacts();
	}

	public static function start_orders() {
		delete_option( 'omnisend_sync_orders_finished' );
		self::start_orders_if_not_finished();
	}

	public static function start_orders_if_not_finished() {
		$is_finished = get_option( 'omnisend_sync_orders_finished', 0 ) == 1;

		if ( $is_finished ) {
			Omnisend_Logger::info( 'Order sync is already finished' );
			return;
		}

		if ( wp_next_scheduled( 'omnisend_init_orders_sync' ) ) {
			Omnisend_Logger::info( 'Order sync is already started' );
			return;
		}

		Omnisend_Logger::info( 'Order sync started' );
		wp_schedule_event( time(), 'omnisend_every_two_minutes', 'omnisend_init_orders_sync' );

		self::start_check_batches_if_not_started();
	}

	public static function finish_orders() {
		Omnisend_Logger::info( 'Order sync finished' );
		update_option( 'omnisend_sync_orders_finished', 1 );

		if ( wp_next_scheduled( 'omnisend_init_orders_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_orders_sync' );
		}
	}

	public static function stop_orders( $reason ) {
		Omnisend_Logger::warning( 'Order sync stopped: ' . $reason );

		if ( wp_next_scheduled( 'omnisend_init_orders_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_orders_sync' );
		}
	}

	public static function are_orders_syncing() {
		if ( wp_next_scheduled( 'omnisend_init_orders_sync' ) ) {
			return true;
		}

		return false;
	}

	public static function is_orders_finished() {
		if ( get_option( 'omnisend_sync_orders_finished' ) == 1 ) {
			return true;
		}

		return false;
	}

	public static function start_products() {
		delete_option( 'omnisend_sync_products_finished' );
		self::start_products_if_not_finished();
	}

	public static function start_products_if_not_finished() {
		$is_finished = get_option( 'omnisend_sync_products_finished', 0 ) == 1;

		if ( $is_finished ) {
			Omnisend_Logger::info( 'Product sync is already finished' );
			return;
		}

		if ( wp_next_scheduled( 'omnisend_init_products_sync' ) ) {
			Omnisend_Logger::info( 'Product sync is already started' );
			return;
		}

		Omnisend_Logger::info( 'Product sync started' );
		wp_schedule_event( time(), 'omnisend_every_two_minutes', 'omnisend_init_products_sync' );

		self::start_check_batches_if_not_started();
	}

	public static function finish_products() {
		Omnisend_Logger::info( 'Product sync finished' );
		update_option( 'omnisend_sync_products_finished', 1 );

		if ( wp_next_scheduled( 'omnisend_init_products_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_products_sync' );
		}
	}

	public static function stop_products( $reason ) {
		Omnisend_Logger::warning( 'Product sync stopped: ' . $reason );

		if ( wp_next_scheduled( 'omnisend_init_products_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_products_sync' );
		}
	}

	public static function are_products_syncing() {
		if ( wp_next_scheduled( 'omnisend_init_products_sync' ) ) {
			return true;
		}

		return false;
	}

	public static function is_products_finished() {
		if ( get_option( 'omnisend_sync_products_finished' ) == 1 ) {
			return true;
		}

		return false;
	}

	public static function start_categories() {
		delete_option( 'omnisend_sync_categories_finished' );
		self::start_categories_if_not_finished();
	}

	public static function start_categories_if_not_finished() {
		$is_finished = get_option( 'omnisend_sync_categories_finished', 0 ) == 1;

		if ( $is_finished ) {
			Omnisend_Logger::info( 'Category sync is already finished' );
			return;
		}

		if ( wp_next_scheduled( 'omnisend_init_categories_sync' ) ) {
			Omnisend_Logger::info( 'Category sync is already started' );
			return;
		}

		Omnisend_Logger::info( 'Category sync started' );
		wp_schedule_event( time(), 'omnisend_every_two_minutes', 'omnisend_init_categories_sync' );
	}

	public static function finish_categories() {
		Omnisend_Logger::info( 'Category sync finished' );
		update_option( 'omnisend_sync_categories_finished', 1 );

		if ( wp_next_scheduled( 'omnisend_init_categories_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_categories_sync' );
		}
	}

	public static function stop_categories( $reason ) {
		Omnisend_Logger::warning( 'Category sync stopped: ' . $reason );

		if ( wp_next_scheduled( 'omnisend_init_categories_sync' ) ) {
			wp_clear_scheduled_hook( 'omnisend_init_categories_sync' );
		}
	}

	public static function are_categories_syncing() {
		if ( wp_next_scheduled( 'omnisend_init_categories_sync' ) ) {
			return true;
		}

		return false;
	}

	public static function is_categories_finished() {
		if ( get_option( 'omnisend_sync_categories_finished' ) == 1 ) {
			return true;
		}

		return false;
	}

	public static function start_check_batches_if_not_started() {
		// Batch checker has already started.
		if ( wp_next_scheduled( 'omnisend_batch_check' ) ) {
			return;
		}

		Omnisend_Logger::info( 'Check batches started' );
		wp_schedule_event( time(), 'omnisend_every_two_minutes', 'omnisend_batch_check' );
	}

	public static function finish_check_batches() {
		if ( self::is_all_batches_sync_finished() && wp_next_scheduled( 'omnisend_batch_check' ) ) {
			Omnisend_Logger::info( 'Check batches finished' );
			wp_clear_scheduled_hook( 'omnisend_batch_check' );
		}
	}

	public static function start_resync_all_with_error_or_skipped() {
		delete_metadata( 'user', '0', Omnisend_Sync::FIELD_NAME, Omnisend_Sync::STATUS_ERROR, true );
		delete_metadata( 'post', '0', Omnisend_Sync::FIELD_NAME, Omnisend_Sync::STATUS_ERROR, true );
		delete_metadata( 'term', '0', Omnisend_Sync::FIELD_NAME, Omnisend_Sync::STATUS_ERROR, true );
		delete_metadata( 'user', '0', Omnisend_Sync::FIELD_NAME, Omnisend_Sync::STATUS_SKIPPED, true );
		delete_metadata( 'post', '0', Omnisend_Sync::FIELD_NAME, Omnisend_Sync::STATUS_SKIPPED, true );

		// HPOS is enabled.
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s AND meta_value IN (%s, %s)",
					Omnisend_Sync::FIELD_NAME,
					Omnisend_Sync::STATUS_ERROR,
					Omnisend_Sync::STATUS_SKIPPED
				)
			);
		}

		self::start_contacts();
		self::start_orders();
		self::start_products();
		self::start_categories();
	}

	public static function are_data_syncing() {
		if ( self::are_contacts_syncing() ) {
			return true;
		}

		if ( self::are_orders_syncing() ) {
			return true;
		}

		if ( self::are_products_syncing() ) {
			return true;
		}

		if ( self::are_categories_syncing() ) {
			return true;
		}

		return false;
	}

	private static function is_all_batches_sync_finished() {
		if ( self::are_contacts_syncing() ) {
			return false;
		}

		if ( self::are_orders_syncing() ) {
			return false;
		}

		if ( self::are_products_syncing() ) {
			return false;
		}

		return true;
	}
}
