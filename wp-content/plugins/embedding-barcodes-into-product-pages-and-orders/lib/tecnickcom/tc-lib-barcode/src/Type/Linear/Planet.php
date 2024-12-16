<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Planet extends \Com\Tecnick\Barcode\Type\Linear\Postnet
{
    protected $format = 'PLANET';

    protected $chbar = array(
        '0' => '11222',
        '1' => '22211',
        '2' => '22121',
        '3' => '22112',
        '4' => '21221',
        '5' => '21212',
        '6' => '21122',
        '7' => '12221',
        '8' => '12212',
        '9' => '12122'
    );
}
