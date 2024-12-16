<?php
/**
 * Plugin Name: Omnisend for WooCommerce
 * Plugin URI: https://www.omnisend.com
 * Description: 100,000+ ecommerce stores use Omnisend to sell more stuff to more people. Send newsletters & SMS and build email lists with popups.
 * Version: 1.15.20
 * Author: Omnisend
 * Author URI: https://www.omnisend.com
 * Developer: Omnisend
 * Developer URI: https://developers.omnisend.com
 *
 * WC requires at least: 6.0
 * WC tested up to: 9.1
 *
 * Copyright: © 2018 Omnisend
 * License: GPLv3 or later License
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'OMNISEND_WOO_PLUGIN_FILE' ) ) {
	define( 'OMNISEND_WOO_PLUGIN_FILE', __FILE__ );
}

define( 'OMNISEND_APP_URL', 'https://app.omnisend.com' );
define( 'OMNISEND_API_URL', 'https://api.omnisend.com' );
define( 'OMNISEND_SNIPPET_URL', 'https://omnisnippet1.com/inshop/launcher-v2.js' );
define( 'OMNISEND_DISCOUNTS_KB_ARTICLE_URL', 'https://support.omnisend.com/en/articles/5846981-discount-content-block-for-woocommerce' );
define( 'OMNISEND_CALLBACK_URL', OMNISEND_APP_URL . '/REST/woocommerce/keys' );
define( 'OMNISEND_LOGS_KEY', 'qhyXzQsK7hnynKH0C6qVyVqCJWvWt8atAHB' );
define( 'OMNISEND_LOGS_URL', OMNISEND_APP_URL . '/woocommerce/plugin/logs' );
define( 'OMNISEND_ACTIVATION_URL', OMNISEND_APP_URL . '/woocommerce/plugin/activation' );
define( 'OMNISEND_UPDATE_URL', OMNISEND_APP_URL . '/woocommerce/plugin/update' );
define( 'OMNISEND_SETTINGS_PAGE', 'omnisend-woocommerce' );
define( 'OMNISEND_LOGS_PAGE', 'omnisend-logs' );
define( 'OMNISEND_SYNC_PAGE', 'omnisend-sync' );
define( 'OMNISEND_WC_API_APP_NAME', 'Omnisend App' );
define( 'OMNISEND_API_KEY_HEADER_NAME', 'X-API-KEY' );
define( 'OMNISEND_ORIGIN_HEADER_NAME', 'X-Omnisend-Origin' );
define( 'OMNISEND_ORIGIN_HEADER_VALUE', 'woocommerce' );
define( 'OMNISEND_LOGIN_URL', OMNISEND_APP_URL . '/login' );
define( 'OMNISEND_REGISTRATION_URL', OMNISEND_APP_URL . '/ecom/registration/start' );
define( 'OMNISEND_PLUGIN_INSTALL_URL', 'REST/woocommerce/install' );
define( 'OMNISEND_ONBOARDING_URL', OMNISEND_APP_URL . '/dashboard' );
define( 'OMNISEND_SWITCH_BRANDS_URL', OMNISEND_APP_URL . '/accounts/switch-store' );
define( 'OMNISEND_EVENTS_TRACKING_URL', OMNISEND_APP_URL . '/woocommerce/events/track' );
define( 'OMNISEND_LANDING_PAGE_CONTENT_URL', OMNISEND_APP_URL . '/REST/woocommerce/plugin/content' );

require_once 'manager/class-omnisend-logger.php';
require_once 'manager/class-omnisend-manager-assistant.php';
require_once 'manager/class-omnisend-helper.php';
require_once 'manager/class-omnisend-empty-required-fields-exception.php';
require_once 'manager/class-omnisend-manager.php';
require_once 'manager/class-omnisend-sync-manager.php';
require_once 'manager/class-omnisend-server-session.php';
require_once 'manager/class-omnisend-user-storage.php';
require_once 'manager/class-omnisend-contact-resolver.php';
require_once 'manager/class-omnisend-event-tracker.php';
require_once 'manager/class-omnisend-logs-sender.php';
require_once 'manager/class-omnisend-settings.php';
require_once 'manager/class-omnisend-notifications.php';
require_once 'manager/class-omnisend-content.php';
require_once 'manager/class-omnisend-contact-cache.php';
/*Include Model classes*/
require_once 'model/class-omnisend-product.php';
require_once 'model/class-omnisend-contact.php';
require_once 'model/class-omnisend-cart.php';
require_once 'model/class-omnisend-cart-event.php';
require_once 'model/class-omnisend-order.php';
require_once 'model/class-omnisend-category.php';
require_once 'model/class-omnisend-sync.php';
require_once 'model/class-omnisend-connection-error.php';
/*Include Ajax classes & functions*/
require_once 'class-omnisend-operation-status.php';
require_once 'class-omnisend-ajax.php';
/*Include settings page display function*/
require_once 'omnisend-settings-page.php';
/*Include logs page display function*/
require_once 'omnisend-logs.php';
/*Include WooCommerce hooks*/
require_once 'omnisend-woocommerce-hooks.php';
/*Include WooCommerce hooks*/
require_once 'omnisend-rebuild-cart.php';
/* Include repository classes */
require_once 'class-omnisend-sync-stats.php';
require_once 'class-omnisend-all-sync-stats.php';
require_once 'class-omnisend-sync-stats-repository.php';
/* Include views */
require_once 'view/settings/api-access-notice.php';
require_once 'view/settings/navigation-tabs.php';
require_once 'view/settings/account-information.php';
require_once 'view/settings/connected.php';
require_once 'view/settings/connection.php';
require_once 'view/settings/permalink-notice.php';
require_once 'view/settings/connection-error-notice.php';
require_once 'view/settings/tag.php';
require_once 'view/settings/sync.php';
require_once 'view/settings/checkout-opt-in-checkbox.php';

