<?php
/**
 * Omnisend Sync Stats Repository Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Utilities\OrderUtil;

class Omnisend_Sync_Stats_Repository {
	const POST_STATUS_PUBLISH  = 'publish';
	const POST_TYPE_SHOP_ORDER = 'shop_order';
	const POST_TYPE_PRODUCT    = 'product';

	public static function count_item( $endpoint, $inc = 1 ) {
		$key   = 'omnisend_' . $endpoint . '_sync_count';
		$count = intval( get_option( $key ) ) + $inc;
		update_option( $key, $count );
	}

	/**
	 * @return Omnisend_All_Sync_Stats
	 */
	public function get_all_stats() {
		return new Omnisend_All_Sync_Stats(
			$this->get_contact_stats(),
			$this->get_order_stats(),
			$this->get_product_stats(),
			$this->get_category_stats()
		);
	}

	/**
	 * @return Omnisend_Sync_Stats
	 */
	private function get_contact_stats() {
		$synced = array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => Omnisend_Sync::FIELD_NAME,
					'compare' => 'LIKE',
					'value'   => '20',
				),
			),
		);

		$not_synced = array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => Omnisend_Sync::FIELD_NAME,
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
			),
		);

		$skipped = array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'   => Omnisend_Sync::FIELD_NAME,
					'value' => Omnisend_Sync::STATUS_SKIPPED,
				),
			),
		);

		$error = array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'   => Omnisend_Sync::FIELD_NAME,
					'value' => Omnisend_Sync::STATUS_ERROR,
				),
			),
		);

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$unique_count = $wpdb->get_var( "SELECT COUNT(DISTINCT user_email) FROM $wpdb->users WHERE user_email != '' AND user_email IS NOT null" );

		return new Omnisend_Sync_Stats(
			$this->get_user_count( array() ),
			$unique_count,
			$this->get_user_count( $synced ),
			$this->get_user_count( $not_synced ),
			$this->get_user_count( $skipped ),
			$this->get_user_count( $error )
		);
	}

	/**
	 * @param array $query_params
	 *
	 * @return int
	 */
	private function get_user_count( $query_params ) {
		$general = array(
			'count_total' => true,
			'number'      => 1,
		);

		return ( new WP_User_Query( array_merge( $general, $query_params ) ) )->get_total();
	}

	/**
	 * @return Omnisend_Sync_Stats
	 */
	private function get_order_stats() {
		// HPOS is enabled.
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return $this->get_stats_from_orders();
		}

		// Traditional CPT-based orders are in use.
		return $this->get_stats_from_posts( self::POST_TYPE_SHOP_ORDER );
	}

	/**
	 * @return Omnisend_Sync_Stats
	 */
	private function get_stats_from_orders() {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT 
				COUNT(o.id) as total,
				SUM(IF(meta.meta_value LIKE %s, 1, 0)) as synced,
				SUM(IF(meta.meta_value IS NULL, 1, 0)) as notSynced,
				SUM(IF(meta.meta_value = %s, 1, 0)) as skipped,
				SUM(IF(meta.meta_value = %s, 1, 0)) as error
			FROM {$wpdb->prefix}wc_orders AS o
			LEFT JOIN {$wpdb->prefix}wc_orders_meta AS meta ON meta.order_id = o.id AND meta.meta_key = %s
			WHERE o.type = %s
			GROUP by '1';
			",
			'20%',
			Omnisend_Sync::STATUS_SKIPPED,
			Omnisend_Sync::STATUS_ERROR,
			Omnisend_Sync::FIELD_NAME,
			self::POST_TYPE_SHOP_ORDER
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$info = $wpdb->get_row( $sql, ARRAY_A );

		return new Omnisend_Sync_Stats(
			$this->get_array_field_or_zero( $info, 'total' ),
			$this->get_array_field_or_zero( $info, 'total' ),
			$this->get_array_field_or_zero( $info, 'synced' ),
			$this->get_array_field_or_zero( $info, 'notSynced' ),
			$this->get_array_field_or_zero( $info, 'skipped' ),
			$this->get_array_field_or_zero( $info, 'error' )
		);
	}

	/**
	 * @return Omnisend_Sync_Stats
	 */
	private function get_stats_from_posts( $post_type, $post_status = '' ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT 
				COUNT(ID) as total,
				SUM(IF(meta.meta_value LIKE %s, 1, 0)) as synced,
				SUM(IF(meta.meta_value IS NULL, 1, 0)) as notSynced,
				SUM(IF(meta.meta_value = %s, 1, 0)) as skipped,
				SUM(IF(meta.meta_value = %s, 1, 0)) as error
			FROM $wpdb->posts AS post
			LEFT JOIN $wpdb->postmeta AS meta ON meta.post_id = post.ID AND meta.meta_key = %s
			WHERE " .
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->build_where_sql_for_posts( $post_type, $post_status )
				. "
			GROUP by '1';
			",
			'20%',
			Omnisend_Sync::STATUS_SKIPPED,
			Omnisend_Sync::STATUS_ERROR,
			Omnisend_Sync::FIELD_NAME
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$info = $wpdb->get_row( $sql, ARRAY_A );

		return new Omnisend_Sync_Stats(
			$this->get_array_field_or_zero( $info, 'total' ),
			$this->get_array_field_or_zero( $info, 'total' ),
			$this->get_array_field_or_zero( $info, 'synced' ),
			$this->get_array_field_or_zero( $info, 'notSynced' ),
			$this->get_array_field_or_zero( $info, 'skipped' ),
			$this->get_array_field_or_zero( $info, 'error' )
		);
	}

	private function build_where_sql_for_posts( $post_type, $post_status = '' ) {
		global $wpdb;

		$sql = $wpdb->prepare( 'post_type = %s', $post_type );

		if ( $post_status ) {
			$sql .= $wpdb->prepare( ' AND post_status = %s', $post_status );
		}

		return $sql;
	}

	private function get_array_field_or_zero( $arr, $field ) {
		return ! empty( $arr[ $field ] ) ? $arr[ $field ] : 0;
	}

	/**
	 * @return Omnisend_Sync_Stats
	 */
	private function get_product_stats() {
		return $this->get_stats_from_posts( self::POST_TYPE_PRODUCT, self::POST_STATUS_PUBLISH );
	}

	/**
	 * @return Omnisend_Sync_Stats
	 */
	private function get_category_stats() {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT
				COUNT(t.term_id) as total,
				SUM(IF(m2.meta_value LIKE %s, 1, 0)) as synced,
				SUM(IF(m2.meta_value IS NULL, 1, 0)) as notSynced,
				SUM(IF(m2.meta_value = %s, 1, 0)) as skipped,
				SUM(IF(m2.meta_value = %s, 1, 0)) as error
			FROM $wpdb->terms AS t
			LEFT JOIN $wpdb->termmeta AS m1 ON ( t.term_id = m1.term_id )
			LEFT JOIN $wpdb->termmeta AS m2 ON ( t.term_id = m2.term_id AND m2.meta_key = %s )
			INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('product_cat') AND ( m1.meta_key = 'order' OR m1.meta_key IS NULL )
			GROUP by '1';
			",
			'20%',
			Omnisend_Sync::STATUS_SKIPPED,
			Omnisend_Sync::STATUS_ERROR,
			Omnisend_Sync::FIELD_NAME
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$info = $wpdb->get_row( $sql, ARRAY_A );

		return new Omnisend_Sync_Stats(
			$this->get_array_field_or_zero( $info, 'total' ),
			$this->get_array_field_or_zero( $info, 'total' ),
			$this->get_array_field_or_zero( $info, 'synced' ),
			$this->get_array_field_or_zero( $info, 'notSynced' ),
			$this->get_array_field_or_zero( $info, 'skipped' ),
			$this->get_array_field_or_zero( $info, 'error' )
		);
	}
}
