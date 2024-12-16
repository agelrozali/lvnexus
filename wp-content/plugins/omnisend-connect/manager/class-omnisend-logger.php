<?php
/**
 * Omnisend Logger Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Logger {
	private static $log_debug = false;

	public static function enable_logging() {
		self::ensure_table_exists();
		Omnisend_Settings::set_logs_status( Omnisend_Settings::STATUS_ENABLED, Omnisend_Settings::SOURCE_ADMIN );
	}

	public static function disable_logging() {
		Omnisend_Settings::set_logs_status( Omnisend_Settings::STATUS_DISABLED, Omnisend_Settings::SOURCE_ADMIN );
	}

	public static function is_logging_enabled() {
		return Omnisend_Settings::get_logs_status() === Omnisend_Settings::STATUS_ENABLED;
	}

	public static function ensure_table_exists() {
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;
		global $charset_collate;

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}omnisend_logs (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`date` datetime,
				`type` varchar(10) CHARACTER SET utf8,
				`endpoint` varchar(15) CHARACTER SET utf8,
				`url` varchar(100) CHARACTER SET utf8,
				`message` longtext CHARACTER SET utf8,
				PRIMARY KEY (`id`)
			)$charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * This function should be placed in each function that is called by hook
	 * Will log info about hook if $log_debug is enabled
	 */
	public static function hook() {
		if ( ! self::$log_debug ) {
			return;
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$trace  = debug_backtrace();
		$action = ! empty( $trace[1]['function'] ) ? $trace[1]['function'] : '---';
		if ( ! empty( $trace[1]['class'] ) ) {
			$action .= $trace[1]['class'] . '::';
		}

		$hook = '---';
		foreach ( $trace as $item ) {
			if ( $item['function'] == 'do_action' && empty( $item['class'] ) ) {
				$hook = $item['args'][0];
				break;
			}
		}

		$message = "$hook | $action";
		self::log( 'hook', '', '', $message );
	}

	public static function debug( $message ) {
		if ( self::$log_debug ) {
			self::log( 'debug', '', '', $message );
		}
	}

	public static function info( $message ) {
		self::log( 'info', '', '', $message );
	}

	public static function warning( $message ) {
		self::log( 'warn', '', '', $message );
	}

	public static function error( $message ) {
		self::log( 'error', '', '', $message );
	}

	public static function log( $type, $endpoint, $url, $message ) {
		if ( ! self::is_logging_enabled() ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$wpdb->prefix . 'omnisend_logs',
			array(
				'type'     => $type,
				'date'     => current_time( 'mysql', 1 ),
				'url'      => $url,
				'endpoint' => $endpoint,
				'message'  => $message,
			)
		);
	}

	public static function get_all_logs() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}omnisend_logs order by id DESC" );
	}

	public static function get_logs( $from_id, $limit ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}omnisend_logs WHERE id > %d ORDER BY id ASC LIMIT %d", $from_id, $limit );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql );
	}

	public static function remove_all_logs() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM {$wpdb->prefix}omnisend_logs WHERE 1 = 1" );
	}

	public static function remove_old_synced_logs( $last_synced_log_id ) {
		global $wpdb;

		// Remove logs only if the table size reaches 10 000 lines.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}omnisend_logs" ) < 10000 ) {
			return;
		}

		$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}omnisend_logs WHERE id <= %d", $last_synced_log_id );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $sql );
	}
}