require_once 'includes/omnisend-api.php';
require_once 'includes/omnisend-cart-event-filter.php';
require_once 'includes/class-omnisend-install.php';
require_once 'includes/class-omnisend-logs.php';
require_once 'includes/blocks/init.php';

/*Declare plugin's settings page*/
add_action( 'admin_menu', 'omnisend_woocommerce_menu' );

function omnisend_woocommerce_menu() {
	$omnisend_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyIiBoZWlnaHQ9IjE5MiIgdmlld0JveD0iMCAwIDE5MiAxOTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xNzAuNiA2OC41OTk5SDE2MC44QzE1OS41IDc3LjI5OTkgMTUyIDgzLjk5OTkgMTQyLjkgODMuOTk5OUgxMDJMMTA2LjQgOTQuNjk5OUwxMTAuMSAxMDMuN0wxMDEuNiA5OC45OTk5TDc0LjYgODMuOTk5OUg2OS40QzY4LjkgODUuODk5OSA2OC41IDg3Ljg5OTkgNjguNSA4OS44OTk5VjE3MC41QzY4LjUgMTgyLjMgNzguMSAxOTEuOSA4OS45IDE5MS45SDk4LjlIMTI2LjZWMTc0LjJDMTI2LjYgMTU3LjIgMTI0LjUgMTQ0LjEgMTExLjggMTQwLjRWMTI2LjVDMTMyLjQgMTI4LjMgMTQyLjYgMTQzLjYgMTQyLjYgMTY4LjhWMTkySDE1OS40SDE3MC41QzE4Mi4zIDE5MiAxOTEuOSAxODIuNCAxOTEuOSAxNzAuNlY4OS44OTk5QzE5MiA3OC4wOTk5IDE4Mi40IDY4LjU5OTkgMTcwLjYgNjguNTk5OVpNMTMwLjkgMTEzLjFDMTI0LjQgMTEzLjEgMTE5LjEgMTA3LjggMTE5LjEgMTAxLjNDMTE5LjEgOTQuNzk5OSAxMjQuNCA4OS40OTk5IDEzMC45IDg5LjQ5OTlDMTM3LjQgODkuNDk5OSAxNDIuNyA5NC43OTk5IDE0Mi43IDEwMS40QzE0Mi43IDEwNy45IDEzNy40IDExMy4xIDEzMC45IDExMy4xWiIgZmlsbD0iIzlBQTFBNyIvPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTEzMy44IDI4Ljk5OTlDMTMxLjUgMjguNTk5OSAxMjkuMiAyOS44OTk5IDEyNy4xIDMyLjk5OTlDMTI1LjUgMzUuMjk5OSAxMjQuMyAzNy44OTk5IDEyMy43IDQwLjU5OTlDMTIzLjMgNDIuMDk5OSAxMjMuMiA0My42OTk5IDEyMy4yIDQ1LjI5OTlDMTIzLjIgNDcuMDk5OSAxMjMuNiA0OS4wOTk5IDEyNC40IDUxLjA5OTlDMTI1LjQgNTMuNTk5OSAxMjYuNyA1NC45OTk5IDEyOC4yIDU1LjI5OTlDMTI5LjggNTUuNTk5OSAxMzEuNSA1NC44OTk5IDEzMy40IDUzLjE5OTlDMTM1LjggNTEuMDk5OSAxMzcuNCA0Ny44OTk5IDEzOC4zIDQzLjY5OTlDMTM4LjUgNDIuMTk5OSAxMzguNyA0MC41OTk5IDEzOC44IDM4Ljk5OTlDMTM4LjggMzcuMTk5OSAxMzguNCAzNS4xOTk5IDEzNy42IDMzLjE5OTlDMTM2LjYgMzAuNjk5OSAxMzUuMyAyOS4yOTk5IDEzMy44IDI4Ljk5OTlaIiBmaWxsPSIjOUFBMUE3Ii8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNOTMuNCAyOC45OTk5QzkxLjEgMjguNTk5OSA4OC44IDI5Ljg5OTkgODYuNyAzMi45OTk5Qzg1LjEgMzUuMjk5OSA4My45IDM3Ljg5OTkgODMuMyA0MC41OTk5QzgyLjkgNDIuMDk5OSA4Mi44IDQzLjY5OTkgODIuOCA0NS4yOTk5QzgyLjggNDcuMDk5OSA4My4yIDQ5LjA5OTkgODQgNTEuMDk5OUM4NSA1My41OTk5IDg2LjMgNTQuOTk5OSA4Ny44IDU1LjI5OTlDODkuNCA1NS41OTk5IDkxLjEgNTQuODk5OSA5MyA1My4xOTk5Qzk1LjQgNTEuMDk5OSA5NyA0Ny44OTk5IDk3LjkgNDMuNjk5OUM5OC4yIDQyLjE5OTkgOTguNCA0MC41OTk5IDk4LjQgMzguOTk5OUM5OC40IDM3LjE5OTkgOTggMzUuMTk5OSA5Ny4yIDMzLjE5OTlDOTYuMiAzMC42OTk5IDk0LjkgMjkuMjk5OSA5My40IDI4Ljk5OTlaIiBmaWxsPSIjOUFBMUE3Ii8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNMTQyLjkgMi41OTk5MUgxNC43QzYuNiAyLjU5OTkxIC0xLjQ3NTIxZS0wNiA5LjE5OTkxIDAuMDk5OTk4NSAxNy4xOTk5VjY1Ljk5OTlDMC4wOTk5OTg1IDc0LjA5OTkgNi43IDgwLjU5OTkgMTQuOCA4MC41OTk5SDc1LjVMMTAzLjIgOTUuOTk5OUw5Ni45IDgwLjU5OTlIMTQyLjlDMTUxIDgwLjU5OTkgMTU3LjYgNzQuMDk5OSAxNTcuNiA2NS45OTk5VjE3LjE5OTlDMTU3LjYgOS4wOTk5MSAxNTEgMi41OTk5MSAxNDIuOSAyLjU5OTkxWk02NC44IDY2Ljg5OTlDNjQuOSA2OC40OTk5IDY0LjcgNjkuODk5OSA2NCA3MS4wOTk5QzYzLjIgNzIuNDk5OSA2Mi4xIDczLjI5OTkgNjAuNiA3My4zOTk5QzU4LjkgNzMuNDk5OSA1Ny4yIDcyLjc5OTkgNTUuNSA3MC45OTk5QzQ5LjUgNjQuODk5OSA0NC43IDU1Ljc5OTkgNDEuMyA0My42OTk5QzM3LjEgNTEuODk5OSAzNCA1OC4wOTk5IDMyIDYyLjE5OTlDMjguMiA2OS4zOTk5IDI1IDczLjE5OTkgMjIuMyA3My4zOTk5QzIwLjYgNzMuNDk5OSAxOS4xIDcyLjA5OTkgMTcuOCA2OC45OTk5QzE0LjUgNjAuNTk5OSAxMSA0NC4zOTk5IDcuMiAyMC4yOTk5QzYuNyAxOC40OTk5IDcuMSAxNi45OTk5IDggMTUuNzk5OUM4LjkgMTQuNTk5OSAxMC4zIDEzLjg5OTkgMTIuMSAxMy43OTk5QzE1LjQgMTMuNDk5OSAxNy4zIDE1LjA5OTkgMTcuNyAxOC4zOTk5QzE5LjcgMzEuNzk5OSAyMS45IDQzLjE5OTkgMjQuMiA1Mi40OTk5TDM4LjMgMjUuNjk5OUMzOS42IDIzLjI5OTkgNDEuMiAyMS45OTk5IDQzLjEgMjEuODk5OUM0NS45IDIxLjY5OTkgNDcuNyAyMy40OTk5IDQ4LjQgMjcuMjk5OUM1MCAzNS43OTk5IDUyLjEgNDMuMDk5OSA1NC41IDQ5LjE5OTlDNTYuMSAzMi45OTk5IDU5IDIxLjE5OTkgNjMgMTMuOTk5OUM2NCAxMi4xOTk5IDY1LjQgMTEuMjk5OSA2Ny4zIDExLjE5OTlDNjguOCAxMS4wOTk5IDcwLjEgMTEuNDk5OSA3MS40IDEyLjQ5OTlDNzIuNiAxMy40OTk5IDczLjMgMTQuNjk5OSA3My40IDE2LjE5OTlDNzMuNSAxNy4zOTk5IDczLjMgMTguMjk5OSA3Mi44IDE5LjI5OTlDNzAuMyAyMy44OTk5IDY4LjIgMzEuNjk5OSA2Ni42IDQyLjQ5OTlDNjUgNTIuOTk5OSA2NC40IDYxLjA5OTkgNjQuOCA2Ni44OTk5Wk0xMDQuMSA1Ni42OTk5QzEwMC4zIDYzLjA5OTkgOTUuMiA2Ni4yOTk5IDg5IDY2LjI5OTlDODcuOSA2Ni4yOTk5IDg2LjcgNjYuMTk5OSA4NS41IDY1Ljg5OTlDODAuOSA2NC45OTk5IDc3LjUgNjIuNDk5OSA3NS4yIDU4LjQ5OTlDNzMuMSA1NC45OTk5IDcyLjEgNTAuNjk5OSA3Mi4xIDQ1Ljc5OTlDNzIuMSAzOS4xOTk5IDczLjggMzMuMTk5OSA3Ny4xIDI3LjY5OTlDODEgMjEuMjk5OSA4NiAxOC4wOTk5IDkyLjIgMTguMDk5OUM5My40IDE4LjA5OTkgOTQuNiAxOC4yOTk5IDk1LjcgMTguNDk5OUMxMDAuMiAxOS4zOTk5IDEwMy43IDIxLjg5OTkgMTA2IDI1Ljg5OTlDMTA4LjEgMjkuMzk5OSAxMDkuMSAzMy40OTk5IDEwOS4xIDM4LjQ5OTlDMTA5LjEgNDUuMTk5OSAxMDcuNCA1MS4xOTk5IDEwNC4xIDU2LjY5OTlaTTE0NC41IDU2LjY5OTlDMTQwLjcgNjMuMDk5OSAxMzUuNiA2Ni4yOTk5IDEyOS40IDY2LjI5OTlDMTI4LjMgNjYuMjk5OSAxMjcuMSA2Ni4xOTk5IDEyNS45IDY1Ljg5OTlDMTIxLjMgNjQuOTk5OSAxMTcuOSA2Mi40OTk5IDExNS42IDU4LjQ5OTlDMTEzLjUgNTQuOTk5OSAxMTIuNSA1MC42OTk5IDExMi41IDQ1Ljc5OTlDMTEyLjUgMzkuMTk5OSAxMTQuMiAzMy4xOTk5IDExNy41IDI3LjY5OTlDMTIxLjQgMjEuMjk5OSAxMjYuNCAxOC4wOTk5IDEzMi42IDE4LjA5OTlDMTMzLjggMTguMDk5OSAxMzUgMTguMjk5OSAxMzYuMSAxOC40OTk5QzE0MC43IDE5LjM5OTkgMTQ0LjEgMjEuODk5OSAxNDYuNCAyNS44OTk5QzE0OC41IDI5LjM5OTkgMTQ5LjUgMzMuNDk5OSAxNDkuNSAzOC40OTk5QzE0OS41IDQ1LjE5OTkgMTQ3LjggNTEuMTk5OSAxNDQuNSA1Ni42OTk5WiIgZmlsbD0iIzlBQTFBNyIvPgo8L3N2Zz4K';

	$page_title = 'Omnisend for WooCommerce';
	$menu_title = get_menu_title( 'Omnisend for WooCommerce' );
	$capability = 'manage_options';
	$menu_slug  = OMNISEND_SETTINGS_PAGE;
	$function   = 'omnisend_show_settings_page';

	// Nonce verification is not required here.
 	// phpcs:disable WordPress.Security.NonceVerification
	if ( isset( $_GET['tab'] ) && $_GET['tab'] === OMNISEND_LOGS_PAGE ) {
		$function = 'omnisend_show_logs';
	}
	// phpcs:enable

	// Nonce verification is not required here.
 	// phpcs:disable WordPress.Security.NonceVerification
	if ( isset( $_GET['tab'] ) && $_GET['tab'] === OMNISEND_SYNC_PAGE ) {
		$function = 'omnisend_show_sync';
	}
	// phpcs:enable

	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $omnisend_icon, 2 );
}

