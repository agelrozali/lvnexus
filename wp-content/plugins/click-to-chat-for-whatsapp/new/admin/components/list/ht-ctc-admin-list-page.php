<?php
/**
 * list .. 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Admin_List_Page' ) ) :

class HT_CTC_Admin_List_Page {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function greetings_template() {

        /**
         * keys are like the file names (expect no)
         * Note: dont inclued 'pro' keyword in this list.
         */
        $values = array(
            'no' => __( '-- No Greetings Dialog --', 'click-to-chat-for-whatsapp'),
            'greetings-1' => __( 'Greetings-1 - Customizable Design', 'click-to-chat-for-whatsapp'),
            'greetings-2' => __( 'Greetings-2 - Content Specific', 'click-to-chat-for-whatsapp')
        );

        $values = apply_filters( 'ht_ctc_fh_greetings_templates', $values );

        return $values;
    }



}

endif; // END class_exists check