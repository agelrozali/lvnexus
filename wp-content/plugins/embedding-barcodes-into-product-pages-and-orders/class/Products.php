<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\Sanitize;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use UkrSolution\BarcodesDigital\Helpers\Variables;

class Products
{
    private $fieldName = "digital_barcode_product_field";
    private $fieldLabel = "Barcode";

    public function addImportButton()
    {
        global $post_type;

        if ($post_type === 'product' && is_admin()) {
            include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/import-button.php';
        }
    }

    public function mediaButtons()
    {
        global $post_type, $post;

        if ($post_type === 'product' && is_admin()) {
            include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/import-single-product-button.php';
        }
    }

    public function variationButtons($variation)
    {
        if ($variation && is_admin()) {
            include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/import-variable-button.php';
        }
    }

    public function variationBarcode($variation)
    {
        global $wpdb;

        if ($variation && is_admin()) {
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
                $shortcode = str_replace("id=XXXX", "id={$variation->ID} width={$width}px height={$height}px datatype=url _errors=return", $data->shortcode);
                $jsShortcode = do_shortcode($shortcode);
                $jsShortcode = strip_tags(trim($jsShortcode));
                $jsShortcode = str_replace(array('"', "'"), '', $jsShortcode);
                include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/variable-barcode-popup.php';
            }
        }
    }

    public function variationInnerBarcode($loop, $variation_data, $variation)
    {
        global $wpdb;

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
            $shortcode = str_replace("id=XXXX", "id={$variation->ID} width={$width}px height={$height}px ", $data->shortcode);
            echo '<div style="max-width:initial;">' . do_shortcode($shortcode) . '</div>';
        }
    }

    public function product_column_barcode($column)
    {
        global $post;

        try {
            if ($column === 'usbarcode') {
                $key = UserSettings::getOption('generatorFieldType', '');

                if ($key === "custom") {
                    $key = UserSettings::getOption('generatorCustomField', '');
                }

                $value = get_post_meta($post->ID, $key, true);

                $url = get_admin_url() . 'admin.php?page=wpbcu-digital-settings';

                if ($value) {
                    $barcodeImg = $this->generateImage($post->ID, $post->post_parent);

                    echo '<div>' . esc_html__($value) . $barcodeImg . '</div>';
                } else {
                    if ($key !== "ID") {
                        $codesList = $this->getCodesFromDB();
                        $existingCodes = $this->getExistingCodes($key, $codesList);

                        if (count($codesList) - count($existingCodes) > 0) {
                            echo '<a href="' . $url . '">Assign code.</a>';
                        } else {
                            echo 'There are 0 codes left in database. Please add new codes <a href="' . $url . '">here</a>';
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
        }
    }

    public function product_column_barcode_style()
    {
        echo '<style type="text/css">';
        echo 'table.wp-list-table .column-usbarcode { width: 100px; text-align: left !important; padding: 5px; }';
        echo 'table.wp-list-table .column-usbarcode div { position: relative; cursor: pointer; }';
        echo 'table.wp-list-table .column-usbarcode div img { position: absolute; top: 20px; right: 15px; display: none; }';
        echo 'table.wp-list-table .column-usbarcode div:hover img { display: initial; z-index: 1000; }';
        echo '</style>';
    }

    public function manage_edit_product_add_columns($columns)
    {
        $columns["usbarcode"] = esc_html__('Barcode', 'wpbcu-barcode-generator');
        return $columns;
    }

    public function search()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        if (isset($_POST['search'])) {
            $post['search'] = sanitize_text_field($_POST['search']);
        }

        $validationOptions = array('search' => 'required|string');

        $data = Validator::create($post, $validationOptions, true)->validate();

        $query = str_replace(" ", "%", $data['search']);

        $sql = "SELECT P.* FROM {$wpdb->posts} AS P, {$wpdb->postmeta} AS PM WHERE P.ID = PM.post_id AND P.post_type IN('product','product_variation','shop_order','shop_coupon') ";
        $sql .= " AND (P.ID = '%s' OR P.post_title LIKE '%s' OR (PM.meta_key = '_SKU' AND PM.meta_value LIKE '%s')) ";
        $sql .= " GROUP BY P.ID";
        $sql = $wpdb->prepare($sql, $data['search'], "%{$query}%", "%{$query}%");
        $results = $wpdb->get_results($sql, ARRAY_A);

        $sql = "SELECT U.*, 'user' AS 'post_type' FROM {$wpdb->users} AS U WHERE ";
        $sql .= " U.ID = '%s' OR U.user_login LIKE '%{$query}%' OR U.user_nicename LIKE '%{$query}%' OR U.user_email LIKE '%{$query}%' ";
        $sql .= " GROUP BY U.ID";
        $sql = $wpdb->prepare($sql, $data['search']);
        $users = $wpdb->get_results($sql, ARRAY_A);

        if ($users) $results = array_merge($results, $users);

        uswbg_a4bJsonResponse($results);
    }

    public function shortcodeOnProductPage()
    {
        $shortcodeProductHook = UserSettings::getOption('shortcodeProductHook', 'woocommerce_product_meta_end');

        if (!$shortcodeProductHook) {
            return;
        }

        add_filter($shortcodeProductHook, array($this, 'woocommerce_product_page_hook'), 10, 3);
    }

    public function woocommerce_product_page_hook()
    {
        global $wpdb, $product;

        $params = UserSettings::getJsonSectionOption('barcodesOnProductPageParams', 'product');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 1;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        $id = $product->get_id();

        if (!$id || !$sid) {
            return;
        }

        if ($width && $height) {
            $size = " width={$width}px height={$height}px ";
            echo do_shortcode("[barcode id=" . $id . " shortcode=" . $sid . " class=digital-barcode-embedded" . $size . "]");
        }
    }

    public function importCodes($data = null)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        if (empty($data)) {
            $keys = array(
                "generatorCodeType", "generatorFieldType", "generatorCustomField", "chWithoutEanUpc", "chInstock", "chPriceGreater",
                "limit", "offset",
                "isPreview", "uploading",
                "generatorCodeList",
            );
            $data = (new Sanitize())::getData($keys);
        }

        if (!count($data)) {
            uswbg_a4bJsonResponse(array("error" => "Wrong data"));
        }

        $codesList = array();

        if ($data["uploading"] === "true") {
            $this->uploadCscFile();
        } else {
        }
        $codesList = $this->getCodesFromDB();

        $additionalField = $data["generatorFieldType"];


        if ($additionalField === "custom") {
            $additionalField = $data["generatorCustomField"];
        }

        $posts = $this->getProductsIds($data["limit"], $data["offset"]);
        $ids = implode(",", $posts["ids"]);
        $data["statistics"] = array(
            "total" => $posts["total"],
            "validatedTotal" => 0,
            "existingCodes" => array(),
            "totalCodes" => count($codesList),
        );

        $data["test"] = array();

        $products = $this->getProductsMetaByIds($ids, $additionalField);

        $existingCodes = $this->getExistingCodes($additionalField, $codesList);
        $data["statistics"]["existingCodes"] = count($existingCodes);

        $codesIndex = 0;
        foreach ($products as $id => $product) {
            if ($additionalField) {
                $chWithoutEanUpc = $data["chWithoutEanUpc"] === "true";
                $chInstock = $data["chInstock"] === "true";
                $chPriceGreater = $data["chPriceGreater"] === "true";

                $checkerEmptyCode = $checkerInstock = $checkerPrice = true;

                if ($chWithoutEanUpc) {
                    if (!isset($product[$additionalField]) || empty($product[$additionalField])) {
                    } else {
                        $checkerEmptyCode = false;
                    }
                }

                if ($chInstock) {
                    if (isset($product['_stock_status']) && $product['_stock_status'] === "instock") {
                    } else {
                        $checkerInstock = false;
                    }
                }

                if ($chPriceGreater) {
                    if (isset($product['_price']) && $product['_price'] > 0) {
                    } else {
                        $checkerPrice = false;
                    }
                }
                if ($checkerEmptyCode && $checkerInstock && $checkerPrice) {
                    $value = $this->getFreeCode($codesList, $existingCodes);

                    $data["statistics"]["validatedTotal"]++;


                    if ($value && $data["isPreview"] !== "true") {
                        update_post_meta($id, $additionalField, $value);

                        $postParent = isset($product["post_parent"]) ? $product["post_parent"] : "";
                        $this->generateImage($id, $postParent);
                    }

                    $codesIndex++;
                }
            }
        }

        if ($data["isPreview"] !== "true") {
            $data["statistics"]["existingCodes"] = count($existingCodes);
        }

        $data["importCodesData"] = $this->getImportFilesData();

        uswbg_a4bJsonResponse($data);
    }

    private function generateImage($postId, $postParent)
    {
        $params = UserSettings::getJsonSectionOption('barcodesOnProductPageParams', 'product');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 1;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        ob_start();

        $size =  " width={$width}px height={$height}px ";

        return do_shortcode("[barcode id=" . $postId . " shortcode=" . $sid . "" . $size . " _errors=return]");

        return ob_get_clean();
    }

    private function getFreeCode($codesList, &$existingCodes)
    {
        foreach ($codesList as $code) {
            if ($code && !in_array($code, $existingCodes)) {
                $existingCodes[] = $code;

                return $code;
            }
        }

        return null;
    }

    public function getCodeForNewProduct()
    {
        $codesList = $this->getCodesFromDB();
        $generatorCustomField = UserSettings::getOption('generatorCustomField', '');
        $existingCodes = $this->getExistingCodes($generatorCustomField, $codesList);
        $code = $this->getFreeCode($codesList, $existingCodes);

        return $code;
    }

    private function uploadCscFile()
    {
        global $wpdb;

        if (!isset($_FILES["file"])) {
            return;
        }

        $file = $_FILES["file"];
        $content = file_get_contents($file['tmp_name']);

        $tblCodesFiles = $wpdb->prefix . Database::$tableCodesFiles;
        $tblCodes = $wpdb->prefix . Database::$tableCodes;

        $codes = str_getcsv($content, "\n");

        if (count($codes) < 2) {
            $codes = str_getcsv($content, ";");
        }

        if (count($codes) < 2) {
            $codes = str_getcsv($content, ",");
        }

        if (!count($codes)) {
            return;
        }

        $codes = array_unique($codes);

        $md5 = md5_file($file['tmp_name']);
        $sql = "SELECT * FROM {$tblCodesFiles} AS F WHERE F.md5 = '%s' ";
        $sql = $wpdb->prepare($sql, $md5);
        $existingFile = $wpdb->get_row($sql);

        if ($existingFile) {
            uswbg_a4bJsonResponse(array("error" => "This file has been uploaded already."));
        }

        $wpdb->insert($tblCodesFiles, array("filename" => $file["name"], "md5" => $md5), array('%s'));
        $fileId = $wpdb->insert_id;

        $values = array();
        $placeHolders = array();
        $query = "INSERT INTO {$tblCodes} (fileId, code) VALUES ";

        foreach ($codes as $code) {
            array_push($values, $fileId, $code);
            $placeHolders[] = "('%d', '%s')";
        }

        $query .= implode(', ', $placeHolders);
        $wpdb->query($wpdb->prepare("$query ", $values));

        return true;
    }

    private function getCodesFromDB()
    {
        global $wpdb;

        $tblCodes = $wpdb->prefix . Database::$tableCodes;

        $sql = "SELECT * FROM {$tblCodes};";
        $codesRows = $wpdb->get_results($sql, ARRAY_A);
        $codes = array();

        foreach ($codesRows as $value) {
            array_push($codes, $value["code"]);
        }

        return array_unique($codes);
    }

    private function getCodesFromCSV($data)
    {
        $codesList = array();

        if (isset($data["generatorCodeList"])) {
            $lines = str_getcsv($data["generatorCodeList"], " ");

            foreach ($lines as $line) {
                $code = explode(",", $line);

                if ($code && count($code)) {
                    $value = trim($code[0]);

                    if ($value) {
                        $codesList[] = $value;
                    }
                }
            }
        }

        return array_unique($codesList);
    }

    private function getProductsIds($limit, $offset)
    {
        global $wpdb;

        $productsIdsSql = "SELECT SQL_CALC_FOUND_ROWS P.ID FROM {$wpdb->posts} AS P ";
        $productsIdsSql .= " WHERE P.post_type IN('product', 'product_variation') AND P.post_status IN('publish') ";
        $productsIdsSql .= " LIMIT %d, %d;";
        $productsIdsSql = $wpdb->prepare($productsIdsSql, $offset, $limit);
        $productsIds = $wpdb->get_results($productsIdsSql, ARRAY_A);
        $ids = array();

        $totalFoundSql = "SELECT FOUND_ROWS() as 'total';";
        $totalFound = $wpdb->get_row($totalFoundSql);

        foreach ($productsIds as $value) {
            $ids[] = $value["ID"];
        }

        return array(
            "ids" => $ids,
            "total" => $totalFound->total,
        );
    }

    private function getProductsMetaByIds($ids, $additionalField)
    {
        global $wpdb;

        $metaSql = "SELECT PM.post_id, PM.meta_key, PM.meta_value, P.post_parent ";
        $metaSql .= " FROM {$wpdb->posts} AS P, {$wpdb->postmeta} AS PM ";
        $metaSql .= " WHERE P.ID = PM.post_id ";
        $metaSql .= " AND P.ID IN({$ids}) ";
        $metaSql .= " AND PM.meta_key IN('%s', '_price', '_stock_status');";

        $metaSql = $wpdb->prepare($metaSql, $additionalField);
        $metaData = $wpdb->get_results($metaSql, ARRAY_A);
        $products = array();

        foreach ($metaData as $value) {

            if (!isset($products[$value["post_id"]])) {
                $products[$value["post_id"]] = array("post_parent" => $value["post_parent"]);
            }

            $products[$value["post_id"]][$value["meta_key"]] = $value["meta_value"];
        }

        return $products;
    }

    private function getExistingCodes($additionalField, $codesList)
    {
        global $wpdb;

        $sql = "SELECT PM.meta_value FROM {$wpdb->postmeta} AS PM WHERE PM.meta_key IN('%s') AND PM.meta_value IN('" . implode("','", $codesList) . "');";
        $sql = $wpdb->prepare($sql, $additionalField);
        $existingCodes = $wpdb->get_results($sql, ARRAY_A);
        $codes = array();

        foreach ($existingCodes as $value) {
            $codes[] = $value["meta_value"];
        }

        return $codes;
    }

    public function getImportFilesData()
    {
        global $wpdb;

        $tblCodesFiles = $wpdb->prefix . Database::$tableCodesFiles;
        $tblCodes = $wpdb->prefix . Database::$tableCodes;

        $sql = "SELECT * FROM {$tblCodesFiles};";
        $files = $wpdb->get_results($sql, ARRAY_A);

        $sql = "SELECT * FROM {$tblCodes};";
        $codes = $wpdb->get_results($sql, ARRAY_A);

        $data = array();

        $key = UserSettings::getOption('generatorFieldType', '');

        if ($key === "custom") {
            $key = UserSettings::getOption('generatorCustomField', '');
        }

        foreach ($files as $file) {
            $codesList = array();

            foreach ($codes as $value) {
                if ($value["fileId"] === $file["id"]) {
                    $codesList[] = $value["code"];
                }
            }

            $existingCodes = $this->getExistingCodes($key, $codesList);

            $data[] = array(
                "id" => $file["id"],
                "filename" => $file["filename"],
                "totalCodes" => count($codesList),
                "leftCodes" => count($codesList) - count($existingCodes),
                "usedCodes" => count($existingCodes),
            );
        }

        return $data;
    }

    public function transition_post_status()
    {
        # code...
    }

    public function woocommerce_product_options_sku()
    {
        global $post;

        $value = get_post_meta($post->ID, $this->fieldName, true);


        include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/barcode-field.php';
    }

    public function woocommerce_process_product_meta($postId)
    {
        if (isset($_POST[$this->fieldName])) {
            $value = sanitize_text_field($_POST[$this->fieldName]);
            update_post_meta($postId, $this->fieldName, $value);
        }

        if (isset($_POST["v_{$this->fieldName}"]) && is_array($_POST["v_{$this->fieldName}"])) {
            foreach ($_POST["v_{$this->fieldName}"] as $variationId => $fieldValue) {
                $value = sanitize_text_field($fieldValue);
                update_post_meta($variationId, $this->fieldName, esc_attr($value));
            }
        }
    }

    public function woocommerce_variation_options($loop, $variation_data, $variation)
    {
        $value = get_post_meta($variation->ID, $this->fieldName, true);


        include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/barcode-variation-field.php';
    }

    public function woocommerce_save_product_variation($variationId)
    {
        $value = "";
        if (isset($_POST["v_{$this->fieldName}"])) {
            $value = sanitize_text_field($_POST["v_{$this->fieldName}"][$variationId]);
        }

        if ($value) {
            update_post_meta($variationId, $this->fieldName, esc_attr($value));
        }
    }

    public function woocommerce_available_variation($variations)
    {
        return $variations;
    }

    public function customFields()
    {
        $customFieldLabel = UserSettings::getoption('customFieldLabel', "Custom Field");
        $customFieldName = UserSettings::getoption('customFieldName', "");

        add_action('woocommerce_product_options_sku', function () use ($customFieldLabel, $customFieldName) {
            global $post;

            if (!$post) {
                return;
            }

            $value = get_post_meta($post->ID, $customFieldName, true);
            $args = array(
                'label' => $customFieldLabel,
                'placeholder' => __('Enter value', 'wpbcu-barcode-generator'),
                'id' => $customFieldName,
                'desc_tip' => true,
                'description' => __('This field created by "Barcode Generator - create barcodes for products, orders, emails" plugin, you can rename this field or disable it in the plugin\'s settings.', 'wpbcu-barcode-generator'),
                'value' => $value
            );
            \woocommerce_wp_text_input($args);
        });
        add_action('woocommerce_process_product_meta', function ($postId) use ($customFieldName) {
            $usbsBarcodeField = isset($_POST[$customFieldName]) ? sanitize_text_field($_POST[$customFieldName]) : '';

            update_post_meta($postId, $customFieldName, $usbsBarcodeField);
        });

        add_action('woocommerce_variation_options_pricing', function ($loop, $variation_data, $variation) use ($customFieldLabel, $customFieldName) {
            $value = get_post_meta($variation->ID, $customFieldName, true);
            $args = array(
                'class' => 'short',
                'label' => $customFieldLabel,
                'placeholder' => __('Enter value', 'wpbcu-barcode-generator'),
                'id' => $customFieldName . '_v[' . $loop . ']',
                'desc_tip' => true,
                'description' => __('This field created by "Barcode Generator - create barcodes for products, orders, emails" plugin, you can rename this field or disable it in the plugin\'s settings.', 'wpbcu-barcode-generator'),
                'value' => $value,
                'wrapper_class' => 'form-row form-row-full'
            );
            \woocommerce_wp_text_input($args);
        }, 10, 3);
        add_action('woocommerce_save_product_variation', function ($variationId, $loop) use ($customFieldName) {
            $value = isset($_POST[$customFieldName . '_v']) && isset($_POST[$customFieldName . '_v'][$loop]) ? $_POST[$customFieldName . '_v'][$loop] : "";
            update_post_meta($variationId, $customFieldName, $value);
        }, 15, 2);
    }

    public function productsPageColumns()
    {
        add_filter('manage_product_posts_columns', function ($columns) {
            $columns['barcode_label'] = __('Label', 'cs-text');

            return $columns;
        });

        add_action('manage_product_posts_custom_column', function ($column, $post_id) {

            switch ($column) {
                case 'barcode_label':
                    $post = get_post($post_id);
                    $product = @wc_get_product($post_id);
                    include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/products/import-single-product-button-products.php';
                    break;
            }
        }, 10, 2);

        add_action('admin_enqueue_scripts', function () {
            wp_register_style('barcode_label_style_products_page', false);
            wp_enqueue_style('barcode_label_style_products_page');
            wp_add_inline_style('barcode_label_style_products_page', "table.wp-list-table .column-barcode_label{ width: 40px !important; } .barcodes-import-single-product {float: right;margin-left: 5px !important;}");
        });
    }

    public function getVariationByProduct()
    {
        $result = array("variations" => array());

        try {
            if (isset($_POST['id'])) {
                $id = sanitize_text_field($_POST['id']);

                if (!$id) {
                    uswbg_a4bJsonResponse($result);
                }

                $product = wc_get_product($id);
                $children = $product->get_children();

                foreach ($children as $variationId) {
                    $variation = get_post($variationId);

                    if ($variation->post_type == "product_variation") {
                        $result["variations"][] = array(
                            "id" => $variationId,
                            "name" => $variation->post_excerpt,
                        );
                    }
                }
            }
        } catch (\Throwable $th) {
        }

        uswbg_a4bJsonResponse($result);
    }
}
