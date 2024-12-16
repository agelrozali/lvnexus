<?php
/**
 * Omnisend Helper Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Helper {

	const ADMINISTRATOR_USER_ROLE         = 'administrator';
	public static $number_of_curl_repeats = 1;

	public static $valid_countries = array( 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA', 'ZM', 'ZW' );

	public static function valid_country_code( $country_code ) {
		if ( in_array( $country_code, self::$valid_countries ) ) {
			return true;
		}
		return false;
	}

	public static function clean_model_from_empty_fields( $model_object ) {
		$clean_model_object = array();

		foreach ( $model_object as $key => $value ) {
			if ( isset( $value ) ) {
				if ( is_array( $value ) ) {
					$clean_model_object[ $key ] = self::clean_model_from_empty_fields( $value );
				} else {
					$clean_model_object[ $key ] = $value;
				}
			}
		}

		return $clean_model_object;
	}

	// Make request to Omnisend API.
	public static function omnisend_api( $link, $method = 'POST', $postfields = array() ) {
		++self::$number_of_curl_repeats;
		$api_key = get_option( 'omnisend_api_key', null );
		if ( is_array( $postfields ) && isset( $postfields['apiKey'] ) ) {
			$api_key = $postfields['apiKey'];
		}
		$result      = array();
		$data_string = array();

		if ( ! empty( $postfields ) ) {
			$data_string = wp_json_encode( $postfields, JSON_UNESCAPED_SLASHES );
		}

		$timeout = ini_get( 'max_execution_time' );
		if ( $timeout > 10 && $timeout <= 30 ) {
			$timeout = $timeout - 2;
		} else {
			$timeout = 30;
		}

		$headers = array(
			'Content-Type'               => 'application/json',
			OMNISEND_API_KEY_HEADER_NAME => $api_key,
			OMNISEND_ORIGIN_HEADER_NAME  => OMNISEND_ORIGIN_HEADER_VALUE,
		);

		switch ( $method ) {
			case 'GET':
				$api_response = wp_remote_get(
					$link,
					array(
						'timeout'     => $timeout,
						'redirection' => 3,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => $headers,
						'cookies'     => array(),
					)
				);
				break;
			default:
				$api_response = wp_remote_post(
					$link,
					array(
						'method'      => $method,
						'timeout'     => $timeout,
						'redirection' => 3,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => $headers,
						'body'        => $data_string,
						'cookies'     => array(),
					)
				);
		}

		if ( is_wp_error( $api_response ) ) {
			$result['code']     = '500';
			$result['response'] = $api_response->get_error_message();
		} else {
			$result['code']     = $api_response['response']['code'];
			$result['response'] = $api_response['body'];

			if ( self::$number_of_curl_repeats == 1 && ( intval( $result['code'] ) == 408 || intval( $result['code'] ) == 429 || intval( $result['code'] ) == 503 ) ) {
				$result = self::omnisend_api( $link, $method, $postfields );
			} else {
				self::$number_of_curl_repeats = 0;
			}
		}

		return $result;
	}

	public static function check_wp_wc_compatibility() {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		$wc_plugin_main_file = WC()->plugin_path() . '/woocommerce.php';
		if ( ! file_exists( $wc_plugin_main_file ) ) {
			return false;
		}

		$file_data                 = get_file_data( $wc_plugin_main_file, array( 'req_wp' => 'Requires at least' ) );
		$required_wp_version_by_wc = $file_data['req_wp'] ?? '0.0';
		if ( floatval( get_bloginfo( 'version' ) ) < floatval( $required_wp_version_by_wc ) ) {
			return false;
		} else {
			return true;
		}
	}

	public static function price_to_cents( $price ) {
		return intval( number_format( (float) $price * 100, 0, '.', '' ) );
	}

	public static function omnisend_plugin_version(): string {
		$file_data = get_file_data( OMNISEND_WOO_PLUGIN_FILE, array( 'ver' => 'Version' ) );
		return $file_data['ver'] ?? '0.0.0';
	}

	public static function get_account_info() {
		$omnisend_plugin_version = self::omnisend_plugin_version();
		preg_match( '#^\d+(\.\d+)*#', defined( 'PHP_VERSION' ) ? PHP_VERSION : phpversion(), $phpver );
		$web_server = null;
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$web_server = explode( ' ', sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) )[0];
		}

		$timeout = ini_get( 'max_execution_time' );
		if ( $timeout > 10 && $timeout <= 30 ) {
			$timeout = $timeout - 2;
		} else {
			$timeout = 30;
		}

		$data = array(
			'website'         => home_url(),
			'version'         => $omnisend_plugin_version,
			'timeout'         => $timeout,
			'createdAt'       => gmdate( DATE_ATOM, time() ),
			'currency'        => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
			'webserver'       => $web_server,
			'phpVersion'      => $phpver[0],
			'platformVersion' => get_bloginfo( 'version' ),
		);
		return $data;
	}

	public static function is_woocommerce_plugin_activated() {
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
	public static function is_omnisend_connected() {
		return (bool) get_option( 'omnisend_api_key', null );
	}

	public static function are_permalinks_correct() {
		$permalink_structure = get_option( 'permalink_structure' );

		return ! empty( $permalink_structure );
	}

	public static function is_woocommerce_api_access_granted() {
		$api_access_granted = false;
		$api_keys           = Omnisend_Install::get_woo_api_keys();

		foreach ( $api_keys as $api_key ) {
			$user = get_userdata( $api_key->user_id );
			if ( in_array( self::ADMINISTRATOR_USER_ROLE, $user->roles ) ) {
				$api_access_granted = true;
			} else {
				Omnisend_Install::remove_woo_api_key( $api_key->key_id );
			}
		}

		return $api_access_granted;
	}

	public static function get_wc_auth_url( $omnisend_account_id ) {
		$endpoint     = '/wc-auth/v1/authorize';
		$store_url    = home_url();
		$callback_url = OMNISEND_CALLBACK_URL . '/' . $omnisend_account_id . '?store_url=' . $store_url;
		$params       = array(
			'app_name'     => OMNISEND_WC_API_APP_NAME,
			'scope'        => 'read_write',
			'user_id'      => get_current_user_id(),
			'return_url'   => admin_url(
				'admin.php?' . http_build_query(
					array(
						'page'     => OMNISEND_SETTINGS_PAGE,
						'_wpnonce' => wp_create_nonce( 'omnisend-oauth' ),
					)
				)
			),
			'callback_url' => $callback_url,
		);

		return home_url( $endpoint . '?' . http_build_query( $params ) );
	}

	public static function get_client_ip() {
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			return getenv( 'HTTP_CLIENT_IP' );
		}

		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			return getenv( 'HTTP_X_FORWARDED_FOR' );
		}

		if ( getenv( 'HTTP_X_FORWARDED' ) ) {
			return getenv( 'HTTP_X_FORWARDED' );
		}

		if ( getenv( 'HTTP_X_CLUSTER_CLIENT_IP' ) ) {
			return getenv( 'HTTP_X_CLUSTER_CLIENT_IP' );
		}

		if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			return getenv( 'HTTP_FORWARDED_FOR' );
		}

		if ( getenv( 'HTTP_FORWARDED' ) ) {
			return getenv( 'HTTP_FORWARDED' );
		}

		return getenv( 'REMOTE_ADDR' );
	}

	public static function get_domain( $url ) {
		return wp_parse_url( $url, PHP_URL_HOST );
	}
}
