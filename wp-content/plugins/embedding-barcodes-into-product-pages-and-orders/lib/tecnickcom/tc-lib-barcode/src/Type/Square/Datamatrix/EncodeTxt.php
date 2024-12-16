<?php

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;

class EncodeTxt extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\Steps
{
    public function encodeTXTC40shift(&$chr, &$enc, &$temp_cw, &$ptr)
    {
        if (isset(Data::$chset['SH1'][$chr])) {
            $temp_cw[] = 0; 
            $shiftset = Data::$chset['SH1'];
        } elseif (isset($chr, Data::$chset['SH2'][$chr])) {
            $temp_cw[] = 1; 
            $shiftset = Data::$chset['SH2'];
        } elseif (($enc == Data::ENC_C40) && isset(Data::$chset['S3C'][$chr])) {
            $temp_cw[] = 2; 
            $shiftset = Data::$chset['S3C'];
        } elseif (($enc == Data::ENC_TXT) && isset(Data::$chset['S3T'][$chr])) {
            $temp_cw[] = 2; 
            $shiftset = Data::$chset['S3T'];
        } else {
            throw new BarcodeException('Error');
        }
        $temp_cw[] = $shiftset[$chr];
        $ptr += 2;
    }

    public function encodeTXTC40(&$data, &$enc, &$temp_cw, &$ptr, &$epos, &$charset)
    {
        $chr = ord($data[$epos]);
        ++$epos;
        if ($chr & 0x80) {
            if ($enc == Data::ENC_X12) {
                throw new BarcodeException('TXTC40 Error');
            }
            $chr = ($chr & 0x7f);
            $temp_cw[] = 1; 
            $temp_cw[] = 30; 
            $ptr += 2;
        }
        if (isset($charset[$chr])) {
            $temp_cw[] = $charset[$chr];
            ++$ptr;
        } else {
            $this->encodeTXTC40shift($chr, $enc, $temp_cw, $ptr);
        }
        return $chr;
    }

    public function encodeTXTC40last($chr, &$cdw, &$cdw_num, &$enc, &$temp_cw, &$ptr, &$epos)
    {
        $cdwr = ($this->getMaxDataCodewords($cdw_num + $ptr) - $cdw_num);
        if (($cdwr == 1) && ($ptr == 1)) {
            $cdw[] = ($chr + 1);
            ++$cdw_num;
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
        } elseif (($cdwr == 2) && ($ptr == 1)) {
            $cdw[] = 254;
            $cdw[] = ($chr + 1);
            $cdw_num += 2;
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
        } elseif (($cdwr == 2) && ($ptr == 2)) {
            $ch1 = array_shift($temp_cw);
            $ch2 = array_shift($temp_cw);
            $ptr -= 2;
            $tmp = ((1600 * $ch1) + (40 * $ch2) + 1);
            $cdw[] = ($tmp >> 8);
            $cdw[] = ($tmp % 256);
            $cdw_num += 2;
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
        } else {
            if ($enc != Data::ENC_ASCII) {
                $enc = Data::ENC_ASCII;
                $this->last_enc = $enc;
                $cdw[] = $this->getSwitchEncodingCodeword($enc);
                ++$cdw_num;
                $epos -= $ptr;
            }
        }
    }

    public function encodeTXT(&$cdw, &$cdw_num, &$pos, &$data_length, &$data, &$enc)
    {
        $temp_cw = array();
        $ptr = 0;
        $epos = $pos;
        $set_id = Data::$chset_id[$enc];
        $charset = Data::$chset[$set_id];
        do {
            $chr = $this->encodeTXTC40($data, $enc, $temp_cw, $ptr, $epos, $charset);
            if ($ptr >= 3) {
                $ch1 = array_shift($temp_cw);
                $ch2 = array_shift($temp_cw);
                $ch3 = array_shift($temp_cw);
                $ptr -= 3;
                $tmp = ((1600 * $ch1) + (40 * $ch2) + $ch3 + 1);
                $cdw[] = ($tmp >> 8);
                $cdw[] = ($tmp % 256);
                $cdw_num += 2;
                $pos = $epos;
                $newenc = $this->lookAheadTest($data, $pos, $enc);
                if ($newenc != $enc) {
                    $enc = $newenc;
                    if ($enc != Data::ENC_ASCII) {
                        $cdw[] = $this->getSwitchEncodingCodeword(Data::ENC_ASCII);
                        ++$cdw_num;
                    }
                    $cdw[] = $this->getSwitchEncodingCodeword($enc);
                    ++$cdw_num;
                    $pos -= $ptr;
                    $ptr = 0;
                    break;
                }
            }
        } while (($ptr > 0) && ($epos < $data_length));
        if ($ptr > 0) {
            $this->encodeTXTC40last($chr, $cdw, $cdw_num, $enc, $temp_cw, $ptr, $epos);
            $pos = $epos;
        }
    }
}
