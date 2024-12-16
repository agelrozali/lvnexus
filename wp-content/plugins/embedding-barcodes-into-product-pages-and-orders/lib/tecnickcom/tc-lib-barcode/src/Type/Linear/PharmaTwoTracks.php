<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class PharmaTwoTracks extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'PHARMA2T';

    protected function setBars()
    {
        $seq = '';
        $code = intval($this->code);

        do {
            switch ($code % 3) {
                case 0:
                    $seq .= '3';
                    $code = (($code - 3) / 3);
                    break;
                case 1:
                    $seq .= '1';
                    $code = (($code - 1) / 3);
                    break;
                case 2:
                    $seq .= '2';
                    $code = (($code - 2) / 3);
            }
        } while ($code != 0);

                $seq = strrev($seq);
        $this->ncols = 0;
        $this->nrows = 2;
        $this->bars = array();
        $len = strlen($seq);
        for ($pos = 0; $pos < $len; ++$pos) {
            switch ($seq[$pos]) {
                case '1':
                    $this->bars[] = array($this->ncols, 1, 1, 1);
                    break;
                case '2':
                    $this->bars[] = array($this->ncols, 0, 1, 1);
                    break;
                case '3':
                    $this->bars[] = array($this->ncols, 0, 1, 2);
                    break;
            }
            $this->ncols += 2;
        }
        --$this->ncols;
    }
}
