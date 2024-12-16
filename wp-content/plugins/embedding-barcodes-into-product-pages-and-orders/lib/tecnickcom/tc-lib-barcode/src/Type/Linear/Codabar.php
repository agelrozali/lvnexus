<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Codabar extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'CODABAR';

    protected $chbar = array(
        '0' => '11111221',
        '1' => '11112211',
        '2' => '11121121',
        '3' => '22111111',
        '4' => '11211211',
        '5' => '21111211',
        '6' => '12111121',
        '7' => '12112111',
        '8' => '12211111',
        '9' => '21121111',
        '-' => '11122111',
        '$' => '11221111',
        ':' => '21112121',
        '/' => '21211121',
        '.' => '21212111',
        '+' => '11222221',
        'A' => '11221211',
        'B' => '12121121',
        'C' => '11121221',
        'D' => '11122211'
    );

    protected function formatCode()
    {
        $this->extcode = 'A'.strtoupper($this->code).'A';
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
            for ($pos = 0; $pos < 8; ++$pos) {
                $bar_width = intval($this->chbar[$char][$pos]);
                if (($pos % 2) == 0) {
                    $this->bars[] = array($this->ncols, 0, $bar_width, 1);
                }
                $this->ncols += $bar_width;
            }
        }
        --$this->ncols;
    }
}
