<?php

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

abstract class Modes extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\Placement
{
    public function get253StateCodeword($cdwpad, $cdwpos)
    {
        $pad = ($cdwpad + (((149 * $cdwpos) % 253) + 1));
        if ($pad > 254) {
            $pad -= 254;
        }
        return $pad;
    }

    protected function get255StateCodeword($cdwpad, $cdwpos)
    {
        $pad = ($cdwpad + (((149 * $cdwpos) % 255) + 1));
        if ($pad > 255) {
            $pad -= 256;
        }
        return $pad;
    }

    protected function isCharMode($chr, $mode)
    {
        $map = array(
            Data::ENC_C40       => 'isC40Mode',
            Data::ENC_TXT       => 'isTXTMode',
            Data::ENC_X12       => 'isX12Mode',
            Data::ENC_EDF       => 'isEDFMode',
            Data::ENC_BASE256   => 'isBASE256Mode',
            Data::ENC_ASCII_EXT => 'isASCIIEXTMode',
            Data::ENC_ASCII_NUM => 'isASCIINUMMode'
        );
        $method = $map[$mode];
        return $this->$method($chr);
    }


    protected function isC40Mode($chr)
    {
        return (($chr == 32) || (($chr >= 48) && ($chr <= 57)) || (($chr >= 65) && ($chr <= 90)));
    }

    protected function isTXTMode($chr)
    {
        return (($chr == 32) || (($chr >= 48) && ($chr <= 57)) || (($chr >= 97) && ($chr <= 122)));
    }

    protected function isX12Mode($chr)
    {
        return (($chr == 13) || ($chr == 42) || ($chr == 62));
    }

    protected function isEDFMode($chr)
    {
        return (($chr >= 32) && ($chr <= 94));
    }

    protected function isBASE256Mode($chr)
    {
        return (($chr == 232) || ($chr == 233) || ($chr == 234) || ($chr == 241));
    }

    protected function isASCIIEXTMode($chr)
    {
        return (($chr >= 128) && ($chr <= 255));
    }

    protected function isASCIINUMMode($chr)
    {
        return (($chr >= 48) && ($chr <= 57));
    }

    protected function getMaxDataCodewords($numcw)
    {
        $mdc = 0;
        foreach (Data::$symbattr[$this->shape] as $matrix) {
            if ($matrix[11] >= $numcw) {
                $mdc = $matrix[11];
                break;
            }
        }
        return $mdc;
    }

    protected function getSwitchEncodingCodeword($mode)
    {
        $map = array(
            Data::ENC_ASCII   => 254,
            Data::ENC_C40     => 230,
            Data::ENC_TXT     => 239,
            Data::ENC_X12     => 238,
            Data::ENC_EDF     => 240,
            Data::ENC_BASE256 => 231
        );
        $cdw = $map[$mode];
        if (($cdw == 254) && ($this->last_enc == Data::ENC_EDF)) {
            $cdw = 124;
        }
        return $cdw;
    }
}
