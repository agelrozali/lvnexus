<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class KlantIndex extends \Com\Tecnick\Barcode\Type\Linear\RoyalMailFourCc
{
    protected $format = 'KIX';

    protected function formatCode()
    {
        $this->extcode = strtoupper($this->code);
    }

    protected function setBars()
    {
        $this->ncols = 0;
        $this->nrows = 3;
        $this->bars = array();
        $this->getCoreBars();
    }
}
