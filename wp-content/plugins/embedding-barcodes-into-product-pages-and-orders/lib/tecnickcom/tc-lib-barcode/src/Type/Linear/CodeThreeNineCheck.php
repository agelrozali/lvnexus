<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeThreeNineCheck extends \Com\Tecnick\Barcode\Type\Linear\CodeThreeNineExtCheck
{
    protected $format = 'C39+';

    protected function formatCode()
    {
        $code = strtoupper($this->code);
        $this->extcode = '*'.$code.$this->getChecksum($code).'*';
    }
}
