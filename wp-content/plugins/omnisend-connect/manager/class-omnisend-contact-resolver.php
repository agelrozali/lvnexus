<?php
/**
 * Omnisend Contact Resolver Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Contact_Resolver {

	public static function update_by_user_id( $user_id ) {
		if ( is_admin() ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user || ! is_object( $user ) || empty( $user->user_email ) ) {
			Omnisend_Logger::info( 'Unable to get user email by ID: ' . $user_id );
			return;
		}

		$contact_id = self::resolve_email_to_contact_id( $user->user_email );
		if ( ! $contact_id ) {
			Omnisend_Logger::debug( "Unable to resolve email $user->user_email to contactId" );
			return;
		}

		Omnisend_User_Storage::set_contact_id( $contact_id );
		Omnisend_Logger::debug( "ContactID $contact_id for email $user->user_email stored in user cookie for cart recovery" );
	}

	public static function update_by_email_and_contact_id( $email, $contact_id ) {
		Omnisend_Contact_Cache::set( $email, $contact_id );

		if ( Omnisend_User_Storage::get_contact_id() != $contact_id ) {
			Omnisend_User_Storage::set_contact_id( $contact_id );
			Omnisend_Logger::debug( "ContactID $contact_id for email $email stored in user cookie for cart recovery" );
		}
	}

	/**
	 * @param $email
	 *
	 * @return bool
	 */
	public static function update_by_email( $email ) {
		$contact_id = self::resolve_email_to_contact_id( $email );
		if ( ! $contact_id ) {
			return false;
		}

		if ( $contact_id == Omnisend_User_Storage::get_contact_id() ) {
			return true;
		}

		Omnisend_User_Storage::set_contact_id( $contact_id );
		Omnisend_Logger::debug( "ContactID $contact_id for email $email stored in user cookie for cart recovery" );

		return true;
	}

	/**
	 * @param $email
	 *
	 * @return mixed|null
	 */
	private static function resolve_email_to_contact_id( $email ) {
		$contact_id = Omnisend_Contact_Cache::get( $email );
		if ( $contact_id ) {
			Omnisend_Logger::debug( "Email $email resolved to contactID $contact_id (using WP options table)" );
			return $contact_id;
		}

		$contact_id = self::get_contact_id_from_omnisend( $email );
		if ( ! $contact_id ) {
			return null;
		}

		Omnisend_Logger::debug( "Email $email resolved to contactID $contact_id (using Omnisend API)" );
		Omnisend_Contact_Cache::set( $email, $contact_id );

		return $contact_id;
	}

	/**
	 * @param $email
	 *
	 * @return mixed|null
	 */
	private static function get_contact_id_from_omnisend( $email ) {
		$api_url     = OMNISEND_API_URL . '/v3/contacts?email=' . rawurldecode( $email );
		$curl_result = Omnisend_Helper::omnisend_api( $api_url, 'GET' );
		if ( $curl_result['code'] < 200 || $curl_result['code'] >= 300 ) {
			Omnisend_Logger::log( 'warn', 'contacts', $api_url, 'Unable to resolve contactId from email, code: ' . $curl_result['code'] );
			return null;
		}

		$response = json_decode( $curl_result['response'], true );
		return $response['contacts'][0]['contactID'];
	}
}
