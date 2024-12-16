<?php

namespace Com\Tecnick\Barcode\Type\Square;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Raw extends \Com\Tecnick\Barcode\Type\Raw
{
    protected $type = 'square';

    protected $format = 'SRAW';
}
