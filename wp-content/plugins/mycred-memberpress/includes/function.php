<?php 
if ( ! defined( 'mycred_memberpress_SLUG' ) ) exit;

/**
 * Check Page
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'is_mycred_hook_page' ) ) :
	function is_mycred_hook_page( $page ){
		return ( strpos( $page, 'mycred' ) !== false && strpos( $page, 'hook' ) !== false );
	}
endif;


if ( ! function_exists( 'custom_limit' ) ) :
	function custom_limit() {
        return array(
            'x' => __('No limit', 'mycred_tutor_lms'),
            'd' => __('/ Day', 'mycred_tutor_lms'),
            'w' => __('/ Week', 'mycred_tutor_lms'),
            'm' => __('/ Month', 'mycred_tutor_lms'),
        );
    } 
endif;
