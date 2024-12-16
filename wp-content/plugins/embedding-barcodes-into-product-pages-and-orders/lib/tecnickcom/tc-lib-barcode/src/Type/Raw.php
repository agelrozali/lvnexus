<?php

namespace Com\Tecnick\Barcode\Type;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Raw extends \Com\Tecnick\Barcode\Type
{
    protected function getCodeRows()
    {
        if (is_array($this->code)) {
            return $this->code;
        }
        $code = preg_replace('/[\s]*/s', '', $this->code);
        $code = preg_replace('/^[\[,]+/', '', $code);
        $code = preg_replace('/[\],]+$/', '', $code);
        $code = preg_replace('/[\]][\[]$/', ',', $code);
        return explode(',', $code);
    }

    protected function setBars()
    {
        $rows = $this->getCodeRows();
        if (empty($rows)) {
            throw new BarcodeException('Empty input string');
        }
        $this->nrows = count($rows);
        if (is_array($rows[0])) {
            $this->ncols = count($rows[0]);
        } else {
            $this->ncols = strlen($rows[0]);
        }
        if (empty($this->ncols)) {
            throw new BarcodeException('Empty columns');
        }
        $this->bars = array();
        foreach ($rows as $posy => $row) {
            if (!is_array($row)) {
                $row = str_split($row, 1);
            }
            $prevcol = '';
            $bar_width = 0;
            $row[] = '0';
            for ($posx = 0; $posx <= $this->ncols; ++$posx) {
                if ($row[$posx] != $prevcol) {
                    if ($prevcol == '1') {
                        $this->bars[] = array(($posx - $bar_width), $posy, $bar_width, 1);
                    }
                    $bar_width = 0;
                }
                ++$bar_width;
                $prevcol = $row[$posx];
            }
        }
    }
}
