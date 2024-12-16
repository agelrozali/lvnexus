<?php
/**
 * Omnisend Contact Cache Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Contact_Cache {
	/**
	 * @param $email
	 *
	 * @return string|null
	 */
	public static function get( $email ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cache_entry = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT contact_id FROM {$wpdb->prefix}omnisend_contact_cache WHERE `email` = %s",
				array( $email )
			)
		);

		if ( ! $cache_entry ) {
			return '';
		}

		return $cache_entry->contact_id;
	}

	/**
	 * @param $email
	 * @param $contact_id
	 */
	public static function set( $email, $contact_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->replace(
			"{$wpdb->prefix}omnisend_contact_cache",
			array(
				'email'      => $email,
				'contact_id' => $contact_id,
			)
		);
	}

	public static function ensure_table_exists() {
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;
		global $charset_collate;

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}omnisend_contact_cache (
				`email` varchar(200) CHARACTER SET utf8,
				`contact_id` varchar(100) CHARACTER SET utf8,
				PRIMARY KEY (`email`)
			)$charset_collate;";

		dbDelta( $sql );

		self::cleanup_legacy_cache();
	}

	private static function cleanup_legacy_cache() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'omnisend_email_contact_ID_%'" );
	}
}