/**
 * Constructs plugin menu title.
 *
 * @return string
 */
function get_menu_title( $title ) {
	$omnisend_notification_count = Omnisend_Notifications::get_count();

	return $omnisend_notification_count ? sprintf( $title . ' <span class="awaiting-mod">%d</span>', $omnisend_notification_count ) : $title;
}

/*Include scripts and styles for settings page and get started notice*/
add_action( 'admin_enqueue_scripts', 'omnisend_admin_scripts_and_styles' );
function omnisend_admin_scripts_and_styles() {
	// Nonce verification is not required here.
	// phpcs:disable WordPress.Security.NonceVerification
	if ( isset( $_GET['page'] ) && $_GET['page'] === OMNISEND_SETTINGS_PAGE ) {
		omnisend_register_localize_enqueue_script(
			'omnisend-settings-script',
			'assets/js/omnisend-settings-script.js',
			'omnisend_settings_script_var',
			'omnisend-settings-script-nonce'
		);
		wp_enqueue_style(
			'omnisend-admin-style.css',
			plugin_dir_url( __FILE__ ) . 'assets/css/omnisend-admin-style.css?' . time(),
			array(),
			'1.0.0'
		);
		wp_enqueue_style(
			'roboto.css',
			plugin_dir_url( __FILE__ ) . 'assets/fonts/roboto/roboto.css?' . time(),
			array(),
			'1.0.0'
		);
	}
	// phpcs:enable
}

