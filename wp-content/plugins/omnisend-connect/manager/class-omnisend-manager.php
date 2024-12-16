<?php
/**
 * Omnisend Manager Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

$omnisend_product = '';

class Omnisend_Manager {

	const HTTP_STATUS_CODES_TO_RETRY_POST_IN_PUT = array( 400, 404, 422 );
	const VERB_POST                              = 'POST';
	const VERB_PUT                               = 'PUT';

	/**
	 * @return bool
	 */
	public static function is_setup() {
		if ( empty( get_option( 'omnisend_api_key', null ) ) ) {
			return false;
		}

		if ( ! Omnisend_Helper::is_woocommerce_plugin_activated() ) {
			return false;
		}

		return true;
	}

	public static function push_contact_to_omnisend( $user_id ) {
		if ( ! self::is_setup() ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( empty( $user ) ) {
			Omnisend_Logger::log( 'warn', 'contacts', '', 'User not found' );
			return;
		}

		$contact_object = Omnisend_Contact::create( $user );
		if ( ! $contact_object ) {
			Omnisend_Logger::log( 'warn', 'contacts', '', 'Contact was not created (missing required fields' );
			return;
		}

		$contact_array = Omnisend_Helper::clean_model_from_empty_fields( $contact_object );
		if ( Omnisend_Server_Session::get( 'omnisend_contact' ) == $contact_array ) {
			return;
		}

		$api_url     = OMNISEND_API_URL . '/v3/contacts';
		$curl_result = Omnisend_Helper::omnisend_api( $api_url, self::VERB_POST, $contact_array );
		if ( $curl_result['code'] >= 200 && $curl_result['code'] < 300 ) {
			$response = json_decode( $curl_result['response'], true );
			if ( ! empty( $response['contactID'] ) ) {
				Omnisend_Contact_Resolver::update_by_email_and_contact_id( $contact_object->email, $response['contactID'] );
			}
			Omnisend_Logger::log( 'info', 'contacts', $api_url, 'Contact ' . $contact_object->email . ' was successfully pushed to Omnisend.' );
			Omnisend_Sync::mark_contact_as_synced( $user->ID );
			Omnisend_Server_Session::set( 'omnisend_contact', $contact_array );
			return;
		}

		if ( $curl_result['code'] == 403 ) {
			Omnisend_Logger::log( 'warn', 'contacts', $api_url, 'Unable to push contact ' . $contact_object->email . " to Omnisend. You don't have rights to push contacts." );
			Omnisend_Sync::mark_contact_as_error( $user->ID );
			return;
		}

		if ( $curl_result['code'] == 400 || $curl_result['code'] == 422 ) {
			Omnisend_Logger::log( 'warn', 'contacts', $api_url, 'Unable to push contact ' . $contact_object->email . ' to Omnisend.' . $curl_result['response'] );
			Omnisend_Sync::mark_contact_as_error( $user->ID );
			return;
		}

		Omnisend_Logger::log( 'warn', 'contacts', $api_url, 'Unable to push contact ' . $contact_object->email . ' to Omnisend. May be server error. ' . $curl_result['response'] );
		Omnisend_Sync::mark_contact_as_error( $user->ID );
	}

	public static function push_category_to_omnisend( $term_id ) {
		if ( ! self::is_setup() ) {
			return;
		}

		$category = Omnisend_Category::create_from_id( $term_id );
		if ( ! $category ) {
			Omnisend_Logger::log( 'warn', 'categories', '', "Unable to push category #$term_id to Omnisend. One or more required fields are empty or invalid" );
			return;
		}

		$verbs_to_try = Omnisend_Sync::was_category_synced_before( $category->id ) ? array( self::VERB_PUT ) : array( self::VERB_POST, self::VERB_PUT );
		foreach ( $verbs_to_try as $verb ) {
			$api_url     = $verb == self::VERB_POST ? OMNISEND_API_URL . '/v3/categories' : OMNISEND_API_URL . '/v3/categories/' . $category->id;
			$curl_result = Omnisend_Helper::omnisend_api( $api_url, $verb, $category->to_array() );

			if ( $curl_result['code'] >= 200 && $curl_result['code'] < 300 ) {
				Omnisend_Logger::log( 'info', 'categories', $api_url, "Category #$category->id was successfully pushed to Omnisend." );
				Omnisend_Sync_Stats_Repository::count_item( 'categories' );
				Omnisend_Sync::mark_category_sync_as_synced( $category->id );
				return;
			}

			if ( in_array( $curl_result['code'], self::HTTP_STATUS_CODES_TO_RETRY_POST_IN_PUT ) ) {
				Omnisend_Logger::log( 'warn', 'categories', $api_url, "Unable to push category #$category->id to Omnisend. {$curl_result['response']}" );
				continue;
			}

			if ( $curl_result['code'] == 403 ) {
				Omnisend_Logger::log( 'warn', 'categories', $api_url, "Unable to push category #$category->id to Omnisend. You don't have rights to push categories." );
				break;
			}

			Omnisend_Logger::log( 'warn', 'categories', $api_url, "Unable to push category #$category->id to Omnisend. May be server error. {$curl_result['response']}" );
			break;
		}

		Omnisend_Sync::mark_category_sync_as_synced( $category->id );
	}

	public static function delete_category_from_omnisend( $id ) {
		if ( ! empty( get_option( 'omnisend_api_key', null ) ) ) {
			$link        = OMNISEND_API_URL . '/v3/categories/' . $id;
			$curl_result = Omnisend_Helper::omnisend_api( $link, 'DELETE', array() );
			return $curl_result['response'];
		}
	}

	public static function push_product_to_omnisend( $product_id = '', $put = 0, $iter = 0 ) {
		global $omnisend_product;
		if ( ! empty( get_option( 'omnisend_api_key', null ) ) ) {
			$prepared_product = Omnisend_Product::create( $product_id );
			// If all required fields are set, push product to Omnisend.
			if ( $prepared_product ) {
				if ( ! $prepared_product->published ) {
					Omnisend_Logger::info( "Skip product #{$prepared_product->productID} sync, because it is not 'published'" );
					return;
				}

				$prepared_product = Omnisend_Helper::clean_model_from_empty_fields( $prepared_product );
				$last_sync        = get_post_meta( $product_id, Omnisend_Sync::FIELD_NAME, true );
				if ( $omnisend_product != $prepared_product ) {
					if ( $put == 1 || ( ! empty( $last_sync ) && $last_sync != Omnisend_Sync::STATUS_ERROR && $put == 0 ) ) {
						$put = 1;
						// If product already exists - try to update.
						$link        = OMNISEND_API_URL . '/v3/products/' . $product_id;
						$curl_result = Omnisend_Helper::omnisend_api( $link, 'PUT', $prepared_product );
					} else {
						$put         = 0;
						$link        = OMNISEND_API_URL . '/v3/products';
						$curl_result = Omnisend_Helper::omnisend_api( $link, 'POST', $prepared_product );
					}

					if ( $curl_result['code'] >= 200 && $curl_result['code'] < 300 ) {
						Omnisend_Logger::log( 'info', 'products', $link, 'Product #' . $product_id . ' was successfully pushed to Omnisend.' );
						update_post_meta( $product_id, Omnisend_Sync::FIELD_NAME, gmdate( DATE_ATOM, time() ) );
						Omnisend_Sync_Stats_Repository::count_item( 'products' );
						$omnisend_product = $prepared_product;
					} elseif ( $curl_result['code'] == 403 ) {
						Omnisend_Logger::log( 'warn', 'products', $link, 'Unable to push product #' . $product_id . " to Omnisend. You don't have rights to push products." );
					} elseif ( $curl_result['code'] == 400 || $curl_result['code'] == 404 || $curl_result['code'] == 422 ) {
						if ( $iter == 0 ) {
							// Try another way.
							self::push_product_to_omnisend( $product_id, $put + 1, $iter + 1 );
						} else {
							Omnisend_Logger::log( 'warn', 'products', $link, 'Unable to push product #' . $product_id . ' to Omnisend.' . $curl_result['response'] );
							if ( empty( $last_sync ) ) {
								update_post_meta( $product_id, Omnisend_Sync::FIELD_NAME, Omnisend_Sync::STATUS_ERROR );
							}
						}
					} else {
						Omnisend_Logger::log( 'warn', 'products', $link, 'Unable to push product #' . $product_id . ' to Omnisend. May be server error. ' . $curl_result['response'] );
					}
				}
			} else {
				$message = 'Unable to push product #' . $product_id . ' to Omnisend. One or more required fields are empty or invalid';
				Omnisend_Logger::log( 'warn', 'products', '', $message );
			}
		}
	}

	public static function push_order_to_omnisend( $order_id ) {
		if ( ! self::is_setup() ) {
			return;
		}

		$order_object = Omnisend_Order::create( $order_id );
		if ( ! $order_object ) {
			$message = 'Unable to push Order #' . $order_id . ' to Omnisend. One or more required fields are empty or invalid';
			Omnisend_Logger::log( 'warn', 'orders', '', $message );
			Omnisend_Sync::mark_order_sync_as_skipped( $order_id );
			return;
		}

		$order_array  = Omnisend_Helper::clean_model_from_empty_fields( $order_object );
		$verbs_to_try = Omnisend_Sync::was_order_synced_before( $order_id ) ? array( self::VERB_PUT ) : array( self::VERB_POST, self::VERB_PUT );

		foreach ( $verbs_to_try as $verb ) {
			$api_url     = $verb == self::VERB_POST ? OMNISEND_API_URL . '/v3/orders' : OMNISEND_API_URL . '/v3/orders/' . $order_id;
			$curl_result = Omnisend_Helper::omnisend_api( $api_url, $verb, $order_array );

			if ( $curl_result['code'] >= 200 && $curl_result['code'] < 300 ) {
				Omnisend_Logger::log( 'info', 'orders', $api_url, "Order #$order_id was successfully pushed to Omnisend." );
				Omnisend_Sync_Stats_Repository::count_item( 'orders' );
				Omnisend_Sync::mark_order_sync_as_synced( $order_id );
				Omnisend_Contact_Resolver::update_by_email( $order_object->email );
				return;
			}

			if ( in_array( $curl_result['code'], self::HTTP_STATUS_CODES_TO_RETRY_POST_IN_PUT ) ) {
				Omnisend_Logger::log( 'warn', 'orders', $api_url, 'Unable to push order #' . $order_id . ' to Omnisend.' . $curl_result['response'] );
				continue;
			}

			if ( $curl_result['code'] == 403 ) {
				Omnisend_Logger::log( 'warn', 'orders', $api_url, "Unable to push order #$order_id to Omnisend. You don't have rights to push orders." );
				break;
			}

			Omnisend_Logger::log( 'warn', 'orders', $api_url, "Unable to push order #$order_id to Omnisend. May be server error. " . $curl_result['response'] );
			break;
		}

		Omnisend_Sync::mark_order_sync_as_error( $order_id );
	}

	public static function update_order_status( $order_id, $status_type, $order_status ) {
		if ( ! self::is_setup() ) {
			return;
		}

		/* Order is not synced - try to push */
		if ( ! Omnisend_Sync::was_order_synced_before( $order_id ) ) {
			self::push_order_to_omnisend( $order_id );
			return;
		}

		/* Order already synced - try to update */
		$post_data = array();

		if ( $status_type == 'fulfillment' ) {
			$post_data['fulfillmentStatus'] = $order_status;
		} else {
			$post_data['paymentStatus'] = $order_status;
		}

		if ( $order_status == 'voided' ) {
			$post_data['canceledDate'] = gmdate( DATE_ATOM, time() );
		}

		$link        = OMNISEND_API_URL . '/v3/orders/' . $order_id;
		$curl_result = Omnisend_Helper::omnisend_api( $link, 'PATCH', $post_data );

		if ( $curl_result['code'] >= 200 && $curl_result['code'] < 300 ) {
			Omnisend_Logger::log( 'info', 'orders', $link, "Order #$order_id status change ($order_status) was successfully pushed to Omnisend" );
			Omnisend_Sync_Stats_Repository::count_item( 'orders' );
			Omnisend_Sync::mark_order_sync_as_synced( $order_id );
		} elseif ( $curl_result['code'] == 403 ) {
			Omnisend_Logger::log( 'warn', 'orders', $link, 'Unable to push order #' . $order_id . " status change ($order_status) to Omnisend. You don't have rights to push orders." );
		} elseif ( $curl_result['code'] == 400 || $curl_result['code'] == 404 || $curl_result['code'] == 422 ) {
			Omnisend_Logger::log( 'warn', 'orders', $link, 'Unable to push order #' . $order_id . " status change ($order_status) to Omnisend. " . $curl_result['response'] );
			Omnisend_Sync::mark_order_sync_as_error( $order_id );
		} else {
			Omnisend_Logger::log( 'warn', 'orders', $link, 'Unable to push order #' . $order_id . " status change ($order_status) to Omnisend. May be server error. " . $curl_result['response'] );
		}
	}

	public static function delete_product_from_omnisend( $id ) {
		if ( ! empty( get_option( 'omnisend_api_key', null ) ) ) {
			$link        = OMNISEND_API_URL . '/v3/products/' . $id;
			$curl_result = Omnisend_Helper::omnisend_api( $link, 'DELETE', array() );

			return $curl_result['response'];
		}
	}

	public static function update_account_info( $data = '' ) {
		if ( ! empty( get_option( 'omnisend_api_key', null ) ) ) {
			if ( $data == '' ) {
				$data = Omnisend_Helper::get_account_info();
			}
			$link        = OMNISEND_API_URL . '/v3/accounts/' . get_option( 'omnisend_account_id', null );
			$curl_result = Omnisend_Helper::omnisend_api( $link, 'POST', $data );
			if ( $curl_result['code'] >= 200 && $curl_result['code'] < 300 ) {
				Omnisend_Logger::log( 'info', 'account', $link, 'Account information has been updated.' );
			} elseif ( $curl_result['code'] == 403 ) {
				Omnisend_Logger::log( 'warn', 'account', $link, 'Unable to update account information' );
			} elseif ( $curl_result['code'] == 400 || $curl_result['code'] == 404 || $curl_result['code'] == 422 ) {
				Omnisend_Logger::log( 'warn', 'account', $link, 'Unable to update account information. ' . $curl_result['response'] );
			} else {
				Omnisend_Logger::log( 'warn', 'account', $link, 'Unable to update account information. May be server error. ' . $curl_result['response'] );
			}
		}
	}

	public static function get_brand_info() {
		if ( empty( get_option( 'omnisend_api_key', null ) ) ) {
			return array();
		}

		$link        = OMNISEND_API_URL . '/v5/brands/current';
		$curl_result = Omnisend_Helper::omnisend_api( $link, 'GET' );

		if ( $curl_result['code'] >= 300 ) {
			Omnisend_Logger::error( "Unable to get account information. Error: {$curl_result['code']} {$curl_result['body']}" );
			return array();
		}

		$response = json_decode( $curl_result['response'], true );
		if ( ! empty( $response['brandID'] ) ) {
			$account_info = array(
				'brandID' => $response['brandID'],
				'name'    => $response['name'],
			);

			return $account_info;
		}
	}
}
