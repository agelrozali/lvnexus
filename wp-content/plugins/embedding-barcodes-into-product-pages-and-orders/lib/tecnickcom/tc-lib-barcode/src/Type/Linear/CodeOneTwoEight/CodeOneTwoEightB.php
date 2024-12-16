<?php

namespace Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeOneTwoEightB extends \Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight
{
    protected $format = 'C128B';

    protected function getCodeData()
    {
        $code = $this->code;
        $code_data = array();
        $len = strlen($code);
        $startid = 104;
        $this->getCodeDataB($code_data, $code, $len);
        return $this->finalizeCodeData($code_data, $startid);
    }
}
