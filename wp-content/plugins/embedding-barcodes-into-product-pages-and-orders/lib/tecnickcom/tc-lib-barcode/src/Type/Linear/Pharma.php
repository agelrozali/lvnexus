<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Pharma extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'PHARMA';

    protected function setBars()
    {
        $seq = '';
        $code = intval($this->code);
        while ($code > 0) {
            if (($code % 2) == 0) {
                $seq .= '11100';
                $code -= 2;
            } else {
                $seq .= '100';
                $code -= 1;
            }
            $code /= 2;
        }
        $seq = substr($seq, 0, -2);
        $seq = strrev($seq);
        $this->processBinarySequence($seq);
    }
}
