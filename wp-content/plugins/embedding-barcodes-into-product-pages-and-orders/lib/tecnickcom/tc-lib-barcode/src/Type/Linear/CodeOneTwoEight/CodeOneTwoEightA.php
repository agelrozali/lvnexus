<?php

namespace Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeOneTwoEightA extends \Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight
{
    protected $format = 'C128A';

    protected function getCodeData()
    {
        $code = $this->code;
        $code_data = array();
        $len = strlen($code);
        $startid = 103;
        $this->getCodeDataA($code_data, $code, $len);
        return $this->finalizeCodeData($code_data, $startid);
    }
}
