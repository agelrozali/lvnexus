<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class InterleavedTwoOfFive extends \Com\Tecnick\Barcode\Type\Linear\InterleavedTwoOfFiveCheck
{
    protected $format = 'I25';

    protected function formatCode()
    {
        $this->extcode = $this->code;
    }
}