function omnisend_register_localize_enqueue_script( $handle, $file, $object_name, $nonce_action ) {
	$src = plugin_dir_url( __FILE__ ) . $file . '?' . time();
	wp_register_script( $handle, $src, array(), '1.0.0', true );
	wp_localize_script(
		$handle,
		$object_name,
		array(
			'nonce' => wp_create_nonce( $nonce_action ),
		)
	);
	wp_enqueue_script(
		$handle,
		$src,
		array(),
		'1.0.0',
		true
	);
}

/*Include front scripts */
add_action( 'wp_enqueue_scripts', 'omnisend_front_scripts_and_styles' );
function omnisend_front_scripts_and_styles() {
	$handle = 'omnisend-front-script.js';
	$file   = plugin_dir_url( __FILE__ ) . 'assets/js/omnisend-front-script.js?' . time();

	wp_register_script( $handle, $file, array(), '1.0.0', true );
	wp_localize_script(
		$handle,
		'omnisend_woo_data',
		array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'contact_id' => Omnisend_User_Storage::get_contact_id(),
			'nonce'      => wp_create_nonce( 'omnisend-front-script-nonce' ),
		)
	);
	wp_enqueue_script( $handle, $file, array(), '1.0.0', true );
}

/*After plugin activation - go to settings page*/
add_action( 'activated_plugin', 'omnisend_activation' );
function omnisend_activation( $plugin ) {
	Omnisend_Logger::hook();
	if ( $plugin == plugin_basename( __FILE__ ) ) {
		omnisend_activated();
	}
}

