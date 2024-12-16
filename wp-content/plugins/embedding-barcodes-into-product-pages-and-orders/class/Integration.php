<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class Integration
{
    public function init()
    {
        $this->wc_pdf_ips();
    }

    private function wc_pdf_ips()
    {
        $wc_pdf_ips = UserSettings::getoption('wc_pdf_ips_status', false);

        if (!$wc_pdf_ips) return;

        $params = UserSettings::getJsonSectionOption('wc_pdf_ips_order_hook_params', 'order');

        $status = isset($params['status']) ? $params['status'] : null;
        $sid = isset($params['shortcode']) ? $params['shortcode'] : null;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;
        $position = isset($params['position']) ? $params['position'] : null;


        if ($status && $sid && $position) {
            add_action($position, function ($type, $order) use ($width, $height, $sid) {
                $this->runShortcode($order->get_id(), "order", $sid, $width, $height);
            }, 10, 2);
        }

        $params = UserSettings::getJsonSectionOption('wc_pdf_ips_product_hook_params', 'order');

        $status = isset($params['status']) ? $params['status'] : null;
        $sid = isset($params['shortcode']) ? $params['shortcode'] : null;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;
        $position = isset($params['position']) ? $params['position'] : null;

        if ($status && $sid) {
            add_action("wpo_wcpdf_after_item_meta", function ($type, $item, $order) use ($width, $height, $sid) {
                if (isset($item["item_id"]) && $item["item_id"]) {
                    $this->runShortcode($item["item_id"], "product", $sid, $width, $height, $order->get_id());
                } else if ($item["variation_id"]) {
                    $this->runShortcode($item["variation_id"], "variation", $sid, $width, $height);
                } else if ($item["product_id"]) {
                    $this->runShortcode($item["product_id"], "product", $sid, $width, $height);
                }
            }, 10, 3);
        }
    }

    private function runShortcode($id, $type, $shortcodeId, $width, $height, $orderId = null)
    {
        if (!$id || !$shortcodeId) {
            return;
        }

        echo "<div>";
        if ($orderId) {
            echo do_shortcode("[barcode id=" . $id . " shortcode=" . $shortcodeId . " width=" . $width . " height=" . $height . " _oid=" . $orderId . "]");
        } else {
            echo do_shortcode("[barcode id=" . $id . " shortcode=" . $shortcodeId . " width=" . $width . " height=" . $height . "]");
        }
        echo "</div>";
    }
}
