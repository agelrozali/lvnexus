<?php
/**
 * 
 * Admin demo - main page
 * 
 * _ad_ - admin demo
 * _mp_ - main page
 * _os_ - other settings
 * 
 * @since 3.30
 *  - s1. front end looks like theme button
 *  - for some styles the default view may need to change. like hover effects, .. 
 * 
 * class names added to settings pages for demo purpose:
 * .ctc_no_demo - to display no demo notification
 * .ctc_demo_style
 * .ctc_ad_main_page_on_change_style
 * .ctc_ad_main_page_on_change_input
 * .ctc_ad_main_page_on_change_input_update_var
 * .ctc_demo_position - positions: bottom_top, right_left, side_1_value, side_2_value
 * .ctc_an_demo_btn
 * .ctc_ee_demo_btn
 * .ctc_demo_style
 * .ctc_oninput
 *      attributes - data-update-type
 *                 - data-update-type-2
 *                 - data-update-selector
 * 
 * 
 * class names at demo:
 * ctc_demo_style ctc_demo_style_${style}
 * ctc_demo_load
 * 
 * 
 * direct class names used for demo:
 * .ht-ctc-admin-sidebar .collapsible
 * 
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Admin_Demo' ) ) :

class HT_CTC_Admin_Demo {

    // _get
    public $get_page = '';

    // by default load demo
    public $load_demo = 'yes';

    public function __construct() {
        $this->hooks();
    }

    public function hooks() {

        if ( isset($_GET) && isset($_GET['page']) ) {
            $this->get_page = $_GET['page'];
        } else {
            return;
        }

        /**
         * check if admin demo is active
         * retun if not active
         * 
         * to deactivate from user side:
         *  -> if _GET have &demo=deactive
         *  set ht_ctc_admin_demo_active to yes
         * 
         * to active from user side:
         *  -> if _GET have &demo=active 
         *  set ht_ctc_admin_demo_active to no
         */
        if ( 'click-to-chat' == $this->get_page  || 'click-to-chat-other-settings' == $this->get_page || 'click-to-chat-customize-styles' == $this->get_page) {
            
            // check if admin demo is active.. (added inside to run only in ctc admin pages..)
            $demo_active = get_option( 'ht_ctc_admin_demo_active');

            // check if demo is activating or deactivating..
            if ( isset($_GET['demo']) && 'active' == $_GET['demo'] ) {
                $this->load_demo = 'yes';
                // add option to db
                update_option( 'ht_ctc_admin_demo_active', 'yes' );
            } else if ( isset($_GET['demo']) && 'deactive' == $_GET['demo'] ) {
                $this->load_demo = 'no';
                // add option to db
                update_option( 'ht_ctc_admin_demo_active', 'no' );
            } else {
                // not activating or deactivating.. check if admin demo already deactived...
                if ( 'no' == $demo_active ) {
                    $this->load_demo = 'no';
                }
            }

        }


        // return if load_demo is no
        if ( 'no' == $this->load_demo ) {
            return;
        }

        // below this only run if admin demo is active.. (i.e. user activated demo from user side and only in click to chat admin pages..)


        if ( 'click-to-chat' == $this->get_page || 'click-to-chat-other-settings' == $this->get_page || 'click-to-chat-customize-styles' == $this->get_page ) {
            
            // load styles (widgets)
            add_action('admin_footer', [$this, 'load_styles']);
            
            // enqueue scripts
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
            
            // enqueue styles at bottom of the page
            add_action('admin_footer', [$this, 'load_css_bottom']);

            // // other settings page
            // if ( 'click-to-chat-other-settings' == $this->get_page ) {
            // }

            // // customize styles page
            // if ( 'click-to-chat-customize-styles' == $this->get_page ) {
            // }

        }

    }

    // enqueue scripts
    public function enqueue_scripts() {

        $os = get_option('ht_ctc_othersettings');

        $js = 'admin-demo.js';
        
        
        if ( defined('HT_CTC_DEBUG_MODE') ) {
            $js = 'dev/admin-demo.dev.js';
        }
        
        
        $args = true;
        
        global $wp_version;
        
        // if wp version is not null and is greater than 6.3
        if ( !$wp_version && version_compare( $wp_version, '6.3', '>=' ) ) {
            $args = array(
                'in_footer' => true,
                'strategy' => 'defer',
            );
        }

        wp_enqueue_script( 'ht-ctc-admin-demo-js', plugins_url( "new/admin/admin_demo/$js", HT_CTC_PLUGIN_FILE ), ['jquery'], HT_CTC_VERSION, $args );
        
        $this->admin_demo_var();
    }
    
    // enqueue styles at bottom of the page
    function load_css_bottom() {

        $os = get_option('ht_ctc_othersettings');

        $css = 'admin-demo.css';
        $animation_css = 'admin-demo-animations.css';

        if ( defined('HT_CTC_DEBUG_MODE') ) {
            $css = 'dev/admin-demo.dev.css';
            $animation_css = 'dev/admin-demo-animations.dev.css';
        }
        
        wp_enqueue_style( 'ht-ctc-admin-demo-css', plugins_url( "new/admin/admin_demo/$css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );

        // other settings page
        if ( 'click-to-chat-other-settings' == $this->get_page ) {
            wp_enqueue_style( 'ht-ctc-admin-demo-animations-css', plugins_url( "new/admin/admin_demo/$animation_css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );
        }
    }

    function admin_demo_var() {

        $options = get_option( 'ht_ctc_chat_options' );

        $number = isset($options['number']) ? esc_attr($options['number']) : '';

        if ( class_exists( 'HT_CTC_Formatting' ) && method_exists( 'HT_CTC_Formatting', 'wa_number' ) ) {
            $number = HT_CTC_Formatting::wa_number( $number );
        }
        
        $pre_filled = isset($options['pre_filled']) ? esc_attr($options['pre_filled']) : '';

        $url_target_d = isset($options['url_target_d']) ? esc_attr($options['url_target_d']) : '_blank';

        $url_structure_m = isset($options['url_structure_m']) ? esc_attr($options['url_structure_m']) : '';
        $url_structure_d = isset($options['url_structure_d']) ? esc_attr($options['url_structure_d']) : '';
        
        $site = HT_CTC_BLOG_NAME;

        $m1 = __( 'No Demo for click: WhatsApp Number is empty', 'click-to-chat-for-whatsapp');
        $m2 = __( 'No Demo for click: URL Target: same tab', 'click-to-chat-for-whatsapp');


        $demo_var = [
            'number' => $number,
            'pre_filled' => $pre_filled,
            'url_target_d' => $url_target_d,
            'url_structure_m' => $url_structure_m,
            'url_structure_d' => $url_structure_d,
            'site' => $site,
            'm1' => $m1,
            'm2' => $m2,
        ];

        wp_localize_script( 'ht-ctc-admin-demo-js', 'ht_ctc_admin_demo_var', $demo_var );
    }


    /**
     * load styles..
     * 
     * main page: load all styles
     * other settings: load only desktop selected style
     * 
     */
    public function load_styles() {

        $options = get_option( 'ht_ctc_chat_options' );
        $othersettings = get_option( 'ht_ctc_othersettings' );

        $styles = array(
            '1', '2', '3', '3_1', '4', '5', '6', '7', '7_1', '8', '99'
        );

        // ctc, ctc customize styles - load all styles. And in ctc other settings load only desktop selected style.
        if ( 'click-to-chat-other-settings' == $this->get_page ) {
            $style_desktop = ( isset( $options['style_desktop']) ) ? esc_attr( $options['style_desktop'] ) : '4';
            $styles = array(
                $style_desktop
            );
        }

        // in styles
        $call_to_action = (isset($options['call_to_action'])) ? __(esc_attr($options['call_to_action']) , 'click-to-chat-for-whatsapp' ) : '';
        if ( '' == $call_to_action ) {
            $call_to_action = "WhatsApp us";
        }
        
        $type = 'chat';
        $is_mobile = '';
        $side_2 = 'right';

        /**
         * .ctc_demo_load_styles parent.. 
         *      greetings.. 
         *      styles.. 
         */
        ?>
        <div class="ctc_demo_load" style="position:fixed; bottom:50px; right:50px; z-index:99999999;">
        <?php
        // // greetings (to load all greetings)
        // include_once HT_CTC_PLUGIN_DIR .'new/tools/demo/demo-greetings.php';

        $notification_count = (isset($othersettings['notification_count'])) ? esc_attr($othersettings['notification_count']) : '1';
        $cs_link = admin_url( 'admin.php?page=click-to-chat-customize-styles' );
        $os_link = admin_url( 'admin.php?page=click-to-chat-other-settings' );

        // load all styles
        foreach ($styles as $style) {
            $class = "ctc_demo_style ctc_demo_style_$style ht_ctc_animation ht_ctc_entry_animation";
            ?>
            <div class="<?= $class ?>" style="display: none; cursor: pointer;">
            <?php
            if ( 'click-to-chat-other-settings' == $this->get_page ) {
                ?>
                <span class="ctc_ad_notification" style="display:none; padding:0px; margin:0px; position:relative; float:right; z-index:9999999;">
                    <span class="ctc_ad_badge" style="position: absolute; top: -11px; right: -11px; font-size:12px; font-weight:600; height:22px; width:22px; box-sizing:border-box; border-radius:50%;border:2px solid #ffffff; background:#ff4c4c; color:#ffffff; display:flex; justify-content:center; align-items:center;"><?= $notification_count ?></span>
                </span>
                <?php
            }
            // no need to santize_file_name. its not user input
            $style = sanitize_file_name( $style );
            $path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles/style-' . $style. '.php';
            include $path;
            ?>
            </div>
            <?php
        }
        ?>
        </div>

        <?php
        /**
         * ctc_menu_at_demo
         *  .ctc_ad_links - displays customize styles and other settings links
         *  .ctc_no_demo_notice - displays no demo notice for some features - e..g. customize styles . s1 add icon, ...
         *  .ctc_demo_messages - displays demo messages - e.g. for no demo for click, same tab., ..
         */
        ?>
        <div class="ctc_menu_at_demo" style="position:fixed; bottom:4px; right:4px; z-index:99999999;">
            <p class="description ctc_ad_links ctc_init_display_none">
                <a target="_blank" href="<?= $cs_link ?>" class="ctc_cs_link">Customize Styles</a> | <a target="_blank" href="<?= $os_link ?>">Animations, Notification badge</a>
            </p>
            <a href="https://holithemes.com/plugins/click-to-chat/admin-live-preview-messages/#no-live-preview/" target="_blank" class="description ctc_no_demo_notice ctc_init_display_none">No live demo for this feature</a>
            <a href="https://holithemes.com/plugins/click-to-chat/admin-live-preview-messages/" target="_blank" class="description ctc_demo_messages ctc_init_display_none"></a>
        </div>
        <?php

    }




}

new HT_CTC_Admin_Demo();

endif; // END class_exists check