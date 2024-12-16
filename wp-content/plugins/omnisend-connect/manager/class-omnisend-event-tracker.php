<?php
/**
 * Omnisend Event Tracker Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Event_Tracker {

	public static function track_event( $event_name = '', $event_time = '', $email = '', $props = array() ) {
		if ( ! $event_name ) {
			return;
		}

		$brand_id = get_option( 'omnisend_account_id', null );
		if ( ! $brand_id ) {
			return;
		}

		$contact_id = Omnisend_User_Storage::get_contact_id();

		if ( ! $email && ! $contact_id ) {
			return;
		}

		$tags = array();
		$tag  = Omnisend_Settings::get_contact_tag_value();

		if ( $tag ) {
			$tags[] = $tag;
		}

		$contact = array(
			'email' => $email,
			'id'    => $contact_id,
			'tags'  => $tags,
		);

		$body = array(
			'ipAddress'  => Omnisend_Helper::get_client_ip(),
			'brandID'    => $brand_id,
			'eventName'  => $event_name,
			'eventTime'  => $event_time,
			'contact'    => $contact,
			'properties' => $props,
		);

		self::track( wp_json_encode( $body, JSON_UNESCAPED_SLASHES ) );
	}

	private static function track( $body = '' ) {
		$response = wp_remote_post(
			OMNISEND_EVENTS_TRACKING_URL,
			array(
				'blocking' => false,
				'headers'  => array(
					'Content-Type' => 'application/json',
				),
				'body'     => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			Omnisend_Logger::error( $response->get_error_message() );
		}
	}
}
