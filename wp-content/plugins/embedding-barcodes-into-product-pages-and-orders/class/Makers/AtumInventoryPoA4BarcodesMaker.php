<?php

namespace UkrSolution\BarcodesDigital\Makers;

use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Helpers\UserFieldsMatching;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class AtumInventoryPoA4BarcodesMaker extends WoocommercePostsA4BarcodesMakerFull
{
    protected $fieldNames = array(
        "orderStandart" => array(
            "ID" => "Order Id",
        ),
    );
    public $shortcodesToOrderCfMap = array(
        'atum-order-supplier' => '_supplier',
    );

    public function __construct($data)
    {
        parent::__construct($data, 'orders');
        $this->usersA4BarcodesMaker = new UsersA4BarcodesMaker($data, 'orders');

        $excludedProdStatusesStr = UserSettings::getOption('excludedProdStatuses', '');
        if (strlen($excludedProdStatusesStr) > 0) {
            $this->excludedProdStatusesArr = explode(',', $excludedProdStatusesStr);
        }
    }

    protected function getItems()
    {
        $pOrdersIds = isset($this->data['atumPoIds']) ? $this->data['atumPoIds'] : null;

        $args = array(
            'post_type' => 'atum_purchase_order',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'post__in' => $pOrdersIds
        );
        $query = new \WP_Query($args);

        $this->items = $query->posts;

    }

    protected function getFileOptions($post, $algorithm)
    {
        return parent::getFileOptions($post, $algorithm);
    }

    protected function getField($post, &$field, $lineNumber = "")
    {
        $value = parent::getField($post, $field, $lineNumber);

        if (!empty($value)) {
            return $value;
        }

        $fieldName = isset($this->fieldNames[$field['type']]) ? $this->fieldNames[$field['type']] : '';
        $isAddFieldName = UserSettings::getoption('fieldNameL' . $lineNumber, false);

        if (isset($this->shortcodesToOrderCfMap[$field['type']])) {
            $field['value'] = $this->shortcodesToOrderCfMap[$field['type']];
            $field['type'] = 'orderCustom';
        }

        switch ($field['type']) {
            case 'orderStandart':
                $value = $this->getStandardOrderField($post, $field['value']);
                $fieldName = isset($this->fieldNames['orderStandart'][$field['value']]) ? $this->fieldNames['orderStandart'][$field['value']] : '';
                break;
            case 'orderCustom':
                $value = $this->getCustomFieldsValues($post, $field);
                $fieldName = isset($this->fieldNames['orderCustom'][$field['value']]) ? $this->fieldNames['orderCustom'][$field['value']] : '';
                break;
            case 'atum-order-id':
                $value = $this->getStandardOrderField($post, 'ID');
                $fieldName = isset($this->fieldNames['standart']['ID']) ? $this->fieldNames['standart']['ID'] : '';
                break;
            case 'atum-order-total':
                $atumOrder = $this->getAtumOrder($post->ID);
                $value = !empty($atumOrder)
                    ?  $this->getValueWithCurrency($atumOrder->get_total_fees(), $field)
                    : '';
                break;
            case 'atum-order-status':
                $atumOrder = $this->getAtumOrder($post->ID);
                $value = !empty($atumOrder)
                    ?  get_post_status_object($atumOrder->get_status())->label
                    : '';
                break;
            case 'atum-order-date-created':
                $field['value'] = '_date_created';
                $value = date_create($this->getCustomFieldsValues($post, $field));
                $value = isset($field['args']['format']) ? $value->format($field['args']['format']) : $value->format('Y-m-d H:i:s');
                break;
            case 'atum-order-date-expected':
                $field['value'] = '_date_expected';
                $value = date_create($this->getCustomFieldsValues($post, $field));
                $value = isset($field['args']['format']) ? $value->format($field['args']['format']) : $value->format('Y-m-d H:i:s');
                break;
            case 'atum-order-cart-tax':
                $field['value'] = '_cart_tax';
                $value = $this->getValueWithCurrency($this->getCustomFieldsValues($post, $field), $field);
                break;
            case 'atum-order-discount-total':
                $field['value'] = '_discount_total';
                $value = $this->getValueWithCurrency($this->getCustomFieldsValues($post, $field), $field);
                break;
            case 'atum-order-discount-tax':
                $field['value'] = '_discount_tax';
                $value = $this->getValueWithCurrency($this->getCustomFieldsValues($post, $field), $field);
                break;
            case 'atum-order-shipping-total':
                $field['value'] = '_shipping_total';
                $value = $this->getValueWithCurrency($this->getCustomFieldsValues($post, $field), $field);
                break;
            case 'atum-order-shipping-tax':
                $field['value'] = '_shipping_tax';
                $value = $this->getValueWithCurrency($this->getCustomFieldsValues($post, $field), $field);
                break;
            case 'atum-order-total-tax':
                $field['value'] = '_total_tax';
                $value = $this->getValueWithCurrency($this->getCustomFieldsValues($post, $field), $field);
                break;
            case 'atum-order-item-meta-field':
                $value = $this->getOrderItemMetaField($post, $field);
                break;
            default:
                $value = '';
        }

        $value = UserFieldsMatching::prepareFieldValue($isAddFieldName, $fieldName, $value, $lineNumber);

        return (string) $value;
    }

    protected function getOrderItemMetaField($post, $field)
    {
        try {
            if (!property_exists($post, 'orderItem')) {
                return '';
            }

            if (metadata_exists('atum_order_item', $post->orderItem->get_id(), $field['value'])) {
                $value = get_metadata('atum_order_item', $post->orderItem->get_id(), $field['value'], true);
            }

            return !empty($value) ? $value : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getAtumOrder($postId)
    {
        return is_plugin_active('atum-stock-manager-for-woocommerce/atum-stock-manager-for-woocommerce.php')
            ? \Atum\Inc\Helpers::get_atum_order_model(absint($postId), TRUE, \Atum\PurchaseOrders\PurchaseOrders::POST_TYPE)
            : null;
    }

    protected function getStandardOrderField($post, $field)
    {
        return isset($post->{$field}) ? $post->{$field} : '';
    }

    protected function getOrderItemsProducts($ordersIds, $orderQuantity = '', $itemsIds = null)
    {
        $items = array();

        foreach ($ordersIds as $id) {
            $itemIdToProductIdMap = array();
            $quantities = array();
            $itemQuantities = array();
            $orderItems = array();
            $orderItemsByItemId = array();

            $order = $this->getAtumOrder($id);

            if (!$order) {
                continue;
            }

            foreach ($order->get_items('line_item') as $itemId => $itemData) {
                if (!$itemsIds || in_array($itemId, $itemsIds)) {
                    $product = \Atum\Inc\Helpers::get_atum_product( $itemData->get_product() );

                    if (!$product instanceof \WC_Product ) {
                        continue;
                    }

                    $productId = $product->get_ID();
                    $quantity = $itemData->get_quantity();
                    $quantities[$productId] = isset($quantities[$productId]) ? $quantities[$productId] + $quantity : $quantity;
                    $itemQuantities[$itemId] = $quantity;
                    $orderItems[$productId] = $itemData;
                    $itemIdToProductIdMap[$itemId] = $productId;
                    $orderItemsByItemId[$itemId] = $itemData;
                }
            }

            $orderProducts = uswbg_a4bGetPosts(array(
                'post_type' => array('product', 'product_variation'),
                'post__in' => empty($itemIdToProductIdMap) ? array(0) : array_values($itemIdToProductIdMap), 
                'orderby' => 'post__in',
            ));

            $orderProductsIndexedByPostId = array();
            foreach ($orderProducts as $orderProduct) {
                $orderProductsIndexedByPostId[$orderProduct->ID] = $orderProduct;
            }

            foreach ($itemIdToProductIdMap as $itemId => $productId) {
                $item = clone $orderProductsIndexedByPostId[$productId];
                $item->orderId = $id;
                $item->productInOrderQty = isset($itemQuantities[$itemId]) ? $itemQuantities[$itemId] : '';
                $item->orderItem = isset($orderItemsByItemId[$itemId]) ? $orderItemsByItemId[$itemId] : null;

                if ($orderQuantity === 'by-orders') {
                    if (isset($itemQuantities[$itemId])) {
                        for ($i = 0; $i < $itemQuantities[$itemId]; $i++) {
                            $items[] = $item;
                        }
                    }
                } else {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }
}
