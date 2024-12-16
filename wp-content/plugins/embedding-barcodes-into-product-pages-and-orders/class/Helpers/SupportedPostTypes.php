<?php

namespace UkrSolution\BarcodesDigital\Helpers;

class SupportedPostTypes
{
    const WC_PRODUCTS = array('product', 'product_variation');
    const WC_ORDERS = array('shop_order');
    const CF7 = array('flamingo_inbound');
    const ATUM = array('atum_purchase_order');
    const ALL = array('product', 'product_variation', 'shop_order', 'flamingo_inbound');

    public static function getCommaSeparatedString($postTypesArray)
    {
        return "'" . implode("','", $postTypesArray) . "'";
    }
}
