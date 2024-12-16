<?php
/**
 * Omnisend Checkout Integration Class
 *
 * @package OmnisendPlugin
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

define( 'OMNISEND_WOOCOMMERCE_CHECKOUT_BLOCK_VERSION', '0.1.0' );

/**
 * Class for integrating with WooCommerce Blocks
 */
class Omnisend_Checkout_Block_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return OMNISEND_CHECKOUT_PLUGIN_NAME;
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$this->register_newsletter_block_frontend_scripts();
		$this->register_newsletter_block_editor_scripts();
		$this->register_editor_blocks();
		$this->register_main_integration();
	}

	/**
	 * Registers the main JS file required to add filters and Slot/Fills.
	 */
	public function register_main_integration() {
		$script_path = '/build/index.js';
		$style_path  = '/build/omnisend-checkout-block.css';

		$script_url = plugins_url( $script_path, __FILE__ );
		$style_url  = plugins_url( $style_path, __FILE__ );

		$script_asset_path = __DIR__ . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_path ),
			);

		wp_enqueue_style(
			'omnisend-woocommerce-checkout-block-blocks-integration',
			$style_url,
			array(),
			$this->get_file_version( $style_path )
		);

		wp_register_script(
			'omnisend-woocommerce-checkout-block-blocks-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'omnisend-woocommerce-checkout-block-blocks-integration',
			'omnisend-woocommerce-checkout-block',
			__DIR__ . '/languages'
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'omnisend-woocommerce-checkout-block-blocks-integration', 'omnisend-woocommerce-checkout-block-checkout-newsletter-subscription-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'omnisend-woocommerce-checkout-block-blocks-integration', 'omnisend-woocommerce-checkout-block-checkout-newsletter-subscription-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$data = array(
			'optInText'        => Omnisend_Settings::get_checkout_opt_in_text(),
			'optInEnabled'     => Omnisend_Settings::get_checkout_opt_in_status() === Omnisend_Settings::STATUS_ENABLED,
			'optInPreselected' => Omnisend_Settings::get_checkout_opt_in_preselected_status() === Omnisend_Settings::STATUS_ENABLED,
		);

		return $data;
	}

	public function register_editor_blocks() {
		register_block_type_from_metadata( __DIR__ . '/build/js/omnisend-checkout-block' );
		$style_path = '/build/omnisend-checkout-block.css';

		$style_url = plugins_url( $style_path, __FILE__ );
		wp_enqueue_style(
			'omnisend-woocommerce-checkout-block-checkout-newsletter-subscription-block',
			$style_url,
			array(),
			$this->get_file_version( $style_path )
		);
	}

	public function register_newsletter_block_editor_scripts() {
		$script_path       = '/build/omnisend-checkout-block.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = __DIR__ . '/build/omnisend-checkout-block.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'omnisend-woocommerce-checkout-block-checkout-newsletter-subscription-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'omnisend-woocommerce-checkout-block-newsletter-block-editor', // script handle.
			'omnisend-woocommerce-checkout-block', // text domain.
			__DIR__ . '/languages'
		);
	}

	public function register_newsletter_block_frontend_scripts() {
		$script_path       = '/build/omnisend-checkout-block-frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = __DIR__ . '/build/omnisend-checkout-block-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'omnisend-woocommerce-checkout-block-checkout-newsletter-subscription-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'omnisend-woocommerce-checkout-block-checkout-newsletter-subscription-block-frontend', // script handle.
			'omnisend-woocommerce-checkout-block', // text domain.
			__DIR__ . '/languages'
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return OMNISEND_WOOCOMMERCE_CHECKOUT_BLOCK_VERSION;
	}
}
