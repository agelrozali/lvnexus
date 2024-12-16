<?php
/**
 * formating
 * add static methods to make things easy
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CTC_Formatting' ) ) :

class HT_CTC_Formatting {


    public static function wa_number( $number ) {

        // remove all expect digits
        $number = preg_replace('/\D/', '', $number );
        // remove initial 0s
        $number = ltrim( $number, '0' );

        // https://faq.whatsapp.com/537057536884131/

        // All phone numbers in Argentina (country code "54") should have a "9" between the country code and area code.
        $number = preg_replace('/^54(0|1|2|3|4|5|6|7|8)/', '549$1', $number );
        // The prefix "15" must be removed so the final number will have 13 digits total (not needed)

        // Mexico (country code "52") need to have "1" after "+52"
        $number = preg_replace('/^52(0|2|3|4|5|6|7|8|9)/', '521$1', $number );

        return $number;
    }

    /**
     * is page builder editor..
     *  elementor, divi, ..
     */
    public static function is_page_builder_editor() {

        // $is_editor = false;
        $is_editor = 'n';

        if (isset($_GET)) {
            if ( isset($_GET['elementor-preview']) || isset($_GET['et_fb']) || isset($_GET['is-editor-iframe']) || isset($_GET['fl_builder']) || isset($_GET['siteorigin_panels_live_editor']) || isset($_GET['pagelayer-iframe']) || isset($_GET['vcv-editable']) ) {
                // $is_editor = true;
                $is_editor = 'y';
            }
        }

        $is_editor = apply_filters( 'ht_ctc_fh_is_page_builder_editor', $is_editor );

        return $is_editor;
    }


}
endif; // END class_exists check
