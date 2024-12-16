<?php
/**
 * Omnisend Contact Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Contact {

	public $email;
	public $phone;
	public $status;
	public $country;
	public $state;
	public $city;
	public $address;
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	public $firstName;
	public $lastName;
	public $countryCode;
	public $postalCode;
	public $statusDate;
	// phpcs:enable
	public $state_code;
	public $tags;

	public static function create( $user ) {
		try {
			return new Omnisend_Contact( $user );
		} catch ( Omnisend_Empty_Required_Fields_Exception $exception ) {
			return null;
		}
	}

	/**
	 * @throws Omnisend_Empty_Required_Fields_Exception
	 */
	private function __construct( $user ) {
		if ( empty( $user ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}

		$email = $user->user_email;
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$this->email = $email;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( get_user_meta( $user->ID, 'first_name', true ) !== '' ) {
			$this->firstName = get_user_meta( $user->ID, 'first_name', true );
		} elseif ( get_user_meta( $user->ID, 'shipping_first_name', true ) !== '' ) {
			$this->firstName = get_user_meta( $user->ID, 'shipping_first_name', true );
		} elseif ( get_user_meta( $user->ID, 'billing_first_name', true ) !== '' ) {
			$this->firstName = get_user_meta( $user->ID, 'billing_first_name', true );
		}

		if ( get_user_meta( $user->ID, 'last_name', true ) != '' ) {
			$this->lastName = get_user_meta( $user->ID, 'last_name', true );
		} elseif ( get_user_meta( $user->ID, 'shipping_last_name', true ) != '' ) {
			$this->lastName = get_user_meta( $user->ID, 'shipping_last_name', true );
		} elseif ( get_user_meta( $user->ID, 'billing_last_name', true ) != '' ) {
			$this->lastName = get_user_meta( $user->ID, 'billing_last_name', true );
		}

		$billing_country  = get_user_meta( $user->ID, 'billing_country', true );
		$shipping_country = get_user_meta( $user->ID, 'shipping_country', true );

		if ( $billing_country !== '' && Omnisend_Helper::valid_country_code( $billing_country ) ) {
			$this->countryCode = $billing_country;
		} elseif ( $shipping_country !== '' && Omnisend_Helper::valid_country_code( $shipping_country ) ) {
			$this->countryCode = $shipping_country;
		}

		if ( ! empty( $this->countryCode ) ) {
			$this->country = WC()->countries->countries[ $this->countryCode ];
		}

		$billing_state  = get_user_meta( $user->ID, 'billing_state', true );
		$shipping_state = get_user_meta( $user->ID, 'shipping_state', true );

		if ( $billing_state != '' ) {
			$this->state = $billing_state;
		} elseif ( $shipping_state != '' ) {
			$this->state = $shipping_state;
		}

		if ( ! empty( $this->state ) && ! empty( $this->countryCode ) ) {
			$states = WC()->countries->get_states( $this->countryCode );
			if ( ! empty( $states[ $this->state ] ) ) {
				$this->state_code = $this->state;
				$this->state      = $states[ $this->state ];
			}
		}

		$billing_city  = get_user_meta( $user->ID, 'billing_city', true );
		$shipping_city = get_user_meta( $user->ID, 'shipping_city', true );

		if ( $billing_city != '' ) {
			$this->city = $billing_city;
		} elseif ( $shipping_city != '' ) {
			$this->city = $shipping_city;
		}

		$address = '';

		$billing_address1  = get_user_meta( $user->ID, 'billing_address_1', true );
		$shipping_address1 = get_user_meta( $user->ID, 'shipping_address_1', true );

		if ( $billing_address1 != '' ) {
			$address .= $billing_address1;
		} elseif ( $shipping_address1 != '' ) {
			$address .= $shipping_address1;
		}

		$billing_address2  = get_user_meta( $user->ID, 'billing_address_2', true );
		$shipping_address2 = get_user_meta( $user->ID, 'shipping_address_2', true );

		if ( $billing_address2 != '' ) {
			$address .= $billing_address2;
		} elseif ( $shipping_address2 != '' ) {
			$address .= $shipping_address2;
		}
		$this->address = $address;

		$billing_postal_code  = get_user_meta( $user->ID, 'billing_postcode', true );
		$shipping_postal_code = get_user_meta( $user->ID, 'shipping_postcode', true );

		if ( $billing_postal_code != '' ) {
			$this->postalCode = $billing_postal_code;
		} elseif ( $shipping_postal_code != '' ) {
			$this->postalCode = $shipping_postal_code;
		}

		$phone_number = get_user_meta( $user->ID, 'billing_phone', true );
		if ( $phone_number ) {
			$this->phone = $phone_number;
		}

		$this->tags = array( 'source: woocommerce' );
		$tag        = Omnisend_Settings::get_contact_tag_value();
		if ( $tag ) {
			$this->tags[] = $tag;
		}

		$this->status = 'nonSubscribed';

		if ( $user->user_registered ) {
			$this->statusDate = gmdate( DATE_ATOM, strtotime( $user->user_registered ) );
		} else {
			$this->statusDate = gmdate( DATE_ATOM, time() );
		}
		if ( empty( $this->email ) || empty( $this->statusDate ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}
		// phpcs:enable
	}
}
