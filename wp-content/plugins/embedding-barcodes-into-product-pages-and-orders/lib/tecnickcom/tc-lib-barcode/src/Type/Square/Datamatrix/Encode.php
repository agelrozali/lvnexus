<?php

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;

class Encode extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\EncodeTxt
{
    public $last_enc;

    public $shape;

    public function __construct($shape = 'S')
    {
        $this->shape = $shape;
    }

    public function encodeASCII(&$cdw, &$cdw_num, &$pos, &$data_length, &$data, &$enc)
    {
        if (($data_length > 1)
            && ($pos < ($data_length - 1))
            && ($this->isCharMode(ord($data[$pos]), Data::ENC_ASCII_NUM)
                && $this->isCharMode(ord($data[$pos + 1]), Data::ENC_ASCII_NUM)
            )
        ) {
            $cdw[] = (intval(substr($data, $pos, 2)) + 130);
            ++$cdw_num;
            $pos += 2;
        } else {
            $newenc = $this->lookAheadTest($data, $pos, $enc);
            if ($newenc != $enc) {
                $enc = $newenc;
                $cdw[] = $this->getSwitchEncodingCodeword($enc);
                ++$cdw_num;
            } else {
                $chr = ord($data[$pos]);
                ++$pos;
                if ($this->isCharMode($chr, Data::ENC_ASCII_EXT)) {
                    $cdw[] = 235;
                    $cdw[] = ($chr - 127);
                    $cdw_num += 2;
                } else {
                    $cdw[] = ($chr + 1);
                    ++$cdw_num;
                }
            }
        }
    }

    public function encodeEDFfour($epos, &$cdw, &$cdw_num, &$pos, &$data_length, &$field_length, &$enc, &$temp_cw)
    {
        if (($epos == $data_length)) {
            $enc = Data::ENC_ASCII;
            $params = Data::getPaddingSize($this->shape, ($cdw_num + $field_length));
            if (($params[11] - $cdw_num) > 2) {
                $cdw[] = $this->getSwitchEncodingCodeword($enc);
                ++$cdw_num;
            }
            return true;
        }
        if ($field_length < 4) {
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
            $params = Data::getPaddingSize($this->shape, ($cdw_num + $field_length + ($data_length - $epos)));
            if (($params[11] - $cdw_num) > 2) {
                $temp_cw[] = 0x1f;
                ++$field_length;
                for ($i = $field_length; $i < 4; ++$i) {
                    $temp_cw[] = 0;
                }
            } else {
                return true;
            }
        }
        $cdw[] = (($temp_cw[0] & 0x3F) << 2) + (($temp_cw[1] & 0x30) >> 4);
        $cdw_num++;
        if ($field_length > 1) {
            $cdw[] = (($temp_cw[1] & 0x0F) << 4) + (($temp_cw[2] & 0x3C) >> 2);
            $cdw_num++;
        }
        if ($field_length > 2) {
            $cdw[] = (($temp_cw[2] & 0x03) << 6) + ($temp_cw[3] & 0x3F);
            $cdw_num++;
        }
        $temp_cw = array();
        $pos = $epos;
        $field_length = 0;
        if ($enc == Data::ENC_ASCII) {
            return true; 
        }
        return false;
    }

    public function encodeEDF(&$cdw, &$cdw_num, &$pos, &$data_length, &$field_length, &$data, &$enc)
    {
        $temp_cw = array();
        $epos = $pos;
        $field_length = 0;
        do {
            $chr = ord($data[$epos]);
            if ($this->isCharMode($chr, Data::ENC_EDF)) {
                ++$epos;
                $temp_cw[] = $chr;
                ++$field_length;
            }
            if (($field_length == 4)
                || ($epos == $data_length)
                || !$this->isCharMode($chr, Data::ENC_EDF)
            ) {
                if ($this->encodeEDFfour($epos, $cdw, $cdw_num, $pos, $data_length, $field_length, $enc, $temp_cw)) {
                    break;
                }
            }
        } while ($epos < $data_length);
    }

    public function encodeBase256(&$cdw, &$cdw_num, &$pos, &$data_length, &$field_length, &$data, &$enc)
    {
        $temp_cw = array();
        $field_length = 0;
        while (($pos < $data_length) && ($field_length <= 1555)) {
            $newenc = $this->lookAheadTest($data, $pos, $enc);
            if ($newenc != $enc) {
                $enc = $newenc;
                break; 
            } else {
                $chr = ord($data[$pos]);
                ++$pos;
                $temp_cw[] = $chr;
                ++$field_length;
            }
        }
        if ($field_length <= 249) {
            $cdw[] = $this->get255StateCodeword($field_length, ($cdw_num + 1));
            ++$cdw_num;
        } else {
            $cdw[] = $this->get255StateCodeword((floor($field_length / 250) + 249), ($cdw_num + 1));
            $cdw[] = $this->get255StateCodeword(($field_length % 250), ($cdw_num + 2));
            $cdw_num += 2;
        }
        if (!empty($temp_cw)) {
            foreach ($temp_cw as $cht) {
                $cdw[] = $this->get255StateCodeword($cht, ($cdw_num + 1));
                ++$cdw_num;
            }
        }
    }
}
