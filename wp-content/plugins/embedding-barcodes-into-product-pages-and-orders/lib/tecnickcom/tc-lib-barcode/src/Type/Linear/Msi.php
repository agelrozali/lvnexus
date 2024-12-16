<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Msi extends \Com\Tecnick\Barcode\Type\Linear\MsiCheck
{
    protected $format = 'MSI';

    protected function formatCode()
    {
        $this->extcode = $this->code;
    }
}
