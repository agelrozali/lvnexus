<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class Emails
{
    public function woocommerce_email_customer_details($order, $sent_to_admin, $plain_text)
    {

        if (is_checkout() && !empty(is_wc_endpoint_url('order-received'))) {
            return;
        }

        $params = UserSettings::getJsonSectionOption('orderBarcodeEmailParams', 'order');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 2;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;


        $size =  " width={$width}px height={$height}px ";

        echo '<div style="text-align:center;">';
        echo do_shortcode("[barcode id=" . $order->get_id() . " shortcode=" . $sid . "" . $size . " _action=email-order]");
        echo '</div><br/>';
    }

    public function woocommerce_order_item_meta_end($orderItemId, $item, $order)
    {
        global $wpdb;

        if (is_checkout() && !empty(is_wc_endpoint_url('order-received'))) {
            return;
        }

        $appointmentId = $wpdb->get_var(
            $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s LIMIT 1", "_appointment_order_item_id", $orderItemId)
        );

        $params = UserSettings::getJsonSectionOption('productBarcodeEmailParams');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 1;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        if ($sid) {
            $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = '%d' AND `type` = %s;", $sid, "product"));
        } else {
            $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `is_default` = 1 AND `type` = %s;", "product"));
        }

        $shortcodeId = ($shortcode) ? $shortcode->id : 1;

        $barcodeType = "";

        if ($shortcode->matching) {
            $matching = @json_decode($shortcode->matching);
            if ($matching && isset($matching->lineBarcode) && isset($matching->lineBarcode->type)) {
                $barcodeType = $matching->lineBarcode->type;
            }
        }

        $pid = $item->get_id(); 
        $vid = $item->get_id(); 
        $id = $vid ? $vid : $pid;

        $size = " width={$width}px height={$height}px ";

        if ($appointmentId && $barcodeType === "wcAppointment") {
            echo "<br/>" . do_shortcode("[barcode id=" . $appointmentId . " shortcode=" . $shortcodeId . "" . $size . " _action=email-product _parent=" . $order->get_id() . "]") . "<br/>";
        } else {
            echo "<br/>" . do_shortcode("[barcode id=" . $id . " shortcode=" . $shortcodeId . "" . $size . " _action=email-product _parent=" . $order->get_id() . "]") . "<br/>";
        }
    }
}
