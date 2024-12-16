<?php
/**
 * Omnisend Manager Assistant Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Utilities\OrderUtil;

add_action( 'omnisend_init_contacts_sync', 'omnisend_init_contacts_sync' );
add_action( 'omnisend_init_orders_sync', 'omnisend_init_orders_sync' );
add_action( 'omnisend_init_products_sync', 'omnisend_init_products_sync' );
add_action( 'omnisend_init_categories_sync', 'omnisend_init_categories_sync' );
add_action( 'omnisend_batch_check', 'omnisend_batch_check' );

function omnisend_init_contacts_sync() {
	Omnisend_Manager_Assistant::sync_all_contacts();
	exit;
}

function omnisend_init_orders_sync() {
	Omnisend_Manager_Assistant::sync_all_orders();
	exit;
}

function omnisend_init_products_sync() {
	Omnisend_Manager_Assistant::sync_all_products();
	exit;
}

function omnisend_init_categories_sync() {
	Omnisend_Manager_Assistant::sync_all_categories();
	exit;
}

function omnisend_batch_check() {
	Omnisend_Manager_Assistant::batch_check();
	exit;
}

class Omnisend_Manager_Assistant {

	public static function batch_check() {
		Omnisend_Logger::hook();
		$batches = get_option( 'omnisend_batches_inProgress' );

		if ( empty( $batches ) ) {
			Omnisend_Sync_Manager::finish_check_batches();
			return;
		}

		$i              = 0;
		$remove_batches = array();
		$renew_orders   = 0;
		$renew_products = 0;
		$renew_contacts = 0;

		foreach ( $batches as $key => $batch_id ) {
			$link     = OMNISEND_API_URL . '/v3/batches/' . $batch_id;
			$response = Omnisend_Helper::omnisend_api( $link, 'GET', array() );
			if ( $response['code'] >= 200 && $response['code'] < 300 ) {
				$r = json_decode( $response['response'], true );
				if ( $r['status'] == 'finished' || $r['status'] == 'stopped' ) {
					if ( $r['errorsCount'] != 0 ) {
						// Check batch items.
						$link           = OMNISEND_API_URL . '/v3/batches/' . $batch_id . '/items';
						$response_batch = Omnisend_Helper::omnisend_api( $link, 'GET', array() );
						if ( $response_batch['code'] >= 200 && $response_batch['code'] < 300 ) {
							$r_batch = json_decode( $response_batch['response'], true );
							if ( ! empty( $r_batch['errors'] ) ) {
								foreach ( $r_batch['errors'] as $item ) {
									if ( $item['responseCode'] == '503' || $item['responseCode'] == '429' || $item['responseCode'] == '408' || $item['responseCode'] == '403' ) {
										// Retry...
										if ( $r['endpoint'] == 'orders' ) {
											$last_sync = Omnisend_Sync::get_order_meta_data( $item['request']['orderID'], Omnisend_Sync::FIELD_NAME );
											if ( $last_sync != '' && $last_sync != Omnisend_Sync::STATUS_ERROR ) {
												$last_sync = strtotime( $last_sync );
											}
											if ( $last_sync != Omnisend_Sync::STATUS_ERROR && ( $last_sync < ( strtotime( $r['createdAt'] ) + 30 ) || $last_sync == '' ) ) {
												Omnisend_Sync::delete_order_meta_data( $item['request']['orderID'], Omnisend_Sync::FIELD_NAME );
												$renew_orders = 1;
											}
										} elseif ( $r['endpoint'] == 'products' ) {
											$last_sync = get_post_meta( $item['request']['productID'], Omnisend_Sync::FIELD_NAME, true );
											if ( $last_sync != '' && $last_sync != Omnisend_Sync::STATUS_ERROR ) {
												$last_sync = strtotime( $last_sync );
											}
											if ( $last_sync != Omnisend_Sync::STATUS_ERROR && ( $last_sync < ( strtotime( $r['createdAt'] ) + 30 ) || $last_sync == '' ) ) {
												delete_post_meta( $item['request']['productID'], Omnisend_Sync::FIELD_NAME );
												$renew_products = 1;
											}
										} elseif ( $r['endpoint'] == 'contacts' ) {
											$user = get_user_by( 'email', $item['request']['email'] );
											if ( ! empty( $user ) ) {
												$last_sync = get_user_meta( $user->ID, Omnisend_Sync::FIELD_NAME, true );
												if ( $last_sync != '' && $last_sync != Omnisend_Sync::STATUS_ERROR ) {
													$last_sync = strtotime( $last_sync );
												}
												if ( $last_sync != Omnisend_Sync::STATUS_ERROR && ( $last_sync < ( strtotime( $r['createdAt'] ) + 30 ) || $last_sync == '' ) ) {
													delete_user_meta( $user->ID, Omnisend_Sync::FIELD_NAME );
													$renew_contacts = 1;
												}
											}
										}
									}
								}
							}
						}
					}
					// Remove batch from inProgress.
					$remove_batches[] = $batch_id;

				}
			} elseif ( $response['code'] == 404 ) {
				$remove_batches[] = $batch_id;
			}

			if ( $i > 3 ) {
				break;
			}

			++$i;
		}

		// Update inProgress batches.
		update_option( 'omnisend_batches_inProgress', array_diff( $batches, $remove_batches ) );

		// Reschedule sync cron jobs.
		if ( $renew_contacts == 1 ) {
			Omnisend_Sync_Manager::start_contacts();
		}

		if ( $renew_orders == 1 ) {
			Omnisend_Sync_Manager::start_orders();
		}

		if ( $renew_products == 1 ) {
			Omnisend_Sync_Manager::start_products();
		}
	}

	// Sync contacts via batches.
	public static function sync_all_contacts() {
		Omnisend_Logger::hook();
		if ( Omnisend_Sync_Manager::is_contacts_finished() ) {
			return;
		}

		if ( empty( get_option( 'omnisend_api_key', null ) ) ) {
			Omnisend_Sync_Manager::stop_contacts( 'no API key' );
			return;
		}

		$wp_user_query = new WP_User_Query(
			array(
				'number'     => 1000,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => Omnisend_Sync::FIELD_NAME,
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);
		$users         = $wp_user_query->get_results();

		if ( empty( $users ) ) {
			Omnisend_Sync_Manager::finish_contacts();
			return;
		}

		// Form batch request and save batchID.
		$args                = array();
		$args['method']      = 'POST';
		$args['endpoint']    = 'contacts';
		$args['items']       = array();
		$skipped_contact_ids = array();
		foreach ( $users as $user ) {
			$prepared_contact = Omnisend_Contact::create( $user );
			if ( $prepared_contact ) {
				$prepared_contact = Omnisend_Helper::clean_model_from_empty_fields( $prepared_contact );
				$args['items'][]  = $prepared_contact;
			} else {
				$skipped_contact_ids[] = $user->ID;
			}
		}
		$link     = OMNISEND_API_URL . '/v3/batches';
		$response = Omnisend_Helper::omnisend_api( $link, 'POST', $args );
		if ( $response['code'] >= 200 && $response['code'] < 300 ) {
			$status   = gmdate( DATE_ATOM, time() );
			$r        = json_decode( $response['response'], true );
			$batch_id = $r['batchID'];
			if ( strlen( $batch_id ) == 24 ) {
				// Write batch to check response later.
				$batches_in_progress = get_option( 'omnisend_batches_inProgress' );
				if ( ! is_array( $batches_in_progress ) ) {
					$batches_in_progress = array();
				}
				if ( ! in_array( $batch_id, $batches_in_progress ) ) {
					$batches_in_progress[] = $batch_id;
					update_option( 'omnisend_batches_inProgress', $batches_in_progress );
				}
				Omnisend_Logger::log( 'info', 'batches', $link, 'Batch sync: contacts were successfully pushed to Omnisend' );
			} else {
				Omnisend_Logger::log( 'warn', 'batches', $link, 'Batch sync error: unable to push contacts to Omnisend' );
				$status = Omnisend_Sync::STATUS_ERROR;
			}
		} else {
			Omnisend_Logger::log( 'warn', 'batches', $link, 'Batch sync error: unable to push contacts to Omnisend' );
			$status = Omnisend_Sync::STATUS_ERROR;
		}

		foreach ( $users as $user ) {
			// Update contact with last update date or mark it as "error" or "skipped".
			$user_status = in_array( $user->ID, $skipped_contact_ids ) ? Omnisend_Sync::STATUS_SKIPPED : $status;
			update_user_meta( $user->ID, Omnisend_Sync::FIELD_NAME, $user_status );
		}
	}

	// Sync orders via batches.
	public static function sync_all_orders() {
		Omnisend_Logger::hook();
		if ( Omnisend_Sync_Manager::is_orders_finished() ) {
			return;
		}

		if ( empty( get_option( 'omnisend_api_key', null ) ) ) {
			Omnisend_Sync_Manager::stop_orders( 'no API key' );
			return;
		}

		$orders = self::get_orders_to_sync();

		if ( empty( $orders ) ) {
			Omnisend_Sync_Manager::finish_orders();
			return;
		}

		// Form batch request and save batchID.
		$args              = array();
		$args['method']    = 'POST';
		$args['endpoint']  = 'orders';
		$args['items']     = array();
		$skipped_order_ids = array();

		foreach ( $orders as $order_id ) {
			$prepared_order = Omnisend_Order::create( $order_id );
			if ( $prepared_order ) {
				$prepared_order  = Omnisend_Helper::clean_model_from_empty_fields( $prepared_order );
				$args['items'][] = $prepared_order;
			} else {
				$skipped_order_ids[] = $order_id;
			}
		}

		if ( count( $args['items'] ) > 0 ) {
			$link     = OMNISEND_API_URL . '/v3/batches';
			$response = Omnisend_Helper::omnisend_api( $link, 'POST', $args );
			if ( $response['code'] >= 200 && $response['code'] < 300 ) {
				$status   = gmdate( DATE_ATOM, time() );
				$r        = json_decode( $response['response'], true );
				$batch_id = $r['batchID'];
				if ( strlen( $batch_id ) == 24 ) {
					// Write batch to check response later.
					$batches_in_progress = get_option( 'omnisend_batches_inProgress' );
					if ( ! is_array( $batches_in_progress ) ) {
						$batches_in_progress = array();
					}
					if ( ! in_array( $batch_id, $batches_in_progress ) ) {
						$batches_in_progress[] = $batch_id;
						update_option( 'omnisend_batches_inProgress', $batches_in_progress );
					}
					Omnisend_Logger::log( 'info', 'batches', $link, 'Batch sync: orders were successfully pushed to Omnisend' );
					Omnisend_Sync_Stats_Repository::count_item( 'orders', $r['totalCount'] );
				} else {
					Omnisend_Logger::log( 'warn', 'batches', $link, 'Batch sync error: unable to push orders to Omnisend' );
					$status = Omnisend_Sync::STATUS_ERROR;
				}
			} else {
				Omnisend_Logger::log( 'warn', 'batches', $link, 'Batch sync error: unable to push orders to Omnisend' );
				$status = Omnisend_Sync::STATUS_ERROR;
			}
		} else {
			$status = Omnisend_Sync::STATUS_SKIPPED;
		}

		foreach ( $orders as $order_id ) {
			// Update order with last update date or mark it as "error" or "skipped".
			$order_status = in_array( $order_id, $skipped_order_ids ) ? Omnisend_Sync::STATUS_SKIPPED : $status;
			Omnisend_Sync::set_order_sync_status( $order_id, $order_status );
		}
	}

	private static function get_orders_to_sync() {
		// HPOS is enabled.
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$query = new WC_Order_Query(
				array(
					'return'      => 'ids',
					'limit'       => 500,
					'field_query' => array(
						array(
							'field' => 'type',
							'value' => 'shop_order',
						),
					),
					'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'relation' => 'OR',
							array(
								'key'     => Omnisend_Sync::FIELD_NAME,
								'compare' => 'NOT EXISTS',
							),
						),
					),
				)
			);

			return $query->get_orders();
		}

		// Traditional CPT-based orders are in use.
		return get_posts(
			array(
				'fields'         => 'ids',
				'posts_per_page' => '500', // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'post_type'      => 'shop_order',
				'post_status'    => array( wc_get_order_statuses() ),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => Omnisend_Sync::FIELD_NAME,
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);
	}

	// Sync products via batches.
	public static function sync_all_products() {
		Omnisend_Logger::hook();
		if ( Omnisend_Sync_Manager::is_products_finished() ) {
			return;
		}

		if ( empty( get_option( 'omnisend_api_key', null ) ) ) {
			Omnisend_Sync_Manager::stop_products( 'no API key' );
			return;
		}

		$products = get_posts(
			array(
				'fields'         => 'ids',
				'posts_per_page' => '1000', // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'post_type'      => 'product',
				'has_password'   => false,
				'post_status'    => 'publish',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => Omnisend_Sync::FIELD_NAME,
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);

		if ( empty( $products ) ) {
			Omnisend_Sync_Manager::finish_products();
			return;
		}

		// Form batch request and save batchID.
		$args             = array();
		$args['method']   = 'POST';
		$args['endpoint'] = 'products';
		$args['items']    = array();
		foreach ( $products as $product_id ) {
			$prepared_product = Omnisend_Product::create( $product_id );
			if ( $prepared_product ) {
				$prepared_product = Omnisend_Helper::clean_model_from_empty_fields( $prepared_product );
				$args['items'][]  = $prepared_product;
			}
		}
		$link     = OMNISEND_API_URL . '/v3/batches';
		$response = Omnisend_Helper::omnisend_api( $link, 'POST', $args );
		if ( $response['code'] >= 200 && $response['code'] < 300 ) {
			$status   = gmdate( DATE_ATOM, time() );
			$r        = json_decode( $response['response'], true );
			$batch_id = $r['batchID'];
			if ( strlen( $batch_id ) == 24 ) {
				// Write batch to check response later.
				$batches_in_progress = get_option( 'omnisend_batches_inProgress' );
				if ( ! is_array( $batches_in_progress ) ) {
					$batches_in_progress = array();
				}
				if ( ! in_array( $batch_id, $batches_in_progress ) ) {
					$batches_in_progress[] = $batch_id;
					update_option( 'omnisend_batches_inProgress', $batches_in_progress );
				}
				Omnisend_Logger::log( 'info', 'batches', $link, 'Batch sync: products were successfully pushed to Omnisend' );
				Omnisend_Sync_Stats_Repository::count_item( 'products', $r['totalCount'] );
			} else {
				Omnisend_Logger::log( 'warn', 'batches', $link, 'Batch sync error: unable to push products to Omnisend' );
				$status = Omnisend_Sync::STATUS_ERROR;
			}
		} else {
			Omnisend_Logger::log( 'warn', 'batches', $link, 'Batch sync error: unable to push products to Omnisend' );
			$status = Omnisend_Sync::STATUS_ERROR;
		}

		foreach ( $products as $product_id ) {
			// Update product with last update date or mark it as "error".
			update_post_meta( $product_id, Omnisend_Sync::FIELD_NAME, $status );
		}
	}

	public static function sync_all_categories() {
		Omnisend_Logger::hook();
		if ( Omnisend_Sync_Manager::is_categories_finished() ) {
			return;
		}

		if ( ! Omnisend_Manager::is_setup() ) {
			Omnisend_Sync_Manager::stop_categories( 'Plugin is not setup' );
			return;
		}

		$categories = self::get_not_synced_categories();
		if ( empty( $categories ) ) {
			Omnisend_Sync_Manager::finish_categories();
			return;
		}

		foreach ( $categories as $category ) {
			Omnisend_Manager::push_category_to_omnisend( $category->term_id );
		}
	}

	private static function get_not_synced_categories() {
		return get_categories(
			array(
				'taxonomy'     => 'product_cat',
				'number'       => 40,
				'hierarchical' => 0,
				'hide_empty'   => 0,
				'meta_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => Omnisend_Sync::FIELD_NAME,
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);
	}

	public static function init_sync() {
		Omnisend_Sync_Manager::start_contacts_if_not_finished();
		Omnisend_Sync_Manager::start_orders_if_not_finished();
		Omnisend_Sync_Manager::start_products_if_not_finished();
		Omnisend_Sync_Manager::start_categories_if_not_finished();
	}
}
