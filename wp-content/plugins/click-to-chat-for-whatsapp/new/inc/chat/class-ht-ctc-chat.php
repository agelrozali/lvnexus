<?php
/**
 * WhatsApp Chat  - main page .. 
 * 
 * @subpackage chat
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Chat' ) ) :

class HT_CTC_Chat {


    public function __construct() {
        $this->hooks();
    }

    function hooks() {

        $othersettings = get_option('ht_ctc_othersettings');

        // wp_footer / wp_head / get_footer
        $chat_load_hook = ( isset( $othersettings['chat_load_hook'] ) ) ? esc_attr($othersettings['chat_load_hook']) : 'wp_footer';
        // chat load hook filter hook to change (after admin settings)
        $chat_load_hook = apply_filters( 'ht_ctc_chat_load_position', $chat_load_hook );

        add_action( "$chat_load_hook", [$this, 'chat'] );

    }


    /**
     * Chat
     * 
     * @var $display - changes at show-hide.php
     * 
     * @return breaks
     *  - if number is not saved.(atleast null)
     *  - if its editing area of page builders
     */
    public function chat() {
        
        do_action('ht_ctc_ah_start_the_chat');

        $options = get_option('ht_ctc_chat_options');
        $othersettings = get_option('ht_ctc_othersettings');
        $greetings = get_option('ht_ctc_greetings_options');
        $greetings_settings = get_option('ht_ctc_greetings_settings');
        $type = "chat";
        $is_editor = '';

        // If db values are not correct
		if ( !is_array($options)  || !isset($options['number']) ) {
            return;
        }
		

        $ht_ctc_chat = array();
        $ht_ctc_os = array();
        $ht_ctc_settings = array();

        // includes..
        include_once HT_CTC_PLUGIN_DIR .'new/inc/commons/class-ht-ctc-formatting.php';

        if ( class_exists( 'HT_CTC_Formatting' ) && method_exists( 'HT_CTC_Formatting', 'is_page_builder_editor' ) ) {
            $is_editor = HT_CTC_Formatting::is_page_builder_editor();
            if ( 'y' == $is_editor ) {
                return;
            }
        }

        /**
         * dont get page level settings if its an archive page..
         */
        $is_page_level_settings = 'yes';

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

            //no page level settings for archive pages
            $is_page_level_settings = 'no';

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

        // page level
        $ht_ctc_pagelevel = [];


        if ( 'no' !== $is_page_level_settings ) {
            $ht_ctc_pagelevel = get_post_meta( $page_id, 'ht_ctc_pagelevel', true );
        }

        /**
         * show/hide
         * $page_display - page level settings  - show/hide/ null
         * $display - global display settings - changes at - show-hide.php
         */
        $display = '';
        $page_display = (isset($ht_ctc_pagelevel['show_hide'])) ? esc_attr($ht_ctc_pagelevel['show_hide']) : '';

        if ( 'show' !== $page_display ) {
            include HT_CTC_PLUGIN_DIR .'new/inc/commons/show-hide.php';
        }


        // is mobile
        $is_mobile = ht_ctc()->device_type->is_mobile();
        // style
        $ht_ctc_chat['style_desktop'] = (isset($options['style_desktop'])) ? esc_attr($options['style_desktop']) : '2';
        if (isset($options['same_settings'])) {
            $ht_ctc_chat['style_mobile'] = $ht_ctc_chat['style_desktop'];
        } else {
            $ht_ctc_chat['style_mobile'] = (isset($options['style_mobile'])) ? esc_attr($options['style_mobile']) : '2';
        }

        // position
        // default position overwrite at js, but useful in amp pages
        $default_position = '';
        include HT_CTC_PLUGIN_DIR .'new/inc/commons/position-to-place.php';
        $ht_ctc_chat['position'] = $position;
        $ht_ctc_chat['position_mobile'] = $position_mobile;
        
        // number
        $ht_ctc_chat['number'] = (isset($options['number'])) ? esc_attr($options['number']) : '';
        $ht_ctc_chat['call_to_action'] = (isset($options['call_to_action'])) ? __(esc_attr($options['call_to_action']) , 'click-to-chat-for-whatsapp' ) : '';
        $ht_ctc_chat['pre_filled'] = (isset($options['pre_filled'])) ? __(esc_attr($options['pre_filled']) , 'click-to-chat-for-whatsapp' ) : '';

        // safe side action .. if number not saved in new method
        if ( '' == $ht_ctc_chat['number'] ) {
            $cc = (isset($options['cc'])) ? esc_attr($options['cc']) : '';
            $num = (isset($options['num'])) ? esc_attr($options['num']) : '';
            if ( '' !== $cc && '' !== $num ) {
                $ht_ctc_chat['number'] = $cc . $num;
            }
        }

        $ht_ctc_chat['number'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['number'], 'Click to Chat for WhatsApp', 'number' );

        if ( isset($ht_ctc_pagelevel['number']) ) {
            $ht_ctc_chat['number'] = esc_attr($ht_ctc_pagelevel['number']);
        }

        // call to action
        $ht_ctc_chat['call_to_action'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['call_to_action'], 'Click to Chat for WhatsApp', 'call_to_action' );

        if ( isset($ht_ctc_pagelevel['call_to_action']) ) {
            $ht_ctc_chat['call_to_action'] = esc_attr($ht_ctc_pagelevel['call_to_action']);
        }

        // prefilled text
        $ht_ctc_chat['pre_filled'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['pre_filled'], 'Click to Chat for WhatsApp', 'pre_filled' );

        if ( isset($ht_ctc_pagelevel['pre_filled']) ) {
            $ht_ctc_chat['pre_filled'] = esc_attr($ht_ctc_pagelevel['pre_filled']);
        }

        $ht_ctc_chat['url_target_d'] = ( isset( $options['url_target_d'] ) ) ? esc_attr($options['url_target_d']) : '_blank';
        $ht_ctc_chat['url_structure_d'] = ( isset( $options['url_structure_d'] ) ) ? esc_attr($options['url_structure_d']) : '';
        $ht_ctc_chat['url_structure_m'] = ( isset( $options['url_structure_m'] ) ) ? esc_attr($options['url_structure_m']) : '';

        // is intl input type is added
        if ( isset($options['intl']) ) {
            $ht_ctc_chat['intl'] = '1';
        }

        $ht_ctc_chat['display_mobile'] = (isset($options['display_mobile'])) ? esc_attr($options['display_mobile']) : 'show';
        $ht_ctc_chat['display_desktop'] = (isset($options['display_desktop'])) ? esc_attr($options['display_desktop']) : 'show';

        // number not added and is administrator
        $no_number = '';
        
        if ( '' == $ht_ctc_chat['number'] ) {
            $no_number = "<p style='background-color:#ffffff; margin:0; border:1px solid #fbfbfb; padding:7px; border-radius:4px; box-shadow:5px 10px 8px #888888;'>No WhatsApp Number Found!</p>";
            if ( current_user_can('administrator') ) {
                $admin_url = admin_url( 'admin.php?page=click-to-chat' );
                $admin_link = "<a href='$admin_url'>WhatsApp number</a>";
                $no_number = "<p style='background-color:#ffffff; margin:0; border:1px solid #fbfbfb; padding:11px; border-radius:4px; box-shadow:5px 10px 8px #888888;'>No WhatsApp Number Found!<br><small style='color:red;'>Admin Notice:<br></small><small>Add $admin_link at pluign Settings<br>If already added, <strong>clear the Cache</strong> and try.<br>If still an issue, please contact plugin developers</small></p>";
            }
        }

        // notification badge
        $ht_ctc_chat['notification_badge'] = (isset($othersettings['notification_badge'])) ? 'show' : 'hide';
        $ht_ctc_chat['notification_count'] = (isset($othersettings['notification_count'])) ? esc_attr($othersettings['notification_count']) : '1';
        
        
        $notification_time = (isset($othersettings['notification_time'])) ? esc_attr($othersettings['notification_time']) : '';
        $notification_bg_color = (isset($othersettings['notification_bg_color'])) ? esc_attr($othersettings['notification_bg_color']) : '#ff4c4c';
        $notification_text_color = (isset($othersettings['notification_text_color'])) ? esc_attr($othersettings['notification_text_color']) : '#ffffff';
        
        $notification_border_color = (isset($othersettings['notification_border_color'])) ? esc_attr($othersettings['notification_border_color']) : '';
        $notification_border = ('' !== $notification_border_color) ? "border:2px solid $notification_border_color;" : '' ;
        

        // class names
        $ht_ctc_chat['class_names'] = "ht-ctc ht-ctc-chat ctc-analytics";
        $ht_ctc_chat['id'] = "ht-ctc-chat";
        // schedule
        $ht_ctc_chat['schedule'] = 'no';

        $zindex = (isset($othersettings['zindex'])) ? esc_attr($othersettings['zindex']) : '';
        $zindex = ('' == $zindex) ? '99999999' : $zindex;

        $analytics = (isset($othersettings['analytics'])) ? esc_attr($othersettings['analytics']) : 'all';

        $ht_ctc_chat['css'] = "display: none; cursor: pointer; z-index: $zindex;";

        // analytics
        $ht_ctc_os['is_ga_enable'] = 'yes';
        $ht_ctc_os['is_fb_pixel'] = 'yes';
        $ht_ctc_os['ga_ads'] = 'no';
        $ht_ctc_os['data-attributes'] = '';
        // @since v3.3.5 new way of adding attributes [data-attributes]
        $ht_ctc_os['attributes'] = '';

        // class name related to animations..
        $ht_ctc_os['class_names'] = '';
        // show effect
        $ht_ctc_os['show_effect'] = '';
        $ht_ctc_os['an_type'] = '';



        // hooks
        $ht_ctc_chat = apply_filters( 'ht_ctc_fh_chat', $ht_ctc_chat );
        $ht_ctc_os = apply_filters( 'ht_ctc_fh_os', $ht_ctc_os );
        $ht_ctc_settings = apply_filters( 'ht_ctc_fh_settings', $ht_ctc_settings );

        // pre-filled  - have to run after filter hook. 
        $ht_ctc_chat['pre_filled'] = str_replace( array('{{url}}', '{url}', '{{title}}', '{title}', '{{site}}', '{site}' ),  array( $page_url, $page_url, $post_title, $post_title, HT_CTC_BLOG_NAME, HT_CTC_BLOG_NAME ), $ht_ctc_chat['pre_filled'] );

        // style for desktop, mobile
        if ( 'yes' == $is_mobile ) {
            $ht_ctc_chat['style'] = $ht_ctc_chat['style_mobile'];
            $wp_device = 'ctc_wp_mobile';
        } else {
            $ht_ctc_chat['style'] = $ht_ctc_chat['style_desktop'];
            $wp_device = 'ctc_wp_desktop';
        }

        // @uses at styles / easy call (after filter hook)
        $style = $ht_ctc_chat['style'];
        $style_desktop = $ht_ctc_chat['style_desktop'];
        $style_mobile = $ht_ctc_chat['style_mobile'];
        $call_to_action = $ht_ctc_chat['call_to_action'];

        $other_classes = $ht_ctc_os['class_names'];

        $ht_ctc_chat['class_names'] .= " $wp_device style-$style $other_classes ";

        // call style
        $style = sanitize_file_name( $style );
        $path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles/style-' . $style. '.php';
        
        $style_desktop = sanitize_file_name( $style_desktop );
        $path_d = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles/style-' . $style_desktop. '.php';
        
        $style_mobile = sanitize_file_name( $style_mobile );
        $path_m = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles/style-' . $style_mobile. '.php';

        
        if ( '' == $call_to_action ) {
            if ( '1' == $style || '4' == $style || '6' == $style || '8' == $style ) {
                $call_to_action = "WhatsApp us";
            }
        }

        $display_css = "display: none; ";

        $side = ( isset( $options['side_2']) ) ? esc_attr( $options['side_2'] ) : 'right';
        $mobile_side = ( isset( $options['mobile_side_2']) ) ? esc_attr( $options['mobile_side_2'] ) : 'right';

        // @uses at styles.
        $is_same_side = 'yes';

        // if desktop and mobile not same settings and not same position side
        if ( !isset( $options['same_settings']) && $side !== $mobile_side ) {
            $is_same_side = 'no';
            $ht_ctc_chat['class_names'] .= " ctc_side_positions ";
        }



        // AMP
        $is_amp = false;
        $on = "";

        /**
         * AMP
         * ampforwp_is_amp_endpoint  / is_amp_endpoint / amp_is_request
         * 
         * scripts handles at class-ht-ctc-scripts.php
         */
        if ( isset($othersettings['amp']) ) {
            if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
            
                $is_amp = true;

                if ( 'yes' == $is_mobile ) {
                    if ( 'show' == $ht_ctc_chat['display_mobile'] ) {
                        $display_css = "";
                    }
                } else {
                    if ( 'show' == $ht_ctc_chat['display_desktop'] ) {
                        $display_css = "";
                    }
                }
                $display_css .= "cursor:pointer;";

                $pre = rawurlencode($ht_ctc_chat['pre_filled']);
                // 'single quote', 'double quote', '&', '<', '>'
                $pre = str_replace( array('%26%23039%3B', '%26quot%3B', '%26amp%3B', '%26lt%3B', '%26gt%3B'), array('', '', '', '<', '>'), $pre);
                $ext = $ht_ctc_chat['number'] . '?text=' . $pre;
                $wame_link = "https://wa.me/$ext";
                $on = "on=\"tap:AMP.navigateTo(url='$wame_link', target='_blank', opener='')\"";

                // no need to deregister here. since 3.20 handles while adding scripts.
                wp_deregister_script( 'ht_ctc_app_js' );
                wp_deregister_script( 'ht_ctc_woo_js' );
            }
        }
        

        /**
         * greetings
         * 
         *  dont load if its an AMP page or if no greetings dialog selected
         */
        if ( false == $is_amp ) {
            include HT_CTC_PLUGIN_DIR .'new/inc/greetings/class-ht-ctc-chat-greetings.php';
        }

        // webhook
        $hook_url = isset($othersettings['hook_url']) ? esc_attr( $othersettings['hook_url'] ) : '';
        $webhook_format = isset($othersettings['webhook_format']) ? esc_attr( $othersettings['webhook_format'] ) : 'string';
        
        /**
         * ctc
         */
        $ctc = array(

            'number' => $ht_ctc_chat['number'],
            'pre_filled' => $ht_ctc_chat['pre_filled'],
            'dis_m' => $ht_ctc_chat['display_mobile'],
            'dis_d' => $ht_ctc_chat['display_desktop'],
            'css' => $ht_ctc_chat['css'],
            'pos_d' => $ht_ctc_chat['position'],
            'pos_m' => $ht_ctc_chat['position_mobile'],
            'schedule' => $ht_ctc_chat['schedule'],
            'se' => $ht_ctc_os['show_effect'],
            'ani' => $ht_ctc_os['an_type'],
        );

        // desktop url structure if web whatsapp 
        if ( 'web' == $ht_ctc_chat['url_structure_d'] ) {
            $ctc['url_structure_d'] = 'web';
        }

        // mobile url structure if whatsapp://..
        if ( 'wa_colon' == $ht_ctc_chat['url_structure_m'] ) {
            $ctc['url_structure_m'] = 'wa_colon';
        }

        // url_target_d
        $ctc['url_target_d'] = $ht_ctc_chat['url_target_d'];

        // anlalytics count type
        if ( 'session' == $analytics ) {
            $ctc['analytics'] = $analytics;
        }

        // ga
        if ( 'yes' == $ht_ctc_os['is_ga_enable'] ) {
            $ctc['ga'] = 'yes';
        }
        
        // ads
        if ( 'yes' == $ht_ctc_os['ga_ads'] ) {
            $ctc['ads'] = 'yes';
        }

        // fb
        if ( 'yes' == $ht_ctc_os['is_fb_pixel'] ) {
            $ctc['fb'] = 'yes';
        }

        // adds only if hided on current page
            // global
        if ( 'no' == $display ) {
            $ctc['display'] = 'no';
        }
            // page level
        if ( 'hide' == $page_display ) {
            $ctc['page_display'] = 'hide';
        }

        // webhook
        if ( '' !== $hook_url ) {
            // $ctc hook url
            $ctc['hook_url'] = $hook_url;
            $hook_v = isset($othersettings['hook_v']) ? $othersettings['hook_v'] : '';

            if ( is_array($hook_v) ) {
                $hook_v = array_filter($hook_v);
                $hook_v = array_values($hook_v);
                $hook_v = array_map('esc_attr', $hook_v );
                
                if ( isset($hook_v[0]) ) {
                    // $ctc - hook values
                    $ctc['hook_v'] = $hook_v;
                }
            }
        }

        // webhook sharing data type. - json, stringify json
        if ('json' == $webhook_format) {
            $ctc['webhook_format'] = 'json';
        }

        // notification time
        if ('' !== $notification_time ) {
            $ctc['n_time'] = $notification_time;
        }


        // Greetings - init display ..
        $g_init = isset($greetings_settings['g_init']) ? esc_attr( $greetings_settings['g_init'] ) : 'default';
        $ctc['g_init'] = $g_init;

        // Greetings - display device based (if not all then add value)
        $g_device = isset($greetings_settings['g_device']) ? esc_attr( $greetings_settings['g_device'] ) : 'all';
        if ('all' !== $g_device) {
            $ctc['g_device'] = $g_device;
        }

        /**
         * ht_ctc_chat_var sends to front end in better way then ht_ctc_variables. so including event name here..
         */
        $g_an_event_name = (isset($othersettings['g_an_event_name'])) ? esc_attr( $othersettings['g_an_event_name'] ) : 'click to chat';
        $ctc['g_an_event_name'] = $g_an_event_name;

        $pixel_event_type = ( isset($othersettings['pixel_event_type']) ) ? esc_attr( $othersettings['pixel_event_type'] ) : 'trackCustom';
        $pixel_event_name = 'Click to Chat by HoliThemes';
        if ( 'trackCustom' == $pixel_event_type ) {
            if (isset($othersettings['pixel_custom_event_name']) && '' !== $othersettings['pixel_custom_event_name']) {
                $pixel_event_name = esc_attr( $othersettings['pixel_custom_event_name'] );
            }
        } else {
            if (isset($othersettings['pixel_standard_event_name']) && '' !== $othersettings['pixel_standard_event_name']) {
                // lead, ..
                $pixel_event_name = esc_attr( $othersettings['pixel_standard_event_name'] );
            }
        }
        $ctc['pixel_event_name'] = $pixel_event_name;

        $ctc = apply_filters( 'ht_ctc_fh_ctc', $ctc );

        // data-attribute - data-settings 
        $ht_ctc_settings = htmlspecialchars(json_encode($ctc), ENT_QUOTES, 'UTF-8');
        
        // localize script - ht_ctc_chat_var
        wp_localize_script( 'ht_ctc_app_js', 'ht_ctc_chat_var', $ctc );


        $g_an_params = ( isset($othersettings['g_an_params']) && is_array($othersettings['g_an_params']) ) ? array_map( 'esc_attr', $othersettings['g_an_params'] ) : '';
        $pixel_params = ( isset($othersettings['pixel_params']) && is_array($othersettings['pixel_params']) ) ? array_map( 'esc_attr', $othersettings['pixel_params'] ) : '';
        
        $g_an_value = ( isset( $options['g_an'] ) ) ? esc_attr( $options['g_an'] ) : 'ga4';

        $values = array(
            'g_an_event_name' => $g_an_event_name,
            'pixel_event_type' => $pixel_event_type,
            'pixel_event_name' => $pixel_event_name,
        );

        // google analytics params
        if ( is_array($g_an_params) && isset($g_an_params[0]) ) {

            foreach ($g_an_params as $param ) {
                $param_options = ( isset($othersettings[$param]) ) ? $othersettings[$param] : [];
                $key = ( isset($param_options['key']) ) ? esc_attr($param_options['key']) : '';
                $value = ( isset($param_options['value']) ) ? esc_attr($param_options['value']) : '';

                if ( !empty($key) && !empty($value) ) {
                    $values['g_an_params'][] = $param;
                    $values[$param] = [
                        'key' => $key,
                        'value'=> $value,
                    ];
                }
            }
        } else {

            // if user not yet saved the params. (backward compatibility)
            if (!isset($othersettings['parms_saved'])) {
                if ('ga' == $g_an_value) {
                    $values['g_an_params'] = [
                        'g_an_param_1',
                        'g_an_param_2',
                    ];

                    $values['g_an_param_1'] = [
                        'key'=> 'event_category',
                        'value'=> 'Click to Chat for WhatsApp',
                    ];

                    $values['g_an_param_2'] = [
                        'key'=> 'event_label',
                        'value'=> '{title}, {url}',
                    ];
                } else {
                    $values['g_an_params'] = [
                        'g_an_param_1',
                        'g_an_param_2',
                        'g_an_param_3',
                    ];
                    $values['g_an_param_1'] = [
                        'key'=> 'number',
                        'value'=> '{number}',
                    ];
                    $values['g_an_param_2'] = [
                        'key'=> 'title',
                        'value'=> '{title}',
                    ];
                    $values['g_an_param_3'] = [
                        'key'=> 'url',
                        'value'=> '{url}',
                    ];
                }
            }
        }

        // pixel params
        if ( is_array($pixel_params) && isset($pixel_params[0]) ) {

            foreach ($pixel_params as $param ) {
                $param_options = ( isset($othersettings[$param]) ) ? $othersettings[$param] : [];
                $key = ( isset($param_options['key']) ) ? esc_attr($param_options['key']) : '';
                $value = ( isset($param_options['value']) ) ? esc_attr($param_options['value']) : '';
                
                if ( !empty($key) && !empty($value) ) {
                    $values['pixel_params'][] = $param;
                    $values[$param] = [
                        'key' => $key,
                        'value'=> $value,
                    ];
                }

            }
        } else {
            if ( !isset($othersettings['parms_saved']) ) {
                $values['pixel_params'] = [
                    'pixel_param_1',
                    'pixel_param_2',
                    'pixel_param_3',
                    'pixel_param_4',
                ];
                
                $values['pixel_param_1'] = [
                    'key'=> 'Category',
                    'value'=> 'Click to Chat for WhatsApp',
                ];
                
                $values['pixel_param_2'] = [
                    'key'=> 'ID',
                    'value'=> '{number}',
                ];
                
                $values['pixel_param_3'] = [
                    'key'=> 'Title',
                    'value'=> '{title}',
                ];
                
                $values['pixel_param_4'] = [
                    'key'=> 'URL',
                    'value'=> '{url}',
                ];
            }
        }

        $values = apply_filters( 'ht_ctc_fh_variables', $values );

        // data-attribute - data-values 
        // $ht_ctc_values = htmlspecialchars(json_encode($values), ENT_QUOTES, 'UTF-8');

        wp_localize_script( 'ht_ctc_app_js', 'ht_ctc_variables', $values );



        // 'no' == $display - hided from global settings (display can be no, only after checking page display is not show)
        // 'hide' == $page_display - hided at page level settings
        if ( 'no' == $display || 'hide' == $page_display ) {
            return;
        }


        // load style
        if ( is_file( $path ) ) {
            do_action('ht_ctc_ah_before_fixed_position');
            ?>  
            <div class="<?= $ht_ctc_chat['class_names'] ?>" id="<?= $ht_ctc_chat['id'] ?>"  
                style="<?= $display_css ?> <?= $default_position ?>" <?= $ht_ctc_os['attributes'] ?> <?= $on ?> >
                <?php
                // add greetings dialog
                do_action('ht_ctc_ah_in_fixed_position');
                ?>
                <div class="ht_ctc_style ht_ctc_chat_style">
                <?php
                // notification badge.
                if ( 'show' == $ht_ctc_chat['notification_badge'] ) {
                    ?>
                    <span class="ht_ctc_notification" style="display:none; padding:0px; margin:0px; position:relative; float:right; z-index:9999999;">
                        <span class="ht_ctc_badge" style="position: absolute; top: -11px; right: -11px; font-size:12px; font-weight:600; height:22px; width:22px; box-sizing:border-box; border-radius:50%; <?= $notification_border ?> background:<?= $notification_bg_color ?>; color:<?= $notification_text_color ?>; display:flex; justify-content:center; align-items:center;"><?= $ht_ctc_chat['notification_count'] ?></span>
                    </span>
                    <?php
                }
                // include style
                if ( isset( $options['select_styles_issue'] ) ) {
                    ?>
                    <div class="ht_ctc_desktop_chat"><?php include $path_d; ?></div>
                    <div class="ht_ctc_mobile_chat"><?php include $path_m; ?></div>
                    <?php
                } else {
                    include $path;
                }
                ?>
                </div>
            </div>
            <?php
            do_action('ht_ctc_ah_after_fixed_position');
            

            // if js var not available, dont depend on this element ht_ctc_chat_data
            // no_number may be needed.
            ?>
            <span class="ht_ctc_chat_data" 
                data-no_number="<?= $no_number ?>"
                data-settings="<?= $ht_ctc_settings ?>" 
            ></span>
            <?php

        }

        
    }

}

new HT_CTC_Chat();

// $ht_ctc_chat = new HT_CTC_Chat();

// // wp_footer / wp_head / get_footer
// $ht_ctc_chat_load_position = apply_filters( 'ht_ctc_chat_load_position', 'wp_footer' );

// add_action( "$ht_ctc_chat_load_position", array( $ht_ctc_chat, 'chat' ) );

endif; // END class_exists check