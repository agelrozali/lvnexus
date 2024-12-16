<?php

namespace Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeOneTwoEightC extends \Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight
{
    protected $format = 'C128C';

    protected function getCodeData()
    {
        $code = $this->code;
        $code_data = array();

        $startid = 105;
        if (ord($code[0]) == 241) {
            $code_data[] = 102;
            $code = substr($code, 1);
        }
        $this->getCodeDataC($code_data, $code);
        return $this->finalizeCodeData($code_data, $startid);
    }
}
