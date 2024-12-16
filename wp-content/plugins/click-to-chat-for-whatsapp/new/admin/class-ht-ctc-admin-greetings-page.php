<?php
/**
 * Start page
 * 
 * Admin 
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Admin_Greetings' ) ) :

class HT_CTC_Admin_Greetings {

    public $values = '';

    public function __construct() {
        $this->hooks();
    }

    public function hooks() {

        add_action('admin_menu', [$this, 'menu'] );


        // only if options.php or this settings page..
        // @admin_perfomance - if this method is not working then we can add at add_settings_section .. 

        // check for options.php, _GET page = click-to-chat-greetings
        $get_url = ( isset($_GET) && isset($_GET['page']) && 'click-to-chat-greetings' == $_GET['page'] ) ? true : false;

        $options_page = false;
        // if request url have options.php .. (or if requesturl is not set.. or empty ) then $options_page = true
        if ( isset($_SERVER['REQUEST_URI']) ) {
            if ( !empty($_SERVER['REQUEST_URI']) ) {
                if ( false !== strpos( $_SERVER['REQUEST_URI'], 'options.php' ) ) {
                    // if options.php page
                    $options_page = true;
                }
            } else {
                $options_page = true;
            }
        } else {
            $options_page = true;
        }

        if ( true == $options_page || true == $get_url ) {
            

            /**
             * 
             * fallback_values: 
             *  created this because 
             *   options_sanitize runs mulitiple times if settings field not exist
             *   https://core.trac.wordpress.org/ticket/21989
             *   due to this htmlentities calling twice at options_sanitize and changes values..  
             *   but untill user save the first settings, fallback values have to display at settings field.. for easy to understand
             *   so here we added option with key fallback_values - as need to load fallback values to display in input fileds as user not saved any values..
             */
            $ht_ctc_greetings_options = get_option('ht_ctc_greetings_options');
            if ( isset($ht_ctc_greetings_options['greetings_template']) || isset($ht_ctc_greetings_options['fallback_values']) ) {
            } else {
                $values = array(
                    'fallback_values' => 'yes',
                );
                add_option('ht_ctc_greetings_options', $values);
            }



            add_action('admin_init', [$this, 'settings'] );
        }

    }


    /**
     * 
     * Settings
     * 
     * class names
     *  pr_: parent element class name
     *  table class names - pr_ht_ctc_greetings_options, 
     *  parent_class
     *      pr_greetings_template (for template select)
     *      ctc_greetings_settings 
     *      greetings-1
     * 
     */
    public function settings_values() {

        include_once HT_CTC_PLUGIN_DIR . 'new/admin/db/defaults/class-ht-ctc-defaults-greetings.php';
        $default_greetings = new HT_CTC_Defaults_Greetings();

        $greetings_fallback_values = $default_greetings->greetings;
        $g1_fallback_values = $default_greetings->g_1;
        $g2_fallback_values = $default_greetings->g_2;
        $g_settings_fallback_values = $default_greetings->g_settings;


        $start_values = [
            'main' => [
                // settings filed - add_settings_field
                'id' => 'ht_ctc_greetings',
                'title' => __( 'Add Greetings Dialog', 'click-to-chat-for-whatsapp'),
                'dbrow' => 'ht_ctc_greetings_options',
                'fallback_values' => $greetings_fallback_values,
                'class' => 'pr_ht_ctc_greetings_options',
                'inputs' => [
                    // with in single add_settings_field add single or multiple things..
                    [
                        'title' => 'Greetings Dialog',
                        'db' => 'greetings_template',
                        'template' => 'select',
                        'description' => "",
                        'list_cb' => 'greetings_template',
                        'parent_class' => 'pr_greetings_template',
                        'description' => "<a href='https://holithemes.com/plugins/click-to-chat/greetings/' target='_blank'>Greetings</a> | <a href='https://holithemes.com/plugins/click-to-chat/greetings-1/' target='_blank'>Greetings-1</a> | <a href='https://holithemes.com/plugins/click-to-chat/greetings-2/' target='_blank'>Greetings-2</a> | <a href='https://holithemes.com/plugins/click-to-chat/greetings-form/' target='_blank'>Form Filling</a> | <a href='https://holithemes.com/plugins/click-to-chat/multi-agent/' target='_blank'>Multi Agent</a>",
                    ],
                    [
                        'template' => 'collapsible_start',
                        'collapsible' => 'no',
                        'ul_class' => 'g_content_collapsible ctc_init_display_none',
                        'title' => "Content: Header, Main, Bottom, Call to Action",
                    ],
                    [
                        'db' => 'empty',
                        'template' => 'empty',
                    ],
                    'header_content' => [
                        'title' => __( 'Header Content', 'click-to-chat-for-whatsapp'),
                        'db' => 'header_content',
                        'template' => 'editor',
                        'label' => 'Header Content',
                        'description' => 'Customize the font to improve the appearance (font size, color, etc.)',
                        'link_url' => '',
                        'link_title' => 'more info',
                        'parent_style' => "margin-bottom: 30px;",
                        'parent_class' => 'pr_header_content ctc_greetings_settings ctc_g_1 ctc_wp_editor',
                    ],
                    'g_header_image' => [
                        'template' => 'g_header_image',
                        'parent_class' => 'pr_g_header_image ctc_greetings_settings ctc_g_1',
                    ],
                    'main_content' => [
                        'title' => __( 'Main Content', 'click-to-chat-for-whatsapp'),
                        'db' => 'main_content',
                        'template' => 'editor',
                        'label' => 'Main Content',
                        'description' => "Variables: {site}, {title}, {url}",
                        'parent_style' => "margin-bottom: 40px;",
                        'parent_class' => 'pr_main_content ctc_greetings_settings ctc_g_1 ctc_g_2 ctc_wp_editor',
                    ],
                    'bottom_content' => [
                        'title' => __( 'Bottom Content', 'click-to-chat-for-whatsapp'),
                        'db' => 'bottom_content',
                        'template' => 'editor',
                        'label' => 'Bottom Content',
                        'description' => "&#128994; <a href='https://holithemes.com/plugins/click-to-chat/symbols/' target='_blank'>Symbols</a>",
                        'parent_style' => "margin-bottom: 30px;",
                        'parent_class' => 'pr_bottom_content ctc_greetings_settings ctc_g_1 ctc_g_2 ctc_wp_editor',
                    ],
                    [
                        'title' => __( 'Call to Action', 'click-to-chat-for-whatsapp'),
                        'db' => 'call_to_action',
                        'template' => 'text',
                        'label' => 'Call to Action',
                        'description' => __( 'Call to Action (Button/Link Text)', 'click-to-chat-for-whatsapp'),
                        'parent_class' => 'pr_call_to_action ctc_greetings_settings ctc_g_1 ctc_g_2',
                    ],
                    [
                        'template' => 'collapsible_end',
                    ],
                ]
            ],
            'greetings_1' => [
                'id' => 'ht_ctc_greetings_1',
                'title' => __( 'Greetings Dialog - 1', 'click-to-chat-for-whatsapp'),
                'dbrow' => 'ht_ctc_greetings_1',
                'fallback_values' => $g1_fallback_values,
                'class' => 'pr_ht_ctc_greetings_1 ctc_greetings_settings',
                'inputs' => [
                    [
                        'template' => 'collapsible_start',
                        'title' => __( 'Greetings-1 - Customizable Design', 'click-to-chat-for-whatsapp'),
                    ],
                    [
                        'db' => 'empty',
                        'template' => 'empty',
                    ],
                    [
                        'title' => __( 'Header - Background Color', 'click-to-chat-for-whatsapp'),
                        'db' => 'header_bg_color',
                        'template' => 'color',
                        'default_color' => '#075e54',
                        'description' => 'Header - Background Color',
                        'parent_class' => 'pr_g1_header_bg_color',
                    ],
                    [
                        'title' => __( 'Main Content - Background Color', 'click-to-chat-for-whatsapp'),
                        'db' => 'main_bg_color',
                        'template' => 'color',
                        'default_color' => '#ece5dd',
                        'description' => 'Main Content - Background Color',
                        'parent_class' => 'pr_g1_main_bg_color',
                    ],
                    [
                        'title' => __( 'Message Box - Background Color', 'click-to-chat-for-whatsapp'),
                        'db' => 'message_box_bg_color',
                        'template' => 'color',
                        'default_color' => '#dcf8c6',
                        'description' => 'Main Content as a Message Box with Background Color',
                        'parent_class' => 'pr_g1_message_box_bg_color',
                    ],
                    [
                        'title' => 'Background image, Call to Action - button type',
                        'template' => 'element_details_start',
                    ],
                    [
                        'title' => __( 'Background image', 'click-to-chat-for-whatsapp'),
                        'db' => 'main_bg_image',
                        'template' => 'checkbox',
                        'description' => 'Add WhatsApp like Background image to main content. ',
                        'parent_class' => 'pr_main_bg_image',
                    ],
                    [
                        'title' => __( 'Call to Action - button type', 'click-to-chat-for-whatsapp'),
                        'db' => 'cta_style',
                        'template' => 'select',
                        'description' => "Call to Action - button type ('Click to Chat' -> Customize)",
                        'list' => [
                            '1' => __( 'Themes Button (style-1)', 'click-to-chat-for-whatsapp'),
                            '7_1' => __( 'Button with WhatsApp Icon (style-7 Extend)', 'click-to-chat-for-whatsapp'),
                        ],
                        'parent_class' => 'pr_g1_cta_style',
                    ],
                    [
                        'template' => 'element_details_end',
                        'description' => "<a href='https://holithemes.com/plugins/click-to-chat/greetings-1/' target='_blank'>Greetings-1</a>",
                    ],
                    [
                        'template' => 'collapsible_end',
                        'description' => "<a href='https://holithemes.com/plugins/click-to-chat/greetings-1/' target='_blank'>Greetings-1</a>",
                    ],
                ]
            ],
            'greetings_2' => [
                'id' => 'ht_ctc_greetings_2',
                'title' => __( 'Greetings Dialog - 2', 'click-to-chat-for-whatsapp'),
                'dbrow' => 'ht_ctc_greetings_2',
                'fallback_values' => $g2_fallback_values,
                'class' => 'pr_ht_ctc_greetings_2 ctc_greetings_settings',
                'inputs' => [
                    [
                        'template' => 'collapsible_start',
                        'title' => __( 'Greetings-2 - Content Specific', 'click-to-chat-for-whatsapp'),
                    ],
                    [
                        'db' => 'empty',
                        'template' => 'empty',
                    ],
                    [
                        'title' => 'Background Color',
                        'db' => 'bg_color',
                        'template' => 'color',
                        'default_color' => '#ffffff',
                        'description' => 'Greetings Dialog Background Color',
                        'parent_class' => 'pr_g2_bg_color greetings-2',
                    ],
                    [
                        'template' => 'collapsible_end',
                        'description' => "<a href='https://holithemes.com/plugins/click-to-chat/greetings-2/' target='_blank'>Greetings-2</a> <br> Customize 'Call to Action' button from 'Click to Chat' -> Customize - Style-1 ",
                    ],
                ]
            ],
            'greetings_settings' => [
                'id' => 'ht_ctc_greetings_settings',
                'title' => 'Additional Settings',
                'dbrow' => 'ht_ctc_greetings_settings',
                'fallback_values' => $g_settings_fallback_values,
                'class' => 'pr_ht_ctc_greetings_settings ctc_greetings_settings',
                'inputs' => [
                    [
                        'db' => 'empty',
                        'template' => 'empty',
                    ],
                    [
                        'db' => 'count',
                        'template' => 'count',
                    ],
                    [
                        'template' => 'collapsible_start',
                        'collapsible' => 'no',
                        'ul_class' => 'ctc_g_opt_in',
                        'title' => __( 'Opt-in', 'click-to-chat-for-whatsapp'),
                    ],
                    [
                        'title' => __( 'Opt-in', 'click-to-chat-for-whatsapp'),
                        'db' => 'is_opt_in',
                        'template' => 'checkbox',
                        'description' => __( "Get website visitors consent before initiating the chat.", 'click-to-chat-for-whatsapp') . " <br> " . __( "Once the website visitor opt-in, the consent prompt will not reappear", 'click-to-chat-for-whatsapp') . " - <a href='https://holithemes.com/plugins/click-to-chat/opt-in/' target='_blank'>Opt-in</a>",
                        'parent_class' => 'pr_is_opt_in',
                    ],
                    [
                        'title' => '',
                        'db' => 'opt_in',
                        'template' => 'editor_lite',
                        'label' => 'Opt-in',
                        'description' => '',
                        'parent_style' => "margin-bottom: 20px;",
                        'parent_class' => 'pr_opt_in ctc_greetings_settings ctc_wp_editor',
                    ],
                    [
                        'template' => 'collapsible_end',
                    ],
                    [
                        'title' => __( 'Display', 'click-to-chat-for-whatsapp'),
                        'db' => 'g_device',
                        'template' => 'select',
                        'description' => __( 'Display Greetings Dialog based on device', 'click-to-chat-for-whatsapp'),
                        'list' => [
                            'all' => __( 'Desktop and Mobile', 'click-to-chat-for-whatsapp'),
                            'desktop' => __( 'Desktop Only', 'click-to-chat-for-whatsapp'),
                            'mobile' => __( 'Mobile Only', 'click-to-chat-for-whatsapp')
                        ],
                        'parent_class' => 'pr_g_device',
                    ],
                    [
                        'title' => __( 'Greetings dialog Size', 'click-to-chat-for-whatsapp'),
                        'db' => 'g_size',
                        'template' => 'select',
                        'list' => [
                            's' => 'Small',
                            'm' => 'Desktop: Medium, Mobile: Full width',
                            'l' => 'Desktop: Large, Mobile: Full width',
                        ]
                    ],
                    [
                        'title' => __( 'Initial stage', 'click-to-chat-for-whatsapp'),
                        'db' => 'g_init',
                        'template' => 'select',
                        'description' => "Preset: automatically opens on desktops and stays closed on mobile devices.. <br> Open: Initially visible. If the user closes the dialog, it stays closed until the user reopens it or triggers greeting actions <br> Close: hidden until the user initiates chat or triggers greeting actions - <a target='_blank' href='https://holithemes.com/plugins/click-to-chat/greetings-initial-stage'>more info</a>",
                        'list' => [
                            'default' => 'Preset',
                            'open' => 'Open',
                            'close' => 'Close',
                        ],
                        'parent_id' => 'g_init',
                        'parent_class' => 'pr_g_init',
                    ],
                ]
            ]

        ];

        $start_values = apply_filters( 'ht_ctc_fh_greetings_setting_values', $start_values );

        return $start_values;
    }


    function settings_cb($s) {

        $dbrow = $s['dbrow'];

        $fallback_values = '';
        if (isset($s['fallback_values'])) {
            $fallback_values = $s['fallback_values'];
        }

        $options = get_option($dbrow, $fallback_values);

        if (isset($options['fallback_values'])) {
            $options = $fallback_values;
        }

        $inputs = $s['inputs'];

        foreach ($inputs as $input) {

            if (isset($input['template'])) {

                $db_key = '';
                $db_value = '';
                if (isset($input['db'])) {
                    $db_key = $input['db'];
                    $db_value = ( isset( $options[$db_key]) ) ? esc_attr( $options[$db_key] ) : '';
                }

                $template = $input['template'];

                $components = (isset($input['path'])) ? $input['path'] : HT_CTC_PLUGIN_DIR ."new/admin/components";

                $path = "$components/$template.php";

                if ( is_file( $path ) ) {
                    include $path;
                }

            }


        }


    }


    public function menu() {

        add_submenu_page(
            'click-to-chat',
            'Greetings',
            'Greetings',
            'manage_options',
            'click-to-chat-greetings',
            array( $this, 'settings_page' )
        );
    }

    public function settings_page() {

        if ( ! current_user_can('manage_options') ) {
            return;
        }

        ?>

        <div class="wrap ctc-admin-greetings-page">

            <?php settings_errors(); ?>

            <!-- full row -->
            <div class="row">

                <div class="col s12 m12 xl8 options">
                    <form action="options.php" method="post" class="">
                        <?php settings_fields( 'ht_ctc_greetings_page_settings_fields' ); ?>
                        <?php do_settings_sections( 'ht_ctc_greetings_page_settings_section' ) ?>
                        <?php submit_button() ?>
                    </form>
                </div>

                <!-- sidebar content -->
                <div class="col s12 m8 l5 xl4 ht-ctc-admin-sidebar ht-ctc-greetings-admin-sidebar sticky-sidebar">
                    <?php
                    if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
                    ?>

                        <div class="sidebar-content">
                            <div class="col s12 m8 l12 xl12">
                                <div class="row">
                                    <ul class="collapsible popout ht_ctc_sidebar_greetings">
                                        <li class="active">
                                            <div class="collapsible-header"><?php _e( 'PRO', 'click-to-chat-for-whatsapp' ); ?></div>	
                                            <div class="collapsible-body">
                                                <p class="description">📝 Greetings - Form filling</p>
                                                <p class="description">👥 Greetings - Multi-Agent</p>
                                                <p class="description">&emsp;⏰ with different time ranges</p>
                                                <p class="description">&emsp;👤 Hide or display agent with next available time</p>
                                                <p class="description">🎯 Actions: Time, Scroll, Viewport</p>
                                                <p class="description">⚙️ Greetings page level settings</p>
                                                <p class="description" style="text-align: center; position:sticky; bottom:2px; margin-top:20px;"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pricing/" class="waves-effect waves-light btn" style="width: 100%;">PRO Version</a></p>
                                            </div>	
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    <?php
                    }
                    ?>
                </div>

            </div>

            <!-- new row - After settings page  -->
            <div class="row">
            </div>

        </div>

        <?php

    }


    public function settings() {


        // @uses for register_setting
        $greetings_list = [
            'ht_ctc_greetings_options',
            'ht_ctc_greetings_1',
            'ht_ctc_greetings_2',
            'ht_ctc_greetings_settings',
        ];

        $greetings_list = apply_filters( 'ht_ctc_fh_greetings_register', $greetings_list );

        // register_setting
        foreach ($greetings_list as $g) {

            register_setting( 
                'ht_ctc_greetings_page_settings_fields', 
                $g, 
                [$this, 'options_sanitize']
            );

        }

        // @admin_perfomance - if the above method is not working then add here..

        add_settings_section( 'ht_ctc_greetings_page_settings_sections_add', '', array( $this, 'ht_ctc_greetings_section_cb' ), 'ht_ctc_greetings_page_settings_section' );


        $settings = $this->settings_values();
        foreach ($settings as $s) {
            add_settings_field( 
                $s['id'], 
                $s['title'], 
                array( $this, 'settings_cb' ), 
                'ht_ctc_greetings_page_settings_section', 
                'ht_ctc_greetings_page_settings_sections_add',
                $s
            );
        }

        add_settings_field( 'ctc_g_content', '', array( $this, 'ctc_g_content_cb' ), 'ht_ctc_greetings_page_settings_section', 'ht_ctc_greetings_page_settings_sections_add' );


    }


    public function ht_ctc_greetings_section_cb() {
        ?>
        <h1 id="greetings_settings">Greetings Dialog</h1>
        <?php
        do_action('ht_ctc_ah_admin' );

    }

    public function ctc_g_content_cb() {

        ?>
        <div class="ctc_greetings_settings ctc_greetings_notes">
            <p class="description">
                <a href='https://holithemes.com/plugins/click-to-chat/greetings/' target='_blank'>Greetings</a>: <a href='https://holithemes.com/plugins/click-to-chat/greetings-1/' target='_blank'>Greetings-1</a>, <a href='https://holithemes.com/plugins/click-to-chat/greetings-2/' target='_blank'>Greetings-2</a>, <a href='https://holithemes.com/plugins/click-to-chat/greetings-form/' target='_blank'>Form Filling</a>, <a href='https://holithemes.com/plugins/click-to-chat/multi-agent/' target='_blank'>Multi Agent</a>
            </p>
            <p class="description">
                <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-actions/">Greetings Actions</a>: Displays Greetings based on <br>
                &emsp;<strong>Click</strong>: Clicked on any element with Class name: 'ctc_greetings' <br>
                &emsp;<strong>Viewport</strong>: an element is in/reached viewport(25% margin) with Class name: 'ctc_greetings_now' [PRO]
            </p>
            
            <?php
            if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
                ?>
                <br>
                <p class="description"><strong>PRO</strong>:</p>
                <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-actions/">Actions</a>: Time, Scroll, Click, Viewport</p>
                <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-form/">Greetings Form</a>: Form filling before initiating the chat</p>
                <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/multi-agent/">Multi Agent</a>: Display Multiple agent with different time ranges</p>
                <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-page-level-settings/">Greetings Page level settings</a>: Change Greetings content for any post</p>
                <br>
                <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pricing/">PRO Version</a></p>
                <?php
            }
            ?>
            
        </div>
        <?php

    }





    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function options_sanitize( $input ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'not allowed to modify - please contact admin ' );
        }

        // formatting api - emoji ..
        include_once HT_CTC_PLUGIN_DIR .'new/admin/admin_commons/ht-ctc-admin-formatting.php';

        $textarea = [
            'pre_filled',
            'woo_pre_filled'
        ];

        $editor = [
            'header_content',
            'main_content',
            'bottom_content',
            'opt_in'
        ];
        $editor = apply_filters( 'ht_ctc_fh_greetings_setting_editor_values', $editor );


        $new_input = array();

        foreach ($input as $key => $value) {
            if( isset( $input[$key] ) ) {

                if ( is_array( $input[$key] ) ) {
                    if ( function_exists('sanitize_textarea_field') ) {
                        $new_input[$key] = map_deep( $input[$key], 'sanitize_textarea_field' );
                    } else {
                        $new_input[$key] = map_deep( $input[$key], 'sanitize_text_field' );
                    }
                } else {
                    if ( in_array( $key, $editor ) ) {
                        // editor
                        if ( !empty( $input[$key]) && '' !== $input[$key] && function_exists('ht_ctc_wp_sanitize_text_editor') ) {
                            $new_input[$key] = ht_ctc_wp_sanitize_text_editor( $input[$key] );
                        } else {
                            // save field even if the value is empty..
                            $new_input[$key] = sanitize_text_field( $input[$key] );
                        }
                    } else if ( in_array( $key, $textarea ) ) {
                        // textarea
                        if ( function_exists('ht_ctc_wp_encode_emoji') ) {
                            $input[$key] = ht_ctc_wp_encode_emoji( $input[$key] );
                        }
                        if ( function_exists('sanitize_textarea_field') ) {
                            $new_input[$key] = sanitize_textarea_field( $input[$key] );
                        } else {
                            $new_input[$key] = sanitize_text_field( $input[$key] );
                        }
                    } else {
                        $new_input[$key] = sanitize_text_field( $input[$key] );
                    }
                }
            }
        }


        $local = [
            'header_content',
            'main_content',
            'bottom_content',
            'call_to_action',
            'opt_in'
        ];

        $local = apply_filters( 'ht_ctc_fh_greetings_setting_local_values', $local );

        // l10n
        do_action('ht_ctc_ah_admin_localization_greetings_page', $new_input );

        foreach ($new_input as $key => $value) {
            if ( in_array( $key, $local ) ) {
                do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', "greetings_$key", $new_input[$key] );
            }
        }


        return $new_input;
    }


}


if ( current_user_can( 'manage_options' ) ) {
    new HT_CTC_Admin_Greetings();
}


endif; // END class_exists check