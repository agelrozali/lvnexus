<?php
/**
* Register css styles, javascript files front end
*
* @package ctc
* @since 2.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Scripts' ) ) :

class HT_CTC_Scripts {

    public function __construct() {
        $this->hooks();
	}

    public function hooks() {
        add_action('wp_enqueue_scripts', [$this, 'register_scripts'], 1 );
    }

    /**
	 * Register styles - front end ( non admin )
	 *
	 * @since 1.0
	 */
    function register_scripts() {

        $os = get_option('ht_ctc_othersettings');
        $cb = get_option('ht_ctc_code_blocks');

        /**
         * if amp_is_request no need to add scripts.
         * 
         * Note: amp_is_request should call after 'parse_query' action. so check here only. i.e. in wp_enqueue_scripts 
         * ref: https://amp-wp.org/reference/function/amp_is_request/
         * 
         * @since 3.20
         */
        if ( isset($os['amp']) ) {
            if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
                return;
            }
        }

        // true/false
        $load_app_js_bottom = apply_filters( 'ht_ctc_fh_load_app_js_bottom', true );

        // js
        $css = 'main.css';
        $js = 'app.js';
        $woo_js = 'woo.js';
        $group_js = 'group.js';
        $share_js = 'share.js';

        if ( defined('HT_CTC_DEBUG_MODE') ) {
            $css = 'dev/main.dev.css';
            $js = 'dev/app.dev.js';
            $woo_js = 'dev/woo.dev.js';
            $group_js = 'dev/group.dev.js';
            $share_js = 'dev/share.dev.js';
        }


        do_action('ht_ctc_ah_scripts_before');

        // enqueue main.css
        wp_enqueue_style( 'ht_ctc_main_css', plugins_url( "new/inc/assets/css/$css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );

        // app.js for all (chat)
        wp_enqueue_script( 'ht_ctc_app_js', plugins_url( "new/inc/assets/js/$js", HT_CTC_PLUGIN_FILE ), array ( 'jquery' ), HT_CTC_VERSION, $load_app_js_bottom );

        // woocommerce
        if ( class_exists( 'WooCommerce' ) ) {

            // if - cart layout option is checked. 
            $woo_options = get_option('ht_ctc_woo_options');

            if ( isset( $woo_options['woo_single_layout_cart_btn']) || isset( $woo_options['woo_shop_layout_cart_btn']) ) {
                wp_enqueue_script( 'ht_ctc_woo_js', plugins_url( "new/inc/assets/js/$woo_js", HT_CTC_PLUGIN_FILE ), array ( 'jquery' ), HT_CTC_VERSION, $load_app_js_bottom );
            }

        }

        /**
         * Custom css
         * custom css code. ht_ctc_main_css - already enqueued above
         * dont use esc_attr. quotes, .. may not work.
         */
        $custom_css = ( isset ( $cb['custom_css'] ) ) ? ( $cb['custom_css'] ) : '';

        if ( '' !== $custom_css ) {

            if ( function_exists('sanitize_textarea_field') ) {
                $custom_css = sanitize_text_field( $custom_css );
            } else {
                $custom_css = '';
            }

            // to compress css
            $custom_css = preg_replace( '/\s+/', ' ', $custom_css );

            $allowed_html = wp_kses_allowed_html( 'post' );
		    $custom_css = wp_kses($custom_css, $allowed_html);

            wp_add_inline_style( 'ht_ctc_main_css', $custom_css );
        }


        // group.js
        if ( isset ( $os['enable_group'] ) ) { 
            wp_enqueue_script( 'ht_ctc_group_js', plugins_url( "new/inc/assets/js/$group_js", HT_CTC_PLUGIN_FILE ), array ( 'jquery', 'ht_ctc_app_js' ), HT_CTC_VERSION, $load_app_js_bottom );
        }

        // share.js
        if ( isset ( $os['enable_share'] ) ) { 
            wp_enqueue_script( 'ht_ctc_share_js', plugins_url( "new/inc/assets/js/$share_js", HT_CTC_PLUGIN_FILE ), array ( 'jquery', 'ht_ctc_app_js' ), HT_CTC_VERSION, $load_app_js_bottom );
        }

        do_action('ht_ctc_ah_scripts_after');

    }

}


new HT_CTC_Scripts();


endif; // END class_exists check