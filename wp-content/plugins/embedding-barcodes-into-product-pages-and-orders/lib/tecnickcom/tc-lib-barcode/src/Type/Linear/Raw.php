<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Raw extends \Com\Tecnick\Barcode\Type\Raw
{
    protected $type = 'linear';

    protected $format = 'LRAW';
}
