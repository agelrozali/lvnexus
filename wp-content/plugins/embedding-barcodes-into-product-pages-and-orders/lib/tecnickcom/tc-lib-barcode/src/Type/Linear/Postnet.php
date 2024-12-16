<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Postnet extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'POSTNET';

    protected $chbar = array(
        '0' => '22111',
        '1' => '11122',
        '2' => '11212',
        '3' => '11221',
        '4' => '12112',
        '5' => '12121',
        '6' => '12211',
        '7' => '21112',
        '8' => '21121',
        '9' => '21211'
    );

    protected function getChecksum($code)
    {
        $sum = 0;
        $len = strlen($code);
        for ($pos = 0; $pos < $len; ++$pos) {
            $sum += intval($code[$pos]);
        }
        $check = ($sum % 10);
        if ($check > 0) {
            $check = (10 - $check);
        }
        return $check;
    }

    protected function formatCode()
    {
        $code = preg_replace('/[-\s]+/', '', $this->code);
        $this->extcode = $code.$this->getChecksum($code);
    }

    protected function setBars()
    {
        $this->ncols = 0;
        $this->nrows = 2;
        $this->bars = array();
        $this->formatCode();
        $clen = strlen($this->extcode);
        $this->bars[] = array($this->ncols, 0, 1, 2);
        $this->ncols += 2;
        for ($chr = 0; $chr < $clen; ++$chr) {
            $char = $this->extcode[$chr];
            if (!isset($this->chbar[$char])) {
                throw new BarcodeException('Invalid character: chr('.ord($char).')');
            }
            for ($pos = 0; $pos < 5; ++$pos) {
                $bar_height = intval($this->chbar[$char][$pos]);
                $this->bars[] = array($this->ncols, floor(1 / $bar_height), 1, $bar_height);
                $this->ncols += 2;
            }
        }
        $this->bars[] = array($this->ncols, 0, 1, 2);
        ++$this->ncols;
    }
}
