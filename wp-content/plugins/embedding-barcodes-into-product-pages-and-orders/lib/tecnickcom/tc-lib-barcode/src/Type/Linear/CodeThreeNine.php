<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeThreeNine extends \Com\Tecnick\Barcode\Type\Linear\CodeThreeNineExtCheck
{
    protected $format = 'C39';

    protected function formatCode()
    {
        $this->extcode = '*'.strtoupper($this->code).'*';
    }
}
