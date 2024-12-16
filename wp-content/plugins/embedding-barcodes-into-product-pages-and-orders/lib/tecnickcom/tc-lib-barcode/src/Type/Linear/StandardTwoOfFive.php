<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class StandardTwoOfFive extends \Com\Tecnick\Barcode\Type\Linear\StandardTwoOfFiveCheck
{
    protected $format = 'S25';

    protected function formatCode()
    {
        $this->extcode = $this->code;
    }
}
