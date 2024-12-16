<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeOneOne extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'CODE11';

    protected $chbar = array(
        '0' => '111121',
        '1' => '211121',
        '2' => '121121',
        '3' => '221111',
        '4' => '112121',
        '5' => '212111',
        '6' => '122111',
        '7' => '111221',
        '8' => '211211',
        '9' => '211111',
        '-' => '112111',
        'S' => '112211'
    );

    protected function getChecksum($code)
    {
        $len = strlen($code);
        $ptr = 1;
        $ccheck = 0;
        for ($pos = ($len - 1); $pos >= 0; --$pos) {
            $digit = $code[$pos];
            if ($digit == '-') {
                $dval = 10;
            } else {
                $dval = intval($digit);
            }
            $ccheck += ($dval * $ptr);
            ++$ptr;
            if ($ptr > 10) {
                $ptr = 1;
            }
        }
        $ccheck %= 11;
        if ($ccheck == 10) {
            $ccheck = '-';
        }
        if ($len <= 10) {
            return $ccheck;
        }
        $code .= $ccheck;
        $ptr = 1;
        $kcheck = 0;
        for ($pos = $len; $pos >= 0; --$pos) {
            $digit = $code[$pos];
            if ($digit == '-') {
                $dval = 10;
            } else {
                $dval = intval($digit);
            }
            $kcheck += ($dval * $ptr);
            ++$ptr;
            if ($ptr > 9) {
                $ptr = 1;
            }
        }
        $kcheck %= 11;
        return $ccheck.$kcheck;
    }

    protected function formatCode()
    {
        $this->extcode = 'S'.$this->code.$this->getChecksum($this->code).'S';
    }

    protected function setBars()
    {
        $this->ncols = 0;
        $this->nrows = 1;
        $this->bars = array();
        $this->formatCode();
        $clen = strlen($this->extcode);
        for ($chr = 0; $chr < $clen; ++$chr) {
            $char = $this->extcode[$chr];
            if (!isset($this->chbar[$char])) {
                throw new BarcodeException('Invalid character: chr('.ord($char).')');
            }
            for ($pos = 0; $pos < 6; ++$pos) {
                $bar_width = intval($this->chbar[$char][$pos]);
                if ((($pos % 2) == 0) && ($bar_width > 0)) {
                    $this->bars[] = array($this->ncols, 0, $bar_width, 1);
                }
                $this->ncols += $bar_width;
            }
        }
        --$this->ncols;
    }
}
