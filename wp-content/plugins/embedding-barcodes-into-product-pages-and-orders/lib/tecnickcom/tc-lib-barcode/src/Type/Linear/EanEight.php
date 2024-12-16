<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class EanEight extends \Com\Tecnick\Barcode\Type\Linear\EanOneThree
{
    protected $format = 'EAN8';

    protected $code_length = 8;

    protected function setBars()
    {
        $this->formatCode();
        $seq = '101'; 
        $half_len = intval(ceil($this->code_length / 2));
        for ($pos = 0; $pos < $half_len; ++$pos) {
            $seq .= $this->chbar['A'][$this->extcode[$pos]];
        }
        $seq .= '01010'; 
        for ($pos = $half_len; $pos < $this->code_length; ++$pos) {
            $seq .= $this->chbar['C'][$this->extcode[$pos]];
        }
        $seq .= '101'; 
        $this->processBinarySequence($seq);
    }
}