/**
 * Define Omnisend cron schedules.
 *
 * @param array $schedules List of WP scheduled cron jobs.
 *
 * @return array
 */
function omnisend_cron_schedules( $schedules ) {
	$schedules['omnisend_every_two_minutes'] = array(
		'interval' => 60 * 2,
		'display'  => __( 'Every 2 minutes', 'omnisend-woocommerce' ),
	);

	$schedules['omnisend_every_five_minutes'] = array(
		'interval' => 60 * 5,
		'display'  => __( 'Every 5 minutes', 'omnisend-woocommerce' ),
	);

	return $schedules;
}

/**
 * Init Omnisend crons.
 *
 * @return void
 */
function omnisend_init_crons() {
	// phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
	add_filter( 'cron_schedules', 'omnisend_cron_schedules' );

	Omnisend_Logs::init();
}

add_action( 'init', 'omnisend_init_crons' );

function omnisend_activated() {
	Omnisend_Logger::enable_logging();

	$first_activation = ! get_option( 'omnisend_account_id', null );

	if ( $first_activation ) {
		$default_checkout_opt_in_text = 'Email me with news and offers';

		Omnisend_Settings::add_checkout_opt_in_status( Omnisend_Settings::STATUS_ENABLED, 'default' );
		Omnisend_Settings::add_checkout_opt_in_text( $default_checkout_opt_in_text, 'default' );
	}
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'omnisend_add_plugin_settings_link' );
function omnisend_add_plugin_settings_link( $links ) {
	$link = '<a href="' . admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE ) . '">' . __( 'Settings' ) . '</a>';
	array_unshift( $links, $link );

	return $links;
}

