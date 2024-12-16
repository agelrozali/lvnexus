<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use UkrSolution\BarcodesDigital\Helpers\Variables;

class Orders
{
    public function addImportButton()
    {
        global $post_type;

        if ($post_type === 'shop_order' && is_admin()) {

            include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/orders/import-orders-button.php';
        }
    }

    public function addOrderItemsImport($orderItemId)
    {
        global $post_type;

        try {
            if ($post_type === 'shop_order' && is_admin()) {
                $orderItem = new \WC_Order_Item_Product($orderItemId);

                $itemId = $orderItem->get_id();

                include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/orders/import-order-item-checkbox.php';
            }
        } catch (\Throwable $th) {
        }
    }

    public function addOrderItemsBarcode($orderItemId, $item)
    {
        global $wpdb;

        global $post_type;

        try {
            $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

            $params = UserSettings::getJsonSectionOption('adminOrderItemPageParams', 'product', 1);

            if (!$params || !isset($params['width']) || !$params['width']) return;

            $status = isset($params['status']) ? $params['status'] : null;
            $sid = isset($params['shortcode']) ? $params['shortcode'] : null;
            $width = isset($params['width']) ? $params['width'] : null;
            $height = isset($params['height']) ? $params['height'] : null;


            if ($status && $post_type === 'shop_order' && is_admin()) {
                $appointmentId = $wpdb->get_var(
                    $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s LIMIT 1", "_appointment_order_item_id", $orderItemId)
                );

                if ($sid) {
                    $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = '%d' AND `type` = %s;", $sid, "product"));
                } else {
                    $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `is_default` = 1 AND `type` = %s;", "product"));
                }

                $barcodeType = "";

                if ($data->matching) {
                    $matching = @json_decode($data->matching);
                    if ($matching && isset($matching->lineBarcode) && isset($matching->lineBarcode->type)) {
                        $barcodeType = $matching->lineBarcode->type;
                    }
                }

                $shortcode = "";

                if ($appointmentId && $barcodeType === "wcAppointment") {
                    $shortcode = str_replace("id=XXXX", "id={$appointmentId} width={$width}", $data->shortcode);
                } else if (!$appointmentId && $barcodeType !== "wcAppointment") {
                    $size = " width={$width}px height={$height}px ";
                    $shortcode = str_replace("id=XXXX", "id={$item->get_id()} _oid={$item->get_order_id()} _parent={$item->get_order_id()}{$size}", $data->shortcode);
                }

                if ($shortcode && $width && $height) {
                    include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/orders/order-item-barcode.php';
                }
            }
        } catch (\Throwable $th) {
        }

    }

    public function orderItemActionButton($order)
    {
        try {
            $orderId = $order->get_id();
            include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/orders/import-order-items.php';
        } catch (\Throwable $th) {
        }
    }

    public function woocommerce_admin_order_preview_get_order_details($data)
    {
        global $wpdb;

        try {
            $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

            $params = UserSettings::getJsonSectionOption('adminOrderPreviewParams', 'order', 1);

            if (!$params || !isset($params['width']) || !$params['width']) return;

            $sid = isset($params['shortcode']) ? $params['shortcode'] : null;
            $width = isset($params['width']) ? $params['width'] : null;
            $height = isset($params['height']) ? $params['height'] : null;

            if ($sid) {
                $shortcodeData = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = '%d' AND `type` = %s;", $sid, "order"));
            } else {
                $shortcodeData = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `is_default` = 1 AND `type` = %s;", "order"));
            }

            if ($data["data"] && $data["data"]["id"] && $shortcodeData) {
                $shortcode = str_replace("id=XXXX", "id={$data["data"]["id"]} width={$width}px height={$height}px ", $shortcodeData->shortcode);
                ob_start();
                require Variables::$A4B_PLUGIN_BASE_PATH . 'templates/orders/preview.php';
                $file = ob_get_clean();
                $data["actions_html"] .= $file;
            }
        } catch (\Throwable $th) {
        }

        return $data;
    }
}
