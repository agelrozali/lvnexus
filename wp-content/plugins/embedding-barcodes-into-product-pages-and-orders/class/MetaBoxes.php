<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\Variables;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class MetaBoxes
{
    public function productPage()
    {
        $screens = ['post', 'product'];
        foreach ($screens as $screen) {
            add_meta_box('barcode-generator-id', 'Barcode', array($this, "productView"), $screen, 'side');
        }
    }

    public function orderPage()
    {
        $screens = ['post', 'shop_order'];
        foreach ($screens as $screen) {
            add_meta_box('barcode-generator-id', 'Barcode', array($this, "orderView"), $screen, 'side');
        }
    }

    public function orderPagePrint()
    {
    }

    public function atumPOPagePrint()
    {
    }

    public function productView($post)
    {
        try {
            global $post, $wpdb;

            $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

            $params = UserSettings::getJsonSectionOption('adminProductPageParams', 'product', 1);

            if (!$params || !isset($params['width']) || !$params['width']) return;

            $sid = isset($params['shortcode']) ? $params['shortcode'] : null;
            $width = isset($params['width']) ? $params['width'] : null;
            $height = isset($params['height']) ? $params['height'] : null;

            if ($sid) {
                $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = '%d' AND `type` = %s;", $sid, "product"));
            } else {
                $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `is_default` = 1 AND `type` = %s;", "product"));
            }


            if ($data && $width && $height) {
                $shortcode = str_replace("id=XXXX", "id={$post->ID} width={$width}px height={$height}px ", $data->shortcode);
                include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/meta-boxes/product-meta-box.php';
            }

            $generatorFieldType = UserSettings::getOption('generatorFieldType', '');

            if ($generatorFieldType === "custom") {
                $generatorCustomField = UserSettings::getOption('generatorCustomField', '');
                $key = $generatorCustomField;

                $generatorCustomFieldValue = get_post_meta($post->ID, $key, true);

                if (!$generatorCustomFieldValue && $post->post_status === "auto-draft") {
                    $productsModel = new Products();
                    $generatorCustomFieldValue = $productsModel->getCodeForNewProduct();
                }

                include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/meta-boxes/product-code-meta-box.php';
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function orderView($post)
    {
        try {
            global $post, $wpdb;

            $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

            $params = UserSettings::getJsonSectionOption('adminOrderPageParams', 'order', 1);

            if (!$params || !isset($params['width']) || !$params['width']) return;

            $sid = isset($params['shortcode']) ? $params['shortcode'] : null;
            $width = isset($params['width']) ? $params['width'] : null;
            $height = isset($params['height']) ? $params['height'] : null;

            if ($sid) {
                $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = '%d' AND `type` = %s;", $sid, "order"));
            } else {
                $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `is_default` = 1 AND `type` = %s;", "order"));
            }

            if ($data && $width && $height) {
                $shortcode = str_replace("id=XXXX", "id={$post->ID} width={$width}px height={$height}px ", $data->shortcode);
                include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/meta-boxes/order-meta-box.php';
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function orderViewPrint($post)
    {
    }

    public function atumPoViewPrint($post)
    {
    }

    public function saveProductPage($post_id)
    {
        $generatorFieldType = UserSettings::getOption('generatorFieldType', '');

        if (isset($_POST["usbdGeneratorCustomFieldValue"]) && $generatorFieldType === "custom") {
            $value = sanitize_text_field($_POST["usbdGeneratorCustomFieldValue"]);
            $generatorCustomField = UserSettings::getOption('generatorCustomField', '');

            update_post_meta($post_id, $generatorCustomField, $value);
        }
    }
}