add_action( 'admin_notices', 'omnisend_show_connection_notice' );
function omnisend_show_connection_notice() {
	if ( Omnisend_Helper::is_omnisend_connected() ) {
		return;
	}
	?>
	<div class="notice notice-warning is-dismissible">
		<p><strong>Connect your WooCommerce store to Omnisend</strong></p>
		<p>Collect, convert, and keep new customers with automated email & SMS. Connect your store and grow your audience with forms, recover sales with Abandoned cards, and Checkout automation.</p>
		<p>
			<a class="button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE ) ); ?>">
			Connect your store to Omnisend
			</a>
		</p>
	</div>
	<?php
}

add_action( 'admin_notices', 'omnisend_show_api_access_notice' );
function omnisend_show_api_access_notice() {
	if ( Omnisend_Manager::is_setup() && ! Omnisend_Helper::is_woocommerce_api_access_granted() ) {
		$omnisend_account_id = get_option( 'omnisend_account_id', null );
		$full_url            = Omnisend_Helper::get_wc_auth_url( $omnisend_account_id );

		?>
		<div class="notice notice-error is-dismissible">
			<p>
				Add missing permissions to keep Omnisend working with WooCommerce.
				<a id="wp-admin-auth-notice-link" href="<?php echo esc_url( $full_url ); ?>">Add permissions
				</a>
			</p>
		</div>
		<?php
	}
}

add_action( 'before_woocommerce_init', 'omnisend_declare_wc_hpos_compatibility' );
function omnisend_declare_wc_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

register_activation_hook( __FILE__, 'omnisend_activate' );
function omnisend_activate() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	Omnisend_Install::notify_about_plugin_activation();
}

register_deactivation_hook( __FILE__, 'omnisend_deactivate' );
function omnisend_deactivate() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	Omnisend_install::deactivate();
}

register_uninstall_hook( __FILE__, 'omnisend_uninstall' );
function omnisend_uninstall() {
	Omnisend_Install::uninstall();
}
