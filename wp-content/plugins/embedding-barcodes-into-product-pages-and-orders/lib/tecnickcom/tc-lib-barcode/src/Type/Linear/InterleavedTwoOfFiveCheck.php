<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class InterleavedTwoOfFiveCheck extends \Com\Tecnick\Barcode\Type\Linear\StandardTwoOfFiveCheck
{
    protected $format = 'I25+';

    protected $chbar = array(
        '0' => '11221',
        '1' => '21112',
        '2' => '12112',
        '3' => '22111',
        '4' => '11212',
        '5' => '21211',
        '6' => '12211',
        '7' => '11122',
        '8' => '21121',
        '9' => '12121',
        'A' => '11',
        'Z' => '21'
    );

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
        $this->extcode = 'AA'.strtolower($this->extcode).'ZA';
        $this->ncols = 0;
        $this->nrows = 1;
        $this->bars = array();
        $clen = strlen($this->extcode);
        for ($idx = 0; $idx < $clen; $idx = ($idx + 2)) {
            $char_bar = $this->extcode[$idx];
            $char_space = $this->extcode[($idx + 1)];
            if ((!isset($this->chbar[$char_bar])) || (!isset($this->chbar[$char_space]))) {
                throw new BarcodeException('Invalid character sequence: '.$char_bar.$char_space);
            }
            $seq = '';
            $chrlen = strlen($this->chbar[$char_bar]);
            for ($pos = 0; $pos < $chrlen; ++$pos) {
                $seq .= $this->chbar[$char_bar][$pos].$this->chbar[$char_space][$pos];
            }
            $seqlen = strlen($seq);
            for ($pos = 0; $pos < $seqlen; ++$pos) {
                $bar_width = intval($seq[$pos]);
                if ((($pos % 2) == 0) && ($bar_width > 0)) {
                    $this->bars[] = array($this->ncols, 0, $bar_width, 1);
                }
                $this->ncols += $bar_width;
            }
        }
        --$this->ncols;
    }
}
