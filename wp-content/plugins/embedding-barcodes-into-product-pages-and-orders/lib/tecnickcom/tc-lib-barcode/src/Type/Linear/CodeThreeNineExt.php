<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeThreeNineExt extends \Com\Tecnick\Barcode\Type\Linear\CodeThreeNineExtCheck
{
    protected $format = 'C39E';

    protected function formatCode()
    {
        $this->extcode = '*'.$this->getExtendCode(strtoupper($this->code)).'*';
    }
}
