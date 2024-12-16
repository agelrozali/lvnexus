<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Helpers\Variables;
use UkrSolution\BarcodesDigital\Helpers\Files;
use UkrSolution\BarcodesDigital\Makers\WoocommercePostsA4BarcodesMaker;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class Shortcodes
{
    private $barcodeMetaKey = '_digital_shortcode_id_XXXX_to_file';
    private $uploadDir = array();
    private $idType = "post";

    public function get($attributes)
    {
        global $wpdb;

        if (!isset($attributes["shortcode"])) {
            return "";
        }

        $this->uploadDir = wp_upload_dir();

        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        $shortcode = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $attributes["shortcode"])
        );

        if (!$shortcode) {
            return "";
        }

        $shortcode = $this->prepareShortcodeMatching($shortcode);

        if (isset($attributes['class']) && is_product()) {
            wp_enqueue_style('product-barcode', Variables::$A4B_PLUGIN_BASE_URL . 'assets/css/style-2.0.2-00123546.css', array());
            wp_enqueue_script('product-barcode-js', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/barcodes-core-2.0.2-00123546.js', array('jquery'), null, true);

            wp_localize_script('product-barcode-js', 'digitalBarcodeJS', array(
                'ajaxUrl' => get_admin_url() . 'admin-ajax.php',
                'routes' => array(
                    'generateBarcode' => 'a4barcode_d_generate_barcode_product_image'
                )
            ));
        }

        if ($shortcode->type === "order") {
            return $this->generateBarcode($shortcode, $attributes, 'orders');
        } else if ($shortcode->type === "custom") {
            return $this->generateCustomBarcode($shortcode, $attributes);
        } else {
            return $this->generateBarcode($shortcode, $attributes);
        }
    }

    private function generateCustomBarcode($shortcode, $attributes)
    {
        $type = (isset($attributes['_action'])) ? $attributes['_action'] : $shortcode->type;
        $code = (isset($attributes['code'])) ? $attributes['code'] : null;
        $line1 = (isset($attributes['line1'])) ? $attributes['line1'] : "";
        $line2 = (isset($attributes['line2'])) ? $attributes['line2'] : "";
        $line3 = (isset($attributes['line3'])) ? $attributes['line3'] : "";
        $line4 = (isset($attributes['line4'])) ? $attributes['line4'] : "";
        $settings = $shortcode->matching ? (array)$shortcode->matching : array();

        $template = "";
        $templateId = $settings["template"];
        $templatesController = new BarcodeTemplatesController();

        if ($templateId) {
            $template = $templatesController->getTemplateById($templateId);
        }

        if (!$template) {
            $template = $templatesController->getTemplateById(1);
        }

        $id = md5($code . $line1 . $line2 . $line3 . $line4);
        $shortcodeUpdated = strtotime($shortcode->datetime);
        $templateTimestamp = strtotime($template->datetime);

        if ($code === null) {
            return "";
        }

        $filePath = Files::checkFile($id, $shortcode->id, null, $type, $shortcodeUpdated, "", $templateTimestamp, $id);

        if ($filePath) {
            $list = array('listItems' => array(array('image' => $filePath)));

            return $this->renderProductBarcodes($template, $id, $list, $attributes, $settings);
        } else {

            $file = Files::createFile($id, $shortcode->id, null, $type, $shortcodeUpdated, "", $templateTimestamp, $id);

            $settings["lineBarcode"] = $code;
            $settings["fieldLine1"] = $line1;
            $settings["fieldLine2"] = $line2;
            $settings["fieldLine3"] = $line3;
            $settings["fieldLine4"] = $line4;

            if (isset($attributes['width']) && $attributes['width'] && isset($attributes['height']) && $attributes['height']) {
                $size = array("width" => (int)$attributes['width'], "height" => (int)$attributes['height']);
            } else {
                $size = null;
            }

            $postsBarcodesGenerator = new WoocommercePostsA4BarcodesMaker($settings, "custom");
            $list = $postsBarcodesGenerator->make(array(
                'imageByUrl' => true,
                'templateId' => $templateId,
                'fileName' => $file->path,
                'type' => $type,
                'size' => $size,
            ));


            $errorMethod = (isset($attributes['_errors']) && $attributes['_errors']) ? $attributes['_errors'] : '';
            $errors = $this->checkBarcodeErrors($list, "custom", $shortcode, $errorMethod);

            if ($errors && $errorMethod === 'return') {
                return implode("", $errors);
            } else {
                echo implode("", $errors);
            }

            if ($list["listItems"]) {
                update_post_meta($id, str_replace("XXXX", $shortcode->id, $this->barcodeMetaKey), $file->id);
                Files::updateVersion($file->id);
            }

            return $this->renderProductBarcodes($template, $id, $list, $attributes, $settings);
        }
    }

    private function generateBarcode($shortcode, $attributes, $postType = '')
    {
        $postType = $postType ? $postType : "products";
        $settings = $shortcode->matching ? (array)$shortcode->matching : array();
        $id = null;

        if (!$settings) {
            return "";
        }

        if (!isset($attributes["id"])) {
            if (isset($attributes["order_id"])) $attributes["id"] = $attributes["order_id"];
            else if (isset($attributes["product_id"])) $attributes["id"] = $attributes["product_id"];
        }

        if (isset($attributes["id"])) {
            $id = $attributes["id"];
            $post = @get_post($id);

            if ($post) {
                if ($post->post_type === "shop_order") {
                } else if ($post->post_type === "product") {
                } else if ($post->post_type === "product_variation") {
                    $settings['isImportSingleVariation'] = 'variation';
                } else if ($post->post_type === "wc_appointment") {
                    $postType = "appointment";
                }
            }
        }




        try {
            if (preg_match("/^\{.*\}$/", $id, $m)) {
                if (count($m) === 1) {
                    $s = str_replace(array("{", "}"), "", $m[0]);
                    $id = do_shortcode("[{$s}]");
                }
            }
        } catch (\Throwable $th) {
        }

        $template = "";
        $templateId = $settings["template"];
        $templatesController = new BarcodeTemplatesController();

        if ($templateId) {
            $template = $templatesController->getTemplateById($templateId);
        }

        if (!$template) {
            $template = $templatesController->getTemplateById(1);
        }

        if ($postType === "orders") {
            $settings["ordersIds"] = [$id];
        } else if ($postType === "appointment") {
            $settings["appointmentsIds"] = [$id];
        } else {
            $settings["productsIds"] = [$id];
        }

        $parentItemId = isset($attributes['_parent']) ? $attributes['_parent'] : null;
        $shortcodeUpdated = strtotime($shortcode->datetime);
        $postUpdated = get_post_modified_time('U', false, $id);
        $templateTimestamp = strtotime($template->datetime);
        $type = (isset($attributes['_action'])) ? $attributes['_action'] : $shortcode->type;

        if (isset($settings["fieldStorage"]) && $settings["fieldStorage"] === "base64") {
            $filePath = null;
        } else {
            $filePath = Files::checkFile($id, $shortcode->id, $parentItemId, $type, $shortcodeUpdated, $postUpdated, $templateTimestamp);
        }

        if ($filePath) {
            $list = array('listItems' => array(array(
                'image' => $filePath
            )));

            return $this->renderProductBarcodes($template, $id, $list, $attributes, $settings);
        } else {

            $file = Files::createFile($id, $shortcode->id, $parentItemId, $type, $shortcodeUpdated, $postUpdated, $templateTimestamp);
            $action = isset($attributes["_action"]) ? $attributes["_action"] : null;

            if (
                is_plugin_active('license-manager-for-woocommerce/license-manager-for-woocommerce.php')
                || in_array($action, array("email-product"))
                || isset($attributes['_oid'])
            ) {
                $parentOrderId = isset($attributes['_oid']) ? $attributes['_oid'] : null;

                if ($parentOrderId && isset($settings['productsIds'])) {
                    $settings['ordersIds'] = array($parentOrderId);
                    $settings['itemsIds'] = $settings['productsIds'];
                    unset($settings['productsIds']);
                    $type = $postType = 'order-products';
                } else if (isset($attributes['_parent']) && isset($settings['productsIds'])) {
                    $settings['ordersIds'] = array($attributes['_parent']);
                    $settings['itemsIds'] = $settings['productsIds'];
                    unset($settings['productsIds']);
                    $postType = 'order-products';
                }
            }

            if (isset($attributes['width']) && $attributes['width'] && isset($attributes['height']) && $attributes['height']) {
                $size = array("width" => (int)$attributes['width'], "height" => (int)$attributes['height']);
            } else {
                $size = null;
            }

            $postsBarcodesGenerator = new WoocommercePostsA4BarcodesMaker($settings, $postType);
            $generateOptions = array(
                'imageByUrl' => true,
                'templateId' => $templateId,
                'fileName' => $file->path,
                'type' => $type,
                'size' => $size,
            );

            if (isset($settings["fieldStorage"]) && $settings["fieldStorage"] === "base64") {
                $generateOptions["storage"] = $settings["fieldStorage"];
            }

            $list = $postsBarcodesGenerator->make($generateOptions);

            $this->resetVariationsTimestamps($id, $shortcode->id);

            $errorMethod = (isset($attributes['_errors']) && $attributes['_errors']) ? $attributes['_errors'] : '';
            $errors = $this->checkBarcodeErrors($list, $postType, $shortcode, $errorMethod);

            if ($errors && $errorMethod === 'return') {
                return implode("", $errors);
            } else {
                echo implode("", $errors);
            }

            if ($list["listItems"]) {
                update_post_meta($id, str_replace("XXXX", $shortcode->id, $this->barcodeMetaKey), $file->id);
                Files::updateVersion($file->id);
            }

            return $this->renderProductBarcodes($template, $id, $list, $attributes, $settings);
        }
    }

    private function renderProductBarcodes($template, $itemId, $list, $attributes, $settings)
    {
        try {
            if (!$list["listItems"]) {
                return;
            }

            ob_start();

            foreach ($list["listItems"] as $key => $item) {
                $fileName = $item["image"] ? $item["image"] : "";

                if (isset($settings["fieldStorage"]) && $settings["fieldStorage"] === "base64") {
                    $imageUrl = $fileName;
                } else {
                    $imageUrl = $this->uploadDir['baseurl'] . $fileName;
                }

                $action = isset($attributes["_action"]) ? $attributes["_action"] : null;

                $removeDomainBarcodeUrl = UserSettings::getOption('removeDomainBarcodeUrl', false);

                if ((bool)$removeDomainBarcodeUrl && !in_array($action, array("email-product", "email-order"))) {
                    $imageUrl = str_replace("https", "http", $imageUrl);
                    $domain = str_replace("https", "http", home_url());
                    $imageUrl = str_replace($domain, '', $imageUrl);
                }

                if (!(bool)$removeDomainBarcodeUrl) {
                    $protocol = substr(home_url(), 0, 5);

                    if ($protocol === "https") {
                        $imageUrl = str_replace("http:", "https:", $imageUrl);
                    }
                }

                $disableBrowserCache = UserSettings::getOption('disableBrowserCache', false);

                if ((bool)$disableBrowserCache && !in_array($action, array("email-product", "email-order"))) {
                    $imageUrl = preg_replace("/\?r=.*/", "", $imageUrl);
                }

                $uol = "px";

                if (isset($attributes['width']) && $attributes['width'] && isset($attributes['height']) && $attributes['height']) {
                    $width = $attributes['width'];
                    $height = $attributes['height'];
                } else if (isset($attributes['width']) && $attributes['width']) {
                    $width = $attributes['width'];
                    $height = 'auto';
                } else {
                    $width = $template->width . $uol;
                    $height = $template->height . $uol;
                }

                $style = "width:" . $template->width . $uol;
                $style .= ";height:" . $template->height . $uol;
                $class = (isset($attributes['class'])) ? $attributes['class'] : '';

                if (isset($attributes) && isset($attributes["datatype"]) && $attributes["datatype"] === "object") {
                    echo json_encode(array(
                        "url" => $imageUrl,
                    ));
                } else if (isset($attributes) && isset($attributes["datatype"]) && $attributes["datatype"] === "url") {
                    echo  $imageUrl;
                } else {
                    $alt = "";
                    $title = "";

                    if (isset($item["lineBarcode"])) {
                        $alt = $item["lineBarcode"];
                        $title = $item["lineBarcode"];
                    }

                    include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/shortcodes/product-barcode-image.php';
                }
            }

            return ob_get_clean();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function checkBarcodeErrors($list, $postType, $shortcode, $errorMethod = "")
    {
        $errors = array();

        if ($list["error"] && is_admin()) {
            foreach ($list["error"] as $key => $error) {
                if ($error["lineBarcode"]) {
                    preg_match('/(\"(.*?)\")/', $error["lineBarcode"], $m);

                    if ($m && false) {

                    } else {
                        $errors[] = "<div>" . $error["lineBarcode"] . "</div>";
                    }
                }
                if ($error["line1"]) {
                    $errors[] = "<div>" . $error["line1"] . "</div>";
                }
                if ($error["line2"]) {
                    $errors[] = "<div>" . $error["line2"] . "</div>";
                }
                if ($error["line3"]) {
                    $errors[] = "<div>" . $error["line3"] . "</div>";
                }
                if ($error["line4"]) {
                    $errors[] = "<div>" . $error["line4"] . "</div>";
                }
            }
        }

        return $errors;
    }

    public function shortcodesGet()
    {
        global $wpdb;

        $post = array();
        if (isset($_POST['type'])) {
            $post['type'] = sanitize_text_field($_POST['type']);
        }

        $validationOptions = array(
            'type' => 'string',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();

        $type = isset($data["type"]) ? $data["type"] : "";

        uswbg_a4bJsonResponse(array(
            'shortcodes' => $this->shortcodesGetByType($type),
        ));
    }

    public function shortcodesGetByType($type = "")
    {
        global $wpdb;

        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        if ($type) {
            $shortcodes = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `type` = %s;", $type),
                ARRAY_A
            );
        } else {
            $shortcodes = $wpdb->get_results("SELECT * FROM `{$tableShortcodes}`;", ARRAY_A);
        }

        return $this->prepareShortcodes($shortcodes);
    }

    public function shortcodeGetById()
    {
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_key($_POST['id']);
        }

        $validationOptions = array(
            'id' => 'required|string',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();

        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        $shortcode = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $data["id"]),
            ARRAY_A
        );

        uswbg_a4bJsonResponse(array(
            'shortcode' => $this->prepareShortcode($shortcode),
        ));
    }

    public function saveOrder()
    {
        $post = array();
        foreach (array('format', 'withVariations', 'shortcodeId', 'fieldStorage', 'sellerName', 'vatNumber') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        if (isset($_POST['template'])) {
            $post['template'] = wp_kses($_POST['template'], 'post');
        }
        foreach (array('fieldLine1', 'fieldLine2', 'fieldLine3', 'fieldLine4') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key], true);
            }
        }
        if (isset($_POST['lineBarcode'])) {
            $post['lineBarcode'] = USWBG_a4bRecursiveSanitizeTextareaField($_POST['lineBarcode'], true);
        }

        $validationRules = array(
            'template' => 'required|string',
            'lineBarcode' => 'required|array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'format' => 'required',
            'withVariations' => 'strtobool|boolean',
            'shortcodeId' => 'string',
            'fieldStorage' => 'string',
            'sellerName' => 'string',
            'vatNumber' => 'string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $this->saveShortcode($data, 'order');
    }

    public function saveCustom()
    {
        $post = array(
            'lineBarcode' => array("value" => "", "type" => "static"),
            'fieldLine1' => array("value" => "", "type" => "static"),
            'fieldLine2' => array("value" => "", "type" => "static"),
            'fieldLine3' => array("value" => "", "type" => "static"),
            'fieldLine4' => array("value" => "", "type" => "static"),
        );
        foreach (array('format', 'withVariations', 'shortcodeId', 'fieldStorage', 'sellerName', 'vatNumber') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        if (isset($_POST['template'])) {
            $post['template'] = wp_kses($_POST['template'], 'post');
        }

        $validationRules = array(
            'template' => 'required|string',
            'lineBarcode' => 'array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'format' => 'required',
            'withVariations' => 'strtobool|boolean',
            'shortcodeId' => 'string',
            'fieldStorage' => 'string',
            'sellerName' => 'string',
            'vatNumber' => 'string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $this->saveShortcode($data, 'custom');
    }

    public function saveProduct()
    {
        $post = array();
        foreach (array('format', 'withVariations', 'shortcodeId', 'fieldStorage', 'sellerName', 'vatNumber') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        if (isset($_POST['template'])) {
            $post['template'] = wp_kses($_POST['template'], 'post');
        }
        foreach (array('fieldLine1', 'fieldLine2', 'fieldLine3', 'fieldLine4') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        if (isset($_POST['lineBarcode'])) {
            $post['lineBarcode'] = USWBG_a4bRecursiveSanitizeTextareaField($_POST['lineBarcode'], true);
        }

        $validationRules = array(
            'template' => 'required|string',
            'lineBarcode' => 'required|array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'format' => 'required',
            'withVariations' => 'strtobool|boolean',
            'shortcodeId' => 'string',
            'fieldStorage' => 'string',
            'sellerName' => 'string',
            'vatNumber' => 'string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $this->saveShortcode($data, 'product');
    }

    private function saveShortcode($data, $type)
    {
        global $wpdb;

        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;
        $id = null;

        if (isset($data["shortcodeId"])) {
            $id = $data["shortcodeId"];
            unset($data["shortcodeId"]);
        }

        if ($id) {
            if (isset($data["template"]) && $type === "custom") {
                $lines = '';

                if (in_array((int)$data["template"], array(4, 7))) {
                    $lines = ' line1="" line2="" line3="" line4=""';
                } else if (in_array((int)$data["template"], array(1, 5, 8, 9))) {
                    $lines = '';
                } else if (in_array((int)$data["template"], array(2))) {
                    $lines = ' line1=""';
                } else if (in_array((int)$data["template"], array(3, 6))) {
                    $lines = ' line1="" line2=""';
                }

                $shortcode = '[barcode code=XXXX shortcode=' . $id . '' . $lines . ']';

                $wpdb->update($tableShortcodes, array("matching" => json_encode($data), "shortcode" => $shortcode,), array("id" => $id,), array('%s',), array('%d'));
            } else {
                $wpdb->update($tableShortcodes, array("matching" => json_encode($data),), array("id" => $id,), array('%s',), array('%d'));
            }
        } else {
            $wpdb->insert($tableShortcodes, array(
                "userId" => get_current_user_id(),
                "matching" => json_encode($data),
                "type" => $type,
            ));

            $id = $wpdb->insert_id;

            $code = '[barcode id=XXXX shortcode=' . $id . ']';
            $wpdb->update($tableShortcodes, array("shortcode" => $code,), array("id" => $id,), array('%s',), array('%d'));
        }

        $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $id), ARRAY_A);
        $shortcodes = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `type` = %s;", $type), ARRAY_A);

        uswbg_a4bJsonResponse(array(
            "shortcode" => $this->prepareShortcode($shortcode),
            "shortcodes" => $this->prepareShortcodes($shortcodes),
        ));
    }

    private function prepareShortcodes($list)
    {


        foreach ($list as &$value) {
            if ($value["matching"]) {
                $value["matching"] = json_decode($value["matching"]);

            }
        }

        return $list;
    }

    private function prepareShortcode($shortcode)
    {


        if ($shortcode["matching"]) {
            $shortcode["matching"] = json_decode($shortcode["matching"]);

        }

        return $shortcode;
    }

    public function prepareShortcodeMatching($shortcode)
    {


        if ($shortcode->matching) {
            $shortcode->matching = json_decode($shortcode->matching, true);

        }

        return $shortcode;
    }

    private function getDefaultLineBarcodeValue($generatorFieldType, $key)
    {
        if ($generatorFieldType === 'ID') {
            return array("value" => "ID", "type" => "standart");
        } else {
            return array("value" => $key, "type" => "custom");
        }
    }

    public function shortcodeRemove()
    {
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_key($_POST['id']);
        }

        $validationRules = array(
            'id' => 'required|string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        $shortcode = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $data['id']),
            ARRAY_A
        );

        $wpdb->delete($tableShortcodes, array('id' => $data['id']), array('%d'));

        if ($shortcode) {
            $shortcodes = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `type` = %s;", $shortcode["type"]),
                ARRAY_A
            );

            uswbg_a4bJsonResponse(array(
                "success" => true,
                "shortcodes" => $this->prepareShortcodes($shortcodes),
            ));
        } else {
            uswbg_a4bJsonResponse(array(
                "success" => true,
            ));
        }
    }

    public function shortcodeSetDefault()
    {
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_key($_POST['id']);
        }

        $validationRules = array(
            'id' => 'required|string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        $shortcode = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $data['id']),
            ARRAY_A
        );

        if ($shortcode) {
            $wpdb->update($tableShortcodes, array("is_default" => null), array("type" => $shortcode["type"]), array('%s'), array('%s'));

            $wpdb->update($tableShortcodes, array("is_default" => 1), array("id" => $data['id']), array('%s'), array('%d'));

            $shortcodes = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `type` = %s;", $shortcode["type"]),
                ARRAY_A
            );

            uswbg_a4bJsonResponse(array(
                "success" => true,
                "shortcodes" => $this->prepareShortcodes($shortcodes),
            ));
        } else {
            uswbg_a4bJsonResponse(array(
                "success" => true,
            ));
        }
    }

    public function shortcodeCreate()
    {
        global $wpdb;

        $post = array();
        foreach (array('id', 'name', 'type') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationRules = array(
            'id' => 'string',
            'name' => 'required|string',
            'type' => 'required|string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        $id = isset($data["id"]) ? $data["id"] : null;

        if ($id) {
            $wpdb->update($tableShortcodes, array("name" => $data["name"]), array("id" => $id), array('%s'), array('%d'));
        } else {
            $wpdb->insert($tableShortcodes, array(
                "userId" => get_current_user_id(),
                "name" => $data["name"],
                "type" => $data["type"],
            ));

            $id = $wpdb->insert_id;

            if ($id) {
                $code = '[barcode id=XXXX shortcode=' . $id . ']';;

                if ($data["type"] === "custom") {
                    $code = '[barcode code=XXXX shortcode=' . $id . ']';
                }

                $wpdb->update($tableShortcodes, array("shortcode" => $code), array("id" => $id), array('%s'), array('%d'));
            }
        }

        $shortcodes = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `type` = %s;", $data["type"]), ARRAY_A);

        if ($id) {
            $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $id), ARRAY_A);

            uswbg_a4bJsonResponse(array(
                "success" => true,
                "shortcode" => $this->prepareShortcode($shortcode),
                "shortcodes" => $this->prepareShortcodes($shortcodes),
            ));
        } else {
            uswbg_a4bJsonResponse(array(
                "success" => true,
                "shortcodes" => $this->prepareShortcodes($shortcodes),
            ));
        }
    }

    public function resetVariationsTimestamps($parentId, $shortcodeId)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT ID FROM `{$wpdb->posts}` 
            WHERE `post_status` != 'closed' 
            AND `post_type` = 'product_variation'
            AND `post_parent` = '%d';", $parentId);
        $products = $wpdb->get_results($sql, OBJECT);

        $tableFiles = $wpdb->prefix . Database::$tableFiles;

        foreach ($products as $product) {
            $wpdb->update($tableFiles, array(
                'itemTimestamp' => 0
            ), array(
                'itemId' => $product->ID,
                'shortcodeId' => $shortcodeId,
                'type' => 'product',
            ));
        }
    }

    public function generateProductsBarcodes()
    {
        global $wpdb;

        $post = array();
        foreach (array('shortcodeId', 'offset') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_key($_POST[$key]);
            }
        }

        $validationRules = array(
            'shortcodeId' => 'required|numeric',
            'offset' => 'required|numeric',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $log = array();

        $perRequest = 20;

        $params = UserSettings::getJsonSectionOption('barcodesOnProductPageParams', 'product');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        $types = array("product", "product_variation");
        $sql = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS ID, post_title, post_parent 
            FROM `{$wpdb->posts}` 
            WHERE `post_status` != %s 
            AND `post_type` in('" . implode("','", $types) . "')
            LIMIT %d OFFSET %d;", 'closed', $perRequest, $data['offset']);
        $products = $wpdb->get_results($sql, OBJECT);
        $totalProducts = $wpdb->get_row("SELECT FOUND_ROWS() AS total;");
        $totalProducts = $totalProducts->total;

        $size = ($width && $height) ? " width={$width}px height={$height}px " :  "";

        ob_start();
        foreach ($products as $product) {
            $result = do_shortcode("[barcode id=" . $product->ID . " shortcode=" . $data['shortcodeId'] . "" . $size . " _errors=return]");
            $this->formatLog($log, $result, $product);
        }
        ob_get_clean();

        $nextOffset = (int)$data['offset'] + $perRequest;

        $message = "Barcodes created %generated out of {$totalProducts} products";

        $processed = ($nextOffset > $totalProducts) ? $totalProducts : $nextOffset;

        $generated = count($products) - count($log);

        uswbg_a4bJsonResponse(array(
            "totalProducts" => $totalProducts,
            "generated" => $generated,
            "processed" => $processed,
            "offset" => $nextOffset,
            "message" => $message,
            "log" => $log
        ));
    }

    private function formatLog(&$log, $str, $product)
    {
        if (preg_match('/^<img.*$/', $str, $m)) {
            return true;
        }

        $productName = $product->post_title;

        if ($product->post_parent) {
            $productName .= " (variation)";
        }

        $log[] = array(
            'productId' => $product->ID,
            'productName' => $productName,
            'message' => $str
        );
    }

    public function generateBarcodeProductImage()
    {
        $post = array();
        foreach (array('productId', 'type') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationRules = array(
            'productId' => 'required|numeric',
            'type' => 'required|string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $params = UserSettings::getJsonSectionOption('barcodesOnProductPageParams', 'product');

        if (!$params || !isset($params['width']) || !$params['width']) return;

        $sid = isset($params['shortcode']) ? $params['shortcode'] : 1;
        $width = isset($params['width']) ? $params['width'] : null;
        $height = isset($params['height']) ? $params['height'] : null;

        $size = " width={$width}px height={$height}px ";
        $image = do_shortcode("[barcode id=" . $data['productId'] . " shortcode=" . $sid . " class=digital-barcode-embedded" . $size . "]");

        uswbg_a4bJsonResponse(array('image' => $image));
    }

    public function wcGeneralUpdated()
    {
        try {
            if (is_plugin_active('woocommerce/woocommerce.php') && isset($_POST["woocommerce_currency"])) {
                $currency = get_option('woocommerce_currency');
                $newCurrency = sanitize_text_field($_POST["woocommerce_currency"]);

                if ($currency !== $newCurrency) {
                    Files::resetAllTimestamps();
                }
            }
        } catch (\Throwable $th) {
        }
    }
}
