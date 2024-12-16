<?php

namespace UkrSolution\BarcodesDigital\POS;

use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class POS_Orders
{
    public function woocommerce_payment_complete($order_id)
    {
        global $wpdb;

        try {
            $order = new \WC_Order($order_id);

            $this->generateOrderImage($order);
            $this->generateItemsImages($order);
        } catch (\Throwable $th) {
        }
    }

    private function generateOrderImage($order)
    {
        $params = UserSettings::getJsonSectionOption('adminOrderPageParams', 'order', 1);

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 2;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        if ($width && $height) {
            do_shortcode("[barcode id=" . $order->get_id() . " shortcode=" . $sid . " width={$width}px height={$height}px _errors=return]");
        }
    }

    private function generateItemsImages($order)
    {
        $params = UserSettings::getJsonSectionOption('barcodesOnProductPageParams', 'product');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 1;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        $size =  " width={$width}px height={$height}px ";

        $items = $order->get_items();

        if ($items) {
            ob_start();
            foreach ($items as $item) {
                $pid = $item->get_product_id();
                $vid = $item->get_variation_id();
                $id = $vid ? $vid : $pid;

                if ($vid) {
                    do_shortcode("[barcode id=" . $vid . " shortcode=" . $sid . " class=digital-barcode-embedded" . $size . " _errors=return]");
                } else {
                    do_shortcode("[barcode id=" . $pid . " shortcode=" . $sid . " class=digital-barcode-embedded" . $size . " _errors=return]");
                }
            }
            ob_get_clean();
        }
    }
}
