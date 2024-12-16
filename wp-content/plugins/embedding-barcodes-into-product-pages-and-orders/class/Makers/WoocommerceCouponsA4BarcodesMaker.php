<?php

namespace UkrSolution\BarcodesDigital\Makers;

use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Helpers\UserFieldsMatching;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class WoocommerceCouponsA4BarcodesMaker extends GeneralPostsA4BarcodesMaker
{
    protected $fieldNames = array(
        "standart" => array(
            "ID" => "Coupon Id",
        ),
        'coupon-code' => 'Coupon code',
        'coupon-expire-date' => 'Coupon expire date',
        'coupon-creation-date' => 'Coupon creation date',
        'coupon-type' => 'Coupon type',
        'coupon-amount' => 'Coupon amount',
    );

    protected function getItems()
    {
        $couponsIds = isset($this->data['couponsIds']) ? $this->data['couponsIds'] : null;

        $args = array(
            'post_type' => 'shop_coupon',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'post__in' => $couponsIds
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

        switch ($field['type']) {
            case 'coupon-code':
                $field['value'] = 'post_title';
                $value = $this->getStandardPostField($post, $field['value']);
                $fieldName = isset($this->fieldNames['standart'][$field['value']]) ? $this->fieldNames['standart'][$field['value']] : '';
                break;
            case 'coupon-expire-date':
                $value = $this->getCouponExpireDate($post, $field);
                break;
            case 'coupon-creation-date':
                $value = $this->getCreationDate($post);
                break;
            case 'coupon-type':
                $value = $this->getCouponType($post, $field);
                break;
            case 'coupon-amount':
                $field['value'] = 'coupon_amount';
                $value = $this->getCustomFieldsValues($post, $field);
                break;
            default:
                $value = '';
        }

        $value = UserFieldsMatching::prepareFieldValue($isAddFieldName, $fieldName, $value, $lineNumber);

        return (string) $value;
    }

    protected function getCouponExpireDate($post, $field)
    {
        $format = isset($field['args']['format']) ? $field['args']['format'] : get_option('date_format');

        return wp_date($format, get_post_meta($post->ID, 'date_expires', true));
    }

    protected function getCouponType($post, $field)
    {
        $coupon = new \WC_Coupon( $post->post_title );

        return wc_get_coupon_type($coupon->get_discount_type());
    }
}
