<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class UpcA extends \Com\Tecnick\Barcode\Type\Linear\EanOneThree
{
    protected $format = 'UPCA';

    protected $code_length = 12;

    protected function formatCode()
    {
        $code = str_pad($this->code, ($this->code_length - 1), '0', STR_PAD_LEFT);
        $code .= $this->getChecksum($code);
        ++$this->code_length;
        $this->extcode = '0'.$code;
    }
}
