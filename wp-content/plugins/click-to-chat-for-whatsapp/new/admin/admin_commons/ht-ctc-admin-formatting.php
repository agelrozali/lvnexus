<?php
/**
 * Formatting API - Admin related.
 * 
 * Encode emoji.. 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Encoding emoji 
 * 
 * To check the charset and run
 * @uses wp_encode_emoji
 * 		 on this page other functions. (so keep this function at the top)
 * 
 * @since 3.3.5
 * @param string $value		input value to convert emojis to html entity
 */
if ( ! function_exists('ht_ctc_wp_encode_emoji') ) {
	function ht_ctc_wp_encode_emoji($value = '') {
		
		if ( defined('DB_CHARSET') && 'utf8' == DB_CHARSET ) {

			if (function_exists('wp_encode_emoji')) {
				$value = wp_encode_emoji( $value );
			}
		}

		return $value;
	}
}


/**
 * sanitize text field - basic sanitize
 * 
 * @uses 
 * @since 4.11
 * @param 
 */
if ( ! function_exists('ht_ctc_sanitize_input_fields') ) {
	function ht_ctc_sanitize_input_fields($value = '') {

		if ( function_exists('sanitize_textarea_field') ) {
			$value = sanitize_textarea_field( $value );
		} else {
			$value = sanitize_text_field( $value );
		}
		
		return $value;
	}
}



/**
 * sanitize text editor
 * 
 * @uses 
 * @since 3.9.3
 * @param 
 */
if ( ! function_exists('ht_ctc_wp_sanitize_text_editor') ) {
	function ht_ctc_wp_sanitize_text_editor($value = '') {

		if ( !empty( $value) && '' !== $value ) {

			if ( function_exists('ht_ctc_wp_encode_emoji') ) {
				$value = ht_ctc_wp_encode_emoji( $value );
			}

			$allowed_html = wp_kses_allowed_html( 'post' );

			// $allowed_html['iframe'] = array(
			//     'src'             => true,
			//     'height'          => true,
			//     'width'           => true,
			//     'frameborder'     => true,
			//     'allowfullscreen' => true,
			//     'title' => true,
			//     'allow' => true,
			//     'autoplay' => true,
			//     'clipboard-write' => true,
			//     'encrypted-media' => true,
			//     'gyroscope' => true,
			//     'picture-in-picture' => true,
			// );

			$new_value = wp_kses($value, $allowed_html);
			// htmlentities this $new_value (double security ..)
			$new_value = htmlentities( $new_value );
			
			// (may not needed - but extra security)
			if ( function_exists('sanitize_textarea_field') ) {
				$new_value = sanitize_textarea_field( $new_value );
			} else {
				$new_value = sanitize_text_field( $new_value );
			}
		}
		
		return $new_value;
	}
}


/**
 * ht_ctc_sanitize_custom_css_code - santize custom css code
 * 
 * @uses 
 * 	admin other settings - options_sanitize - custom css code
 * @since 4.11
 */
if ( ! function_exists('ht_ctc_sanitize_custom_css_code') ) {
	function ht_ctc_sanitize_custom_css_code($value = '') {


		if ( !empty( $value) ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
			$value = wp_kses($value, $allowed_html);
		}

		if ( function_exists('sanitize_textarea_field') ) {
			$value = sanitize_textarea_field( $value );
		} else {
			$value = sanitize_text_field( $value );
		}

		// $value = htmlentities( $value );

		return $value;
	}
}