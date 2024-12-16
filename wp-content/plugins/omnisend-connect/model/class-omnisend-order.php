<?php
/**
 * Omnisend Order Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Order {
	public $email;
	public $currency;
	public $source;
	public $products = array();
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	public $orderID;
	public $cartID;
	public $attributionID;
	public $shippingMethod;
	public $orderUrl;
	public $orderSum;
	public $subTotalSum;
	public $subTotalTaxIncluded;
	public $discountSum;
	public $taxSum;
	public $shippingSum;
	public $createdAt;
	public $updatedAt;
	public $canceledDate;
	public $contactNote;
	public $paymentMethod;
	public $paymentStatus;
	public $fulfillmentStatus;
	public $billingAddress  = array();
	public $shippingAddress = array();
	// phpcs:enable

	/**
	 * @param $order_id
	 *
	 * @return Omnisend_Order|null
	 */
	public static function create( $order_id ) {
		try {
			return new Omnisend_Order( $order_id );
		} catch ( Omnisend_Empty_Required_Fields_Exception $exception ) {
			return null;
		}
	}

	/**
	 * @throws Omnisend_Empty_Required_Fields_Exception
	 */
	private function __construct( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->orderID = '' . $order->get_id();
		if ( empty( $this->orderID ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}
		// phpcs:enable

		$email = $order->get_billing_email();
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$this->email = $email;
		}

		if ( empty( $this->email ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}

		$this->currency = $order->get_currency();
		if ( empty( $this->currency ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->orderSum = Omnisend_Helper::price_to_cents( $order->get_total() );
		if ( ! isset( $this->orderSum ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}

		$this->createdAt = empty( $order->get_date_created() ) ? gmdate( DATE_ATOM, time() ) : $order->get_date_created()->format( DATE_ATOM );
		if ( empty( $this->createdAt ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}
		// phpcs:enable

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->cartID = '' . $order->get_meta( 'omnisend_cartID', true );

		if ( Omnisend_User_Storage::get_attribution_id() ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->attributionID = Omnisend_User_Storage::get_attribution_id();
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->shippingMethod      = $order->get_shipping_method();
		$this->subTotalSum         = Omnisend_Helper::price_to_cents( $order->get_subtotal() );
		$this->subTotalTaxIncluded = true;
		$this->discountSum         = $order->get_total_discount() ? Omnisend_Helper::price_to_cents( $order->get_total_discount() ) : null;
		$this->taxSum              = $order->get_total_tax() ? Omnisend_Helper::price_to_cents( $order->get_total_tax() ) : null;
		$this->shippingSum         = $order->get_total_shipping() ? Omnisend_Helper::price_to_cents( $order->get_total_shipping() ) : null;
		$this->updatedAt           = empty( $order->get_date_modified() ) ? gmdate( DATE_ATOM, time() ) : $order->get_date_modified()->format( DATE_ATOM );
		// phpcs:enable

		if ( $order->get_user() ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->orderUrl = $order->get_view_order_url() ? esc_url( $order->get_view_order_url() ) : null;
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->paymentMethod = $order->get_payment_method_title();

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( ! empty( $order->get_date_paid() ) ) {
			$this->paymentStatus = 'paid';
		} else {
			$this->paymentStatus = 'awaitingPayment';
		}

		switch ( $order->get_status() ) {
			case 'processing':
				$this->fulfillmentStatus = 'inProgress';
				break;
			case 'pending':
				$this->fulfillmentStatus = 'unfulfilled';
				break;
			case 'completed':
				$this->fulfillmentStatus = 'fulfilled';
				break;
			case 'refunded':
				$this->paymentStatus = 'refunded';
				break;
			case 'cancelled':
				$this->paymentStatus = 'voided';
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$this->canceledDate = empty( $order->get_date_modified() ) ? gmdate( DATE_ATOM, time() ) : $order->get_date_modified()->format( DATE_ATOM );
				break;
		}
		// phpcs:enable

		$order_data = $order->get_data();

		$this->source = $order_data['created_via'];
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->contactNote = $order->get_customer_note();

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->billingAddress['firstName'] = $order_data['billing']['first_name'];
		$this->billingAddress['lastName']  = $order_data['billing']['last_name'];
		$this->billingAddress['company']   = $order_data['billing']['company'];
		if ( $order_data['billing']['country'] && Omnisend_Helper::valid_country_code( $order_data['billing']['country'] ) ) {
			$this->billingAddress['countryCode'] = $order_data['billing']['country'];
			$this->billingAddress['country']     = WC()->countries->countries[ $this->billingAddress['countryCode'] ];
		}
		if ( $order_data['billing']['state'] ) {
			$this->billingAddress['state']     = $order_data['billing']['state'];
			$this->billingAddress['stateCode'] = $this->billingAddress['state'];
			$states                            = WC()->countries->get_states( $this->billingAddress['countryCode'] );

			if ( $states ) {
				$this->billingAddress['state'] = $states[ $order_data['billing']['state'] ];
			}
		}

		$this->billingAddress['city']       = $order_data['billing']['city'];
		$this->billingAddress['address']    = $order_data['billing']['address_1'];
		$this->billingAddress['address2']   = $order_data['billing']['address_2'];
		$this->billingAddress['postalCode'] = $order_data['billing']['postcode'];
		// phpcs:enabled

		$phone_number = $order_data['billing']['phone'];
		if ( empty( $phone_number ) ) {
			$phone_number = $order->get_billing_phone();
		}

		if ( $phone_number && filter_var( $phone_number, FILTER_SANITIZE_NUMBER_INT ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->billingAddress['phone'] = $phone_number;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( ! empty( $order_data['shipping']['first_name'] ) ) {
			$this->shippingAddress['firstName'] = $order_data['shipping']['first_name'];
		} else {
			$this->shippingAddress['firstName'] = '';
		}

		if ( ! empty( $order_data['shipping']['last_name'] ) ) {
			$this->shippingAddress['lastName'] = $order_data['shipping']['last_name'];
		} else {
			$this->shippingAddress['lastName'] = '';
		}

		if ( ! empty( $order_data['shipping']['company'] ) ) {
			$this->shippingAddress['company'] = $order_data['shipping']['company'];
		} else {
			$this->shippingAddress['company'] = '';
		}

		if ( ! empty( $order_data['shipping']['country'] ) && Omnisend_Helper::valid_country_code( $order_data['shipping']['country'] ) ) {
			$this->shippingAddress['country']     = WC()->countries->countries[ $order_data['shipping']['country'] ];
			$this->shippingAddress['countryCode'] = $order_data['shipping']['country'];
		} else {
			$this->shippingAddress['country']     = '';
			$this->shippingAddress['countryCode'] = '';
		}

		if ( ! empty( $order_data['shipping']['state'] ) ) {
			$states = WC()->countries->get_states( $order_data['shipping']['country'] );
			if ( ! empty( $states[ $order_data['shipping']['state'] ] ) ) {
				$this->shippingAddress['stateCode'] = $order_data['shipping']['state'];
				$this->shippingAddress['state']     = $states[ $order_data['shipping']['state'] ];
			}
		} else {
			$this->shippingAddress['state']     = '';
			$this->shippingAddress['stateCode'] = '';
		}

		if ( ! empty( $order_data['shipping']['city'] ) ) {
			$this->shippingAddress['city'] = $order_data['shipping']['city'];
		} else {
			$this->shippingAddress['city'] = '';
		}

		if ( ! empty( $order_data['shipping']['address_1'] ) ) {
			$this->shippingAddress['address'] = $order_data['shipping']['address_1'];
		} else {
			$this->shippingAddress['address'] = '';
		}

		if ( ! empty( $order_data['shipping']['address_2'] ) ) {
			$this->shippingAddress['address2'] = $order_data['shipping']['address_2'];
		} else {
			$this->shippingAddress['address2'] = '';
		}

		if ( ! empty( $order_data['shipping']['postcode'] ) ) {
			$this->shippingAddress['postalCode'] = $order_data['shipping']['postcode'];
		} else {
			$this->shippingAddress['postalCode'] = '';
		}
		// phpcs:enabled

		foreach ( $order->get_items() as $item_id => $wc_product_data ) {
			$product         = array();
			$product['tags'] = array();
			$product_valid   = true;
			/*Required field*/
			$product['productID'] = '' . $wc_product_data['product_id'];
			if ( empty( $product['productID'] ) ) {
				$product['productID'] = wc_get_order_item_meta( $item_id, '_product_id', true );
				if ( empty( $product['productID'] ) ) {
					$product_valid = false;
				}
			}
			$product['variantID'] = ( ! empty( $wc_product_data->get_variation_id() ) ) ? '' . $wc_product_data->get_variation_id() : '' . $product['productID'];
			if ( empty( $product['variantID'] ) ) {
				$product_valid = false;
			}

			$wc_product = $wc_product_data->get_product();
			if ( is_object( $wc_product ) ) {
				$product['sku']             = $wc_product->get_sku();
				$product['weight']          = $wc_product->get_weight() ? intval( $wc_product->get_weight() ) : null;
				$url_tmp                    = wp_parse_url( wp_get_attachment_url( $wc_product->get_image_id() ) );
				$can_form_product_image_url = ! empty( $url_tmp['scheme'] ) && ! empty( $url_tmp['host'] ) && ! empty( $url_tmp['path'] );
				if ( $can_form_product_image_url ) {
					$product['imageUrl'] = $url_tmp['scheme'] . '://' . $url_tmp['host'] . $url_tmp['path'];
				}
			}

			$product['variantTitle'] = $wc_product_data->get_name();

			/*Required field*/
			$product['title'] = $wc_product_data->get_name();
			if ( empty( $product['title'] ) ) {
				$product_valid = false;
			}

			/*Required field*/
			$product['quantity'] = intval( $wc_product_data->get_quantity() );
			if ( ! isset( $product['quantity'] ) ) {
				$product_valid = false;
			}

			/*Required field*/
			$product['price'] = Omnisend_Helper::price_to_cents( $order->get_item_total( $wc_product_data, true, false ) );
			if ( ! isset( $product['price'] ) ) {
				$product_valid = false;
			}
			$product['categoryIDs'] = array_map( 'strval', wp_get_post_terms( $product['productID'], 'product_cat', array( 'fields' => 'ids' ) ) );

			$product_tags = get_the_terms( $wc_product_data['product_id'], 'product_tag' );
			if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
				foreach ( $product_tags as $term ) {
					array_push( $product['tags'], $term->name );
				}
			}

			$product['productUrl'] = get_permalink( $wc_product_data['product_id'] );
			if ( $product_valid ) {
				array_push( $this->products, $product );
			}
		}
	}
}
