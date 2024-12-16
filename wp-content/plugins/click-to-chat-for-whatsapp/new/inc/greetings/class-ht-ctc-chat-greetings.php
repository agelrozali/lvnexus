<?php
/**
 * WhatsApp Chat  - main page .. 
 * 
 * @uses ht-ctc-chat  if: 'no' !== $greetings['greetings_template']
 * 
 * @subpackage chat
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Chat_Greetings' ) ) :

class HT_CTC_Chat_Greetings {

    public function __construct() {
        $this->start();
	}

    public function start() {
        add_action( 'ht_ctc_ah_in_fixed_position', [$this, 'greetings_dialog'] );
    }


    function greetings_dialog() {
        
        $greetings = get_option('ht_ctc_greetings_options' );
        $chat = get_option('ht_ctc_chat_options');
        $greetings_settings = get_option('ht_ctc_greetings_settings');
        $g_box_classes = '';

        $ht_ctc_greetings = array();

        $ht_ctc_greetings['greetings_template'] = ( isset( $greetings['greetings_template']) ) ? esc_attr( $greetings['greetings_template'] ) : '';
        $ht_ctc_greetings['header_content'] = ( isset( $greetings['header_content']) ) ? esc_attr($greetings['header_content']) : '';
        $ht_ctc_greetings['main_content'] = ( isset( $greetings['main_content']) ) ? esc_attr($greetings['main_content']) : '';
        $ht_ctc_greetings['bottom_content'] = ( isset( $greetings['bottom_content']) ) ? esc_attr($greetings['bottom_content']) : '';
        $ht_ctc_greetings['call_to_action'] = ( isset( $greetings['call_to_action']) ) ? esc_attr( $greetings['call_to_action'] ) : '';

        $ht_ctc_greetings['is_opt_in'] = ( isset( $greetings_settings['is_opt_in']) ) ? esc_attr( $greetings_settings['is_opt_in'] ) : '';
        $ht_ctc_greetings['opt_in'] = ( isset( $greetings_settings['opt_in']) ) ? esc_attr( $greetings_settings['opt_in'] ) : '';

        if ('' == $ht_ctc_greetings['call_to_action']) {
            $ht_ctc_greetings['call_to_action'] = 'WhatsApp';
        }

        $ht_ctc_greetings = apply_filters( 'ht_ctc_fh_greetings_start', $ht_ctc_greetings );

        // return if template not set..
        if ( '' == $ht_ctc_greetings['greetings_template'] || 'no' == $ht_ctc_greetings['greetings_template'] ) {
            return;
        }


        $page_id = get_the_ID();
        // $page_id = get_queried_object_id();

        // $object_id = get_queried_object_id();
        // if (0 == $object_id || '' == $object_id) {
        //     $page_id = get_the_ID();
        // } else {
        //     $page_id = $object_id;
        // }


        $page_url = get_permalink();
        $post_title = esc_html( get_the_title() );

        if ( is_home() || is_front_page() ) {
            // is home page
            $page_url = home_url('/');
            // if home page is a loop then return site name.. (instead of getting the last post title in that loop)
            $post_title = HT_CTC_BLOG_NAME;

            // if home page is a page then return page title.. (if not {site} and {title} will be same )
            if ( is_page() ) {
                $post_title = esc_html( get_the_title() );
            }
        } elseif ( is_singular() ) {
            // is singular
            $page_url = get_permalink();
            $post_title = esc_html( get_the_title() );
        } elseif ( is_archive() ) {

            if ( isset($_SERVER['HTTP_HOST']) && $_SERVER['REQUEST_URI'] ) {
                $protocol = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http';
                $page_url = $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            }

            if ( is_category() ) {
                $post_title = single_cat_title( '', false );
            } elseif ( is_tag() ) {
                $post_title = single_tag_title( '', false );
            } elseif ( is_author() ) {
                $post_title = get_the_author();
            } elseif ( is_post_type_archive() ) {
                $post_title = post_type_archive_title( '', false );
            } elseif ( function_exists( 'is_tax') && function_exists( 'single_term_title') && is_tax() ) {
                $post_title = single_term_title( '', false );
            } else {
                if ( function_exists('get_the_archive_title') ) {
                    $post_title = get_the_archive_title();
                }
            }

        }

        // is shop page
        if ( class_exists( 'WooCommerce' ) && function_exists( 'is_shop') && function_exists( 'wc_get_page_id') && is_shop() ) {
            $page_id = wc_get_page_id( 'shop' );
            $post_title = esc_html( get_the_title( $page_id ) );
        }

        $ht_ctc_greetings['header_content'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['header_content'], 'Click to Chat for WhatsApp', 'greetings_header_content' );
        $ht_ctc_greetings['main_content'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['main_content'], 'Click to Chat for WhatsApp', 'greetings_main_content' );
        $ht_ctc_greetings['bottom_content'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['bottom_content'], 'Click to Chat for WhatsApp', 'greetings_bottom_content' );
        $ht_ctc_greetings['call_to_action'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['call_to_action'], 'Click to Chat for WhatsApp', 'greetings_call_to_action' );
        $ht_ctc_greetings['opt_in'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['opt_in'], 'Click to Chat for WhatsApp', 'greetings_opt_in' );
        

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
        
        

        // greetings dialog position based on chat icon/button position
        $g_position_r_l = ( isset( $chat['side_2']) ) ? esc_attr( $chat['side_2'] ) : 'right';

        $g_m_position_r_l = ( isset( $chat['mobile_side_2']) ) ? esc_attr( $chat['mobile_side_2'] ) : 'right';

        // is rtl page
        $rtl_page = "";
        if ( function_exists('is_rtl') && is_rtl() ) {
            $rtl_page = "yes";
        }

        // close button position
        $g_close_button_position = ('yes' == $rtl_page) ? 'left' : 'right';

        $g_close_button_styles = "position:absolute; top:0; $g_close_button_position:0; cursor:pointer; padding:5px; margin:4px; border-radius:50%; background-color: unset !important; z-index: 9999; line-height: 1;";

        // if desktop and mobile not same settings and not same position side
        if ( !isset( $chat['same_settings']) && $g_position_r_l !== $g_m_position_r_l ) {
            // $g_box_classes .= ('left' == $g_position_r_l) ? ' ctc_d_p_left ' : ' ctc_d_p_right ';
            $g_box_classes .= ('left' == $g_m_position_r_l) ? ' ctc_m_p_left ' : ' ctc_m_p_right ';
        }


        $ht_ctc_greetings['path'] = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/' . $ht_ctc_greetings['greetings_template']. '.php';

        // filter hook to update values... 
        $ht_ctc_greetings = apply_filters( 'ht_ctc_fh_greetings', $ht_ctc_greetings );

        if ( '' !== $ht_ctc_greetings['header_content'] ) {
            $ht_ctc_greetings['header_content'] = html_entity_decode(wp_kses($ht_ctc_greetings['header_content'], $allowed_html) );
            $ht_ctc_greetings['header_content'] = str_replace( array('{url}', '{title}', '{site}' ),  array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['header_content'] );
        }
        if ( '' !== $ht_ctc_greetings['main_content'] ) {
            $ht_ctc_greetings['main_content'] = html_entity_decode(wp_kses($ht_ctc_greetings['main_content'], $allowed_html) );
            $ht_ctc_greetings['main_content'] = str_replace( array('{url}', '{title}', '{site}' ),  array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['main_content'] );
        }
        if ( '' !== $ht_ctc_greetings['bottom_content'] ) {
            $ht_ctc_greetings['bottom_content'] = html_entity_decode(wp_kses($ht_ctc_greetings['bottom_content'], $allowed_html) );
            $ht_ctc_greetings['bottom_content'] = str_replace( array('{url}', '{title}', '{site}' ),  array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['bottom_content'] );
        }
        if ( '' !== $ht_ctc_greetings['is_opt_in'] && '' !== $ht_ctc_greetings['opt_in'] ) {
            $ht_ctc_greetings['opt_in'] = html_entity_decode(wp_kses($ht_ctc_greetings['opt_in'], $allowed_html) );
            $ht_ctc_greetings['opt_in'] = str_replace( array('{url}', '{title}', '{site}' ),  array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['opt_in'] );
        }

        $box_shadow = '0px 1px 9px 0px rgba(0,0,0,.14)';
        if ( 'greetings-2' == $ht_ctc_greetings['greetings_template'] ) {
            $box_shadow = '0px 0px 5px 1px rgba(0,0,0,.14)';
        }

        /**
         * greetings dialog size
         * ctc_m_full_width: class to make mobile full width for medium and large
         */
        $g_size = ( isset($greetings_settings['g_size']) ) ? esc_attr( $greetings_settings['g_size'] ) : 's';

        $min_width = '300px';
        $ctc_m_full_width = '';
        
        if ('s' == $g_size) {
        } else if ( 'm' == $g_size ) {
            $min_width = '330px';
            $ctc_m_full_width = 'ctc_m_full_width';
        } else if ( 'l' == $g_size ) {
            $min_width = '360px';
            $ctc_m_full_width = 'ctc_m_full_width';
        }
        


        $box_layout_bg_color = '';
        if ( 'greetings-1' == $ht_ctc_greetings['greetings_template'] || 'greetings-2' == $ht_ctc_greetings['greetings_template'] ) {
        } else {
            $box_layout_bg_color = 'background-color: #ffffff;';
        }


        /**
         * 
         * for inside close button - to the close button, ctc_greetings_close_btn added background-color: unset !important; border-radius:50%; for backword compatibility i.e. user changed by adding css.. 
         */
        if ( is_file( $ht_ctc_greetings['path'] ) ) {

            $template = $ht_ctc_greetings['greetings_template'];
            $g_box_classes .= " template-$template";

            // styles specific to rtl pages.. 
            if ('yes' == $rtl_page) {
                ?>
                <style id="ht_ctc_rtl">.g_header_badge_online {left: 0;right: unset;}</style>
                <?php
            }
            ?>
            
            <div style="position: relative; bottom: 18px; cursor: auto;" class="ht_ctc_greetings <?= $ctc_m_full_width ?>">

                <div class="ht_ctc_chat_greetings_box <?= $g_box_classes ?>" style="display: none; position: absolute; bottom: 0px; <?= $g_position_r_l ?>: 0px; min-width: <?= $min_width ?>; max-width: 420px; ">

                    <div class="ht_ctc_chat_greetings_box_layout" style="max-height: 84vh; overflow-y:auto; <?= $box_layout_bg_color ?> box-shadow: <?= $box_shadow ?>; border-radius:8px;clear:both;">

                        <span style="<?= $g_close_button_styles ?>" class="ctc_greetings_close_btn">
                            <svg style="color:lightgray; background-color: unset !important; border-radius:50%;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </span>

                        <div class="ctc_greetings_template">
                            <?php include $ht_ctc_greetings['path']; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

    }


}


new HT_CTC_Chat_Greetings();

endif; // END class_exists check