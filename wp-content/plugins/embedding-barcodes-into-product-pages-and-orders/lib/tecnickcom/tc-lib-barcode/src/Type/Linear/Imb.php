<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Imb extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'IMB';

    protected static $asc_chr = array(
        4,0,2,6,3,5,1,9,8,7,
        1,2,0,6,4,8,2,9,5,3,
        0,1,3,7,4,6,8,9,2,0,
        5,1,9,4,3,8,6,7,1,2,
        4,3,9,5,7,8,3,0,2,1,
        4,0,9,1,7,0,2,4,6,3,
        7,1,9,5,8
    );

    protected static $dsc_chr = array(
        7,1,9,5,8,0,2,4,6,3,
        5,8,9,7,3,0,6,1,7,4,
        6,8,9,2,5,1,7,5,4,3,
        8,7,6,0,2,5,4,9,3,0,
        1,6,8,2,0,4,5,9,6,7,
        5,2,6,3,8,5,1,9,8,7,
        4,0,2,6,3);

    protected static $asc_pos = array(
        3,0,8,11,1,12,8,11,10,6,4,12,2,7,9,6,7,9,2,8,4,0,12,7,10,9,0,7,10,5,7,9,
        6,8,2,12,1,4,2,0,1,5,4,6,12,1,0,9,4,7,5,10,2,6,9,11,2,12,6,7,5,11,0,3,2);

    protected static $dsc_pos = array(
        2,10,12,5,9,1,5,4,3,9,11,5,10,1,6,3,4,1,10,0,2,11,8,6,1,12,3,8,6,4,4,11,
        0,6,1,9,11,5,3,7,3,10,7,11,8,2,10,3,5,8,0,3,12,11,8,4,5,1,3,0,7,12,9,8,10);

    protected function getReversedUnsignedShort($num)
    {
        $rev = 0;
        for ($pos = 0; $pos < 16; ++$pos) {
            $rev <<= 1;
            $rev |= ($num & 1);
            $num >>= 1;
        }
        return $rev;
    }

    protected function getFrameCheckSequence($code_arr)
    {
        $genpoly = 0x0F35; 
        $fcs = 0x07FF; 
        $data = hexdec($code_arr[0]) << 5;
        for ($bit = 2; $bit < 8; ++$bit) {
            if (($fcs ^ $data) & 0x400) {
                $fcs = ($fcs << 1) ^ $genpoly;
            } else {
                $fcs = ($fcs << 1);
            }
            $fcs &= 0x7FF;
            $data <<= 1;
        }
        for ($byte = 1; $byte < 13; ++$byte) {
            $data = hexdec($code_arr[$byte]) << 3;
            for ($bit = 0; $bit < 8; ++$bit) {
                if (($fcs ^ $data) & 0x400) {
                    $fcs = ($fcs << 1) ^ $genpoly;
                } else {
                    $fcs = ($fcs << 1);
                }
                $fcs &= 0x7FF;
                $data <<= 1;
            }
        }
        return $fcs;
    }

    protected function getTables($type, $size)
    {
        $table = array();
        $lli = 0; 
        $lui = $size - 1; 
        for ($count = 0; $count < 8192; ++$count) {
            $bit_count = 0;
            for ($bit_index = 0; $bit_index < 13; ++$bit_index) {
                $bit_count += intval(($count & (1 << $bit_index)) != 0);
            }
            if ($bit_count == $type) {
                $reverse = ($this->getReversedUnsignedShort($count) >> 3);
                if ($reverse >= $count) {
                    if ($reverse == $count) {
                        $table[$lui] = $count;
                        --$lui;
                    } else {
                        $table[$lli] = $count;
                        ++$lli;
                        $table[$lli] = $reverse;
                        ++$lli;
                    }
                }
            }
        }
        return $table;
    }

    protected function getRoutingCode($routing_code)
    {
        switch (strlen($routing_code)) {
            case 0:
                return 0;
            case 5:
                return bcadd($routing_code, '1');
            case 9:
                return bcadd($routing_code, '100001');
            case 11:
                return bcadd($routing_code, '1000100001');
        }
        throw new BarcodeException('Invalid routing code');
    }

    protected function getCharsArray()
    {
        $this->ncols = 0;
        $this->nrows = 3;
        $this->bars = array();
        $code_arr = explode('-', $this->code);
        $tracking_number = $code_arr[0];
        $binary_code = 0;
        if (isset($code_arr[1])) {
            $binary_code = $this->getRoutingCode($code_arr[1]);
        }
        $binary_code = bcmul($binary_code, 10);
        $binary_code = bcadd($binary_code, $tracking_number[0]);
        $binary_code = bcmul($binary_code, 5);
        $binary_code = bcadd($binary_code, $tracking_number[1]);
        $binary_code .= substr($tracking_number, 2, 18);
        $binary_code = $this->convertDecToHex($binary_code);
        $binary_code = str_pad($binary_code, 26, '0', STR_PAD_LEFT);
        $binary_code_arr = chunk_split($binary_code, 2, "\r");
        $binary_code_arr = substr($binary_code_arr, 0, -1);
        $binary_code_arr = explode("\r", $binary_code_arr);
        $fcs = $this->getFrameCheckSequence($binary_code_arr);
        $first_byte = sprintf('%2s', dechex((hexdec($binary_code_arr[0]) << 2) >> 2));
        $binary_code_102bit = $first_byte.substr($binary_code, 2);
        $codewords = array();
        $data = $this->convertHexToDec($binary_code_102bit);
        $codewords[0] = bcmod($data, 636) * 2;
        $data = bcdiv($data, 636);
        for ($pos = 1; $pos < 9; ++$pos) {
            $codewords[$pos] = bcmod($data, 1365);
            $data = bcdiv($data, 1365);
        }
        $codewords[9] = $data;
        if (($fcs >> 10) == 1) {
            $codewords[9] += 659;
        }
        $table2of13 = $this->getTables(2, 78);
        $table5of13 = $this->getTables(5, 1287);
        $characters = array();
        $bitmask = 512;
        foreach ($codewords as $val) {
            if ($val <= 1286) {
                $chrcode = $table5of13[$val];
            } else {
                $chrcode = $table2of13[($val - 1287)];
            }
            if (($fcs & $bitmask) > 0) {
                $chrcode = ((~$chrcode) & 8191);
            }
            $characters[] = $chrcode;
            $bitmask /= 2;
        }

                return array_reverse($characters);
    }

    protected function setBars()
    {
        $chars = $this->getCharsArray();
        for ($pos = 0; $pos < 65; ++$pos) {
            $asc = (($chars[self::$asc_chr[$pos]] & pow(2, self::$asc_pos[$pos])) > 0);
            $dsc = (($chars[self::$dsc_chr[$pos]] & pow(2, self::$dsc_pos[$pos])) > 0);
            if ($asc and $dsc) {
                $this->bars[] = array($this->ncols, 0, 1, 3);
            } elseif ($asc) {
                $this->bars[] = array($this->ncols, 0, 1, 2);
            } elseif ($dsc) {
                $this->bars[] = array($this->ncols, 1, 1, 2);
            } else {
                $this->bars[] = array($this->ncols, 1, 1, 1);
            }
            $this->ncols += 2;
        }
        --$this->ncols;
    }
}
