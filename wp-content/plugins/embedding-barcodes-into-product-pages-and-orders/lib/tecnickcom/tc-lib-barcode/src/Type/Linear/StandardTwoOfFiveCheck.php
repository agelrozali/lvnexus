<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class StandardTwoOfFiveCheck extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'S25+';

    protected $chbar = array(
        '0' => '10101110111010',
        '1' => '11101010101110',
        '2' => '10111010101110',
        '3' => '11101110101010',
        '4' => '10101110101110',
        '5' => '11101011101010',
        '6' => '10111011101010',
        '7' => '10101011101110',
        '8' => '11101010111010',
        '9' => '10111010111010'
    );

    protected function getChecksum($code)
    {
        $clen = strlen($code);
        $sum = 0;
        for ($idx = 0; $idx < $clen; $idx+=2) {
            $sum += intval($code[$idx]);
        }
        $sum *= 3;
        for ($idx = 1; $idx < $clen; $idx+=2) {
            $sum += intval($code[$idx]);
        }
        $check = $sum % 10;
        if ($check > 0) {
            $check = (10 - $check);
        }
        return $check;
    }

    protected function formatCode()
    {
        $this->extcode = $this->code.$this->getChecksum($this->code);
    }

    protected function setBars()
    {
        $this->formatCode();
        if ((strlen($this->extcode) % 2) != 0) {
            $this->extcode = '0'.$this->extcode;
        }
        $seq = '1110111010';
        $clen = strlen($this->extcode);
        for ($idx = 0; $idx < $clen; ++$idx) {
            $digit = $this->extcode[$idx];
            if (!isset($this->chbar[$digit])) {
                throw new BarcodeException('Invalid character: chr('.ord($digit).')');
            }
            $seq .= $this->chbar[$digit];
        }
        $seq .= '111010111';
        $this->processBinarySequence($seq);
    }
}
