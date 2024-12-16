<?php

namespace UkrSolution\BarcodesDigital\BarcodeTemplates;

use UkrSolution\BarcodesDigital\Helpers\Variables;

class BarcodeTemplate
{
    protected $patterns = array(
        '/\[barcode_img_url]/',
        '/\[2dcode_img_url]/',
        '/\[product_image_url]/',
        '/\[line1]/',
        '/\[line2]/',
        '/\[line3]/',
        '/\[line4]/',
        '/\[field=ID]/',
        '/\[cf=_regular_price]/',
        '/\[cf=_price]/',
        '/\[attr=Size]/',
        '/\[attr=Color]/',
    );

    private $replacements = array();

    public function __construct($object)
    {
        $this->replacements = array(
            Variables::$A4B_PLUGIN_BASE_URL . 'assets/img/example_barcode1d.svg',
            Variables::$A4B_PLUGIN_BASE_URL . 'assets/img/example_barcode2d.svg',
            Variables::$A4B_PLUGIN_BASE_URL . 'assets/img/product-img1.png',
            '190198457325',
            'Apple iPhone X 64Gb',
            '799.99 $',
            'Computers & Electronics',
            '123',
            '100.00 $',
            '89.99 $',
            'XL',
            'Green',
        );

        $this->mergeWith($object);
    }

    public function getPreview()
    {
        $preview = preg_replace($this->patterns, $this->replacements, $this->template);

        return $preview;
    }

    protected function mergeWith($object)
    {
        if (is_array($object) || is_object($object)) {
            foreach ($object as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
