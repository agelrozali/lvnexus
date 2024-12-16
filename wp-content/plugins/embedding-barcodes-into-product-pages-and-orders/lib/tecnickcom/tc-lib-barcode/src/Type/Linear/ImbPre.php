<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class ImbPre extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'IMBPRE';

    protected function setBars()
    {
        $code = strtolower($this->code);
        if (preg_match('/^[fadt]{65}$/', $code) != 1) {
            throw new BarcodeException('Invalid character sequence');
        }
        $this->ncols = 0;
        $this->nrows = 3;
        $this->bars = array();
        for ($pos = 0; $pos < 65; ++$pos) {
            switch ($code[$pos]) {
                case 'f':
                    $this->bars[] = array($this->ncols, 0, 1, 3);
                    break;
                case 'a':
                    $this->bars[] = array($this->ncols, 0, 1, 2);
                    break;
                case 'd':
                    $this->bars[] = array($this->ncols, 1, 1, 2);
                    break;
                case 't':
                    $this->bars[] = array($this->ncols, 1, 1, 1);
                    break;
            }
            $this->ncols += 2;
        }
        --$this->ncols;
    }
}
