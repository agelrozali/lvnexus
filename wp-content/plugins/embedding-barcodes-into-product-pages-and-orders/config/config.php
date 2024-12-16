<?php

$result = array(
    'listAlgorithm' => array( 
        array('key' => 'C128', 'label' => 'CODE-128'),
        array('key' => 'C39', 'label' => 'CODE-39'),
        array('key' => 'QRCODE', 'label' => 'QRCODE'),
        array('key' => 'DATAMATRIX', 'label' => 'DataMatrix'),
        array('key' => 'EAN13', 'label' => 'EAN 13'),
        array('key' => 'EAN8', 'label' => 'EAN 8'),
        array('key' => 'UPCA', 'label' => 'UPC-A'),
        array('key' => 'UPCE', 'label' => 'UPC-E'),
    ),
    'listMessagesValidationRules' => array(
        'required' => __('No %s parameter specified.', 'wpbcu-barcode-generator'),
        'required_item' => __('Please select at least one item and try again.', 'wpbcu-barcode-generator'),
        'numeric' => __('The %s parameter is not a number.', 'wpbcu-barcode-generator'),
        'boolean' => __('The %s parameter is not a boolean.', 'wpbcu-barcode-generator'),
        'string' => __('The %s parameter is not a string.', 'wpbcu-barcode-generator'),
        'array' => __('The %s parameter is not an array.', 'wpbcu-barcode-generator'),
        'in' => __('In the %s parameter, an invalid value was specified.', 'wpbcu-barcode-generator'),
        'xml' => __('Markup should be valid XML in the %s parameter.', 'wpbcu-barcode-generator'),
        'custom_field' => __('Posts not found with custom field: %s.', 'wpbcu-barcode-generator'),
        'not_empty' => __('Field "%s" should not be empty.', 'wpbcu-barcode-generator'),
        'empty_line' => __('Empty lines not allowed in field "%s".', 'wpbcu-barcode-generator'),
        'one_shortcode_per_line' => __('Only one shortcode per line allowed in field "%s".', 'wpbcu-barcode-generator'),
    ),
    'uploads' => 'barcodes', 

    'testBarcodes' => array(
        'algorithms' => array(
            'C39' => array('short' => 'SKU-MGLASS-1234', 'long' => 'SKU-MGLASS-1234'),
            'C128' => array('short' => 'SKU-MGLASS-1234', 'long' => 'SKU-MGLASS-1234'),
            'QRCODE' => array('short' => 'SKU-MGLASS-1234', 'long' => 'http://wp4.julia-v.ukrsol.com/product/beautiful-murano-glass-vase-copy/'),
            'DATAMATRIX' => array('short' => 'SKU-MGLASS-1234', 'long' => 'http://wp4.julia-v.ukrsol.com/product/beautiful-murano-glass-vase-copy/'),
            'EAN8' => array('short' => '73127727', 'long' => '73127727'),
            'EAN13' => array('short' => '4006381333931', 'long' => '4006381333931'),
            'UPCA' => array('short' => '725272730706', 'long' => '725272730706'),
            'UPCE' => array('short' => '06141939', 'long' => '04252614'),
        ),
        'names' => array(
            'short' => 'Murano Glass vase',
            'long' => 'Beautiful Murano Glass vase from Venice, Italy - M Size, Green Color, Authenticity Certificate',
        ),
        'texts1' => array(
            'short' => 'Shipped from Murano',
            'long' => 'Shipped directly from Murano island from murano artists - genuine glass with certificates of authenticity attached',
        ),
        'texts2' => array(
            'short' => 'Check out more',
            'long' => 'Check out more details about Murano Glass on our website, subscribe to our weekly updates & more',
        ),
    ),

    'productPreviewData' => array(
        'ID' => '190198457325',
        'code' => '190198457325',
        'line1' => 'Apple iPhone X 64Gb',
        'line2' => '$799.99',
        'line3' => '190198457325',
        'line4' => 'Computers & Electronics',
    ),
    'shippingPreviewData' => array(
        'ID' => '258500',
        'code' => '258500',
        'line1' => '258500',
        'line2' => date('d M Y'),
        'line3' => 'line 3',
        'line4' => 'line 4',
        'replacements' => array(
            'cf' => array(
                '_shipping_first_name' => 'Joe',
                '_shipping_last_name' => 'Williams',
                '_shipping_address_1' => 'Fifth Avenue 12',
                '_shipping_address_2' => 'Apartment 72',
                '_shipping_city' => 'Los Angeles',
                '_shipping_postcode' => '10107',
                '_shipping_country' => 'US',
                '_shipping_country_full_name' => 'United States',
                '_shipping_state' => 'CA',
                '_shipping_state_full_name' => 'California',
                '_billing_first_name' => 'Joe',
                '_billing_last_name' => 'Williams',
                '_billing_address_1' => 'Fifth Avenue 12',
                '_billing_address_2' => 'Apartment 72',
                '_billing_city' => 'Los Angeles',
                '_billing_postcode' => '10107',
                '_billing_country' => 'US',
                '_billing_country_full_name' => 'United States',
                '_billing_state' => 'CA',
                '_billing_state_full_name' => 'California',
            ),
            'order-single-item-qty' => array(
                'order-single-item-qty' => 25,
            ),
            'main_product_image_url' => array(
                'main_product_image_url' => '',
            ),
        ),
    ),
);

return $result;
