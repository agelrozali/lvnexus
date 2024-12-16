<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Helpers\SupportedPostTypes;
use UkrSolution\BarcodesDigital\Makers\WoocommercePostsA4BarcodesMaker;

class WooCommerce
{
    public function getCategories()
    {
        Request::ajaxRequestAccess();
        $listCategories = array();

        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'hide_empty' => false,
        );

        foreach (get_categories($args) as $category) {
            $listCategories[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'countProds' => $category->category_count,
                'parent' => $category->category_parent,
            );
        }

        uswbg_a4bJsonResponse(array(
            'list' => $listCategories,
            'error' => empty($listCategories) ? array(__('No data found on request')) : array(),
            'success' => array(),
        ));
    }

    public function getBarcodes()
    {
        Request::ajaxRequestAccess();
        global $current_user;

        $customTemplatesController = new BarcodeTemplatesController();
        $activeTemplate = $customTemplatesController->getActiveTemplate();

        $post = array();
        foreach (array('format', 'withVariations', 'isImportSingleVariation', 'isUseApi', 'lineSeparator1', 'lineSeparator2', 'lineSeparator3', 'lineSeparator4', 'page', 'profileId') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        foreach (array(
            'productsCategories',
            'productsIds',
            'lineBarcode',
            'fieldLine1',
            'fieldLine2',
            'fieldLine3',
            'fieldLine4',
            'fieldSepLine1',
            'fieldSepLine2',
            'fieldSepLine3',
            'fieldSepLine4',
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        if (isset($_POST["template"])) {
            $post["template"] = stripslashes($_POST["template"]);
        }

        $validationRules = array(
            'productsCategories' => 'requiredItem:if_empty,productsIds|array|bail', 
            'productsIds' => 'requiredItem:if_empty,productsCategories|array|bail', 
            'lineBarcode' => $activeTemplate->code_match ? 'array' : 'required|array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'fieldSepLine1' => 'array',
            'fieldSepLine2' => 'array',
            'fieldSepLine3' => 'array',
            'fieldSepLine4' => 'array',
            'format' => 'required',
            'withVariations' => 'strtobool|boolean',
            'isImportSingleVariation' => 'string',
            'lineSeparator1' => 'string',
            'lineSeparator2' => 'string',
            'lineSeparator3' => 'string',
            'lineSeparator4' => 'string',
            'template' => 'html',
            'page' => 'string',
            'profileId' => 'numeric',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $postsBarcodesGenerator = new WoocommercePostsA4BarcodesMaker($data);
        $result = $postsBarcodesGenerator->make();


        if ($current_user && isset($data['page']) && $data['page'] === "template-editor") {
            if (isset($data['productsIds']) && count($data['productsIds'])) {
                \update_user_meta($current_user->ID, "usplp_product_preview", $data['productsIds'][0]);
            }
        }

        uswbg_a4bJsonResponse($result);
    }

    public function countProductsByCustomField()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        foreach (array('field', 'postType') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationOptions = array(
            'field' => 'required|string',
            'postType' => 'string',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();

        $fields = array_map('trim', explode(',', $data['field']));
        $postType = isset($data['postType']) ? $data['postType'] : "";

        $counters = array();
        foreach ($fields as $field) {
            $fieldKey = $field;

            if (empty($field)) {
                continue;
            } elseif (0 === strpos($field, 'product.')) {
                $field = substr($field, 8); 
            } elseif (0 === strpos($field, 'variation.')) {
                $field = substr($field, 10); 
            }

            if ($postType === "import-orders") {
                $postTypes = SupportedPostTypes::getCommaSeparatedString(SupportedPostTypes::WC_ORDERS);
            } elseif ($postType === "import-cf-messages") {
                $postTypes = SupportedPostTypes::getCommaSeparatedString(SupportedPostTypes::CF7);
            } elseif ($postType === "import-atum-po") {
                $postTypes = SupportedPostTypes::getCommaSeparatedString(SupportedPostTypes::ATUM);
            } else {
                $postTypes = SupportedPostTypes::getCommaSeparatedString(SupportedPostTypes::ALL);
            }


            $count = $this->getCustomFieldCount($field, $postTypes);

            if (
                $postType === "import-cf-messages"
                && $count === '0'
            ) {

                $count = $this->getCustomFieldCount('_field_' . $field, $postTypes);
            }

            $counters[$fieldKey] = $count;
        }

        uswbg_a4bJsonResponse(array('counters' => $counters));
    }

    protected function getCustomFieldCount($field, $postTypes)
    {
        global $wpdb;

        $response = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT COUNT(DISTINCT p.`ID`) as 'count'
                FROM `{$wpdb->prefix}postmeta` AS pm, `{$wpdb->prefix}posts` AS p
                WHERE pm.`meta_key` = BINARY %s
                AND pm.`post_id` = p.`ID`
                AND p.`post_type` IN($postTypes)
                ",
                array($field)
            )
        );

        return $response->count;
    }

    public function getAttributes($isAjax = true)
    {
        Request::ajaxRequestAccess();
        $wcAttributesInfo = array('wc_taxonomies' => array());

        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $wcAttributesInfo['wc_taxonomies'] = wc_get_attribute_taxonomies();
        }

        if ($isAjax === true) {
            return uswbg_a4bJsonResponse(array('wc_attributes' => $wcAttributesInfo));
        } else {
            return $wcAttributesInfo;
        }
    }

    public function getLocalAttributes()
    {
        Request::ajaxRequestAccess();
        $ids = array();
        $type = "products";
        $localAttrs = array();

        if (isset($_POST['ids']) && $_POST['ids']) {
            $ids =  USWBG_a4bRecursiveSanitizeTextField($_POST['ids']);
        }
        if (isset($_POST['type']) && $_POST['type']) {
            $type =  sanitize_text_field($_POST['type']);
        }

        if ($type === "import-products") {
            foreach ($ids as $id) {
                $order = wc_get_order($id);

                if (!empty($order)) {
                    foreach ($order->get_items() as $itemId => $itemData) {
                        $productId = $itemData->get_product()->get_ID();
                        $this->getFormatLocalAttributes($productId, $localAttrs);
                    }
                }
            }
        } else {
            foreach ($ids as $id) {
                $this->getFormatLocalAttributes($id, $localAttrs);
            }
        }

        $tempArr = array_unique(array_column($localAttrs, 'name'));
        $result = array_intersect_key($localAttrs, $tempArr);

        return uswbg_a4bJsonResponse(array('local_attributes' => $result));
    }

    private function getFormatLocalAttributes($id, &$localAttrs)
    {
        $product = get_post($id);
        $parentProductId = 'product_variation' === $product->post_type ? $product->post_parent : $id;
        $attributes = get_post_meta($parentProductId, '_product_attributes', true);

        if ($attributes) {
            foreach ($attributes as $key => $value) {
                if (!preg_match("/^pa_/", $key, $m)) {
                    $localAttrs[] = array("name" => $value["name"]);
                }
            }
        }
    }

    public function getBarcodesByOrders()
    {
        Request::ajaxRequestAccess();
        global $current_user;
        $post = array();
        foreach (array('format', 'orderQuantity', 'useStockQuantity', 'isUseApi', 'lineSeparator1', 'lineSeparator2', 'lineSeparator3', 'lineSeparator4', 'profileId', 'page') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        foreach (array(
            'ordersCategories',
            'ordersIds',
            'lineBarcode',
            'fieldLine1',
            'fieldLine2',
            'fieldLine3',
            'fieldLine4',
            'fieldSepLine1',
            'fieldSepLine2',
            'fieldSepLine3',
            'fieldSepLine4',
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        $customTemplatesController = new BarcodeTemplatesController();
        $activeTemplate = $customTemplatesController->getActiveTemplate();

        if (isset($_POST["template"])) {
            $post["template"] = stripslashes($_POST["template"]);
        }

        $validationRules = array(
            'ordersCategories' => 'requiredItem:if_empty,ordersIds|array|bail', 
            'ordersIds' => 'requiredItem:if_empty,ordersCategories|array|bail', 
            'lineBarcode' => $activeTemplate->code_match ? 'array' : 'required|array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'fieldSepLine1' => 'array',
            'fieldSepLine2' => 'array',
            'fieldSepLine3' => 'array',
            'fieldSepLine4' => 'array',
            'lineSeparator1' => 'string',
            'lineSeparator2' => 'string',
            'lineSeparator3' => 'string',
            'lineSeparator4' => 'string',
            'format' => 'required',
            'orderQuantity' => 'required',
            'useStockQuantity' => 'required',
            'template' => 'html',
            'page' => 'string',
            'profileId' => 'numeric',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $postsBarcodesGenerator = new WoocommercePostsA4BarcodesMaker($data, 'orders');
        $result = $postsBarcodesGenerator->make();


        if ($current_user && isset($data['page']) && $data['page'] === "template-editor") {
            if (isset($data['ordersIds']) && count($data['ordersIds'])) {
                \update_user_meta($current_user->ID, "usplp_product_preview", $data['ordersIds'][0]);
            }
        }

        uswbg_a4bJsonResponse($result);
    }

    public function getShippingLabelsByOrders()
    {
        $post = array();

        foreach (array('format', 'isUseApi', 'lineSeparator1', 'lineSeparator2', 'lineSeparator3', 'lineSeparator4', 'profileId') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        foreach (array(
            'ordersIds',
            'lineBarcode',
            'fieldLine1',
            'fieldLine2',
            'fieldLine3',
            'fieldLine4',
            'fieldSepLine1',
            'fieldSepLine2',
            'fieldSepLine3',
            'fieldSepLine4',
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        $validationRules = array(
            'ordersIds' => 'required|array',
            'format' => 'required',
            'lineBarcode' => 'array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'fieldSepLine1' => 'array',
            'fieldSepLine2' => 'array',
            'fieldSepLine3' => 'array',
            'fieldSepLine4' => 'array',
            'lineSeparator1' => 'string',
            'lineSeparator2' => 'string',
            'lineSeparator3' => 'string',
            'lineSeparator4' => 'string',
            'profileId' => 'numeric',
        );
        $data = Validator::create($post, $validationRules, true)->validate();
        $postsBarcodesGenerator = new WoocommercePostsA4BarcodesMaker($data, 'orders');
        $result = $postsBarcodesGenerator->make();


        uswbg_a4bJsonResponse($result);
    }

    public function getBarcodesByOrderProducts()
    {
        Request::ajaxRequestAccess();
        global $current_user;

        $post = array();
        foreach (array('format', 'orderQuantity', 'useStockQuantity', 'isUseApi', 'lineSeparator1', 'lineSeparator2', 'lineSeparator3', 'lineSeparator4', 'profileId', 'page') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        foreach (array(
            'ordersCategories',
            'ordersIds',
            'lineBarcode',
            'fieldLine1',
            'fieldLine2',
            'fieldLine3',
            'fieldLine4',
            'fieldSepLine1',
            'fieldSepLine2',
            'fieldSepLine3',
            'fieldSepLine4',
            'itemsIds',
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        $customTemplatesController = new BarcodeTemplatesController();
        $activeTemplate = $customTemplatesController->getActiveTemplate();

        $validationRules = array(
            'ordersCategories' => 'requiredItem:if_empty,ordersIds|array|bail', 
            'ordersIds' => 'requiredItem:if_empty,ordersCategories|array|bail', 
            'lineBarcode' => $activeTemplate->code_match ? 'array' : 'required|array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'fieldSepLine1' => 'array',
            'fieldSepLine2' => 'array',
            'fieldSepLine3' => 'array',
            'fieldSepLine4' => 'array',
            'itemsIds' => 'array',
            'format' => 'required',
            'orderQuantity' => 'required',
            'useStockQuantity' => 'required',
            'lineSeparator1' => 'string',
            'lineSeparator2' => 'string',
            'lineSeparator3' => 'string',
            'lineSeparator4' => 'string',
            'page' => 'string',
            'profileId' => 'numeric',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $postsBarcodesGenerator = new WoocommercePostsA4BarcodesMaker($data, 'order-products');
        $result = $postsBarcodesGenerator->make();


        if ($current_user && isset($data['page']) && $data['page'] === "template-editor") {
            if (isset($data['ordersIds']) && count($data['ordersIds'])) {
                \update_user_meta($current_user->ID, "usplp_product_preview", $data['ordersIds'][0]);
            }
        }

        uswbg_a4bJsonResponse($result);
    }
}
