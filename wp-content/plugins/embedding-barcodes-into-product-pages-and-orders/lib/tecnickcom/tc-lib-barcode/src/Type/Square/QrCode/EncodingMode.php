<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;

abstract class EncodingMode extends \Com\Tecnick\Barcode\Type\Square\QrCode\InputItem
{
    public function getEncodingMode($data, $pos)
    {
        if (!isset($data[$pos])) {
            return Data::$encodingModes['NL'];
        }
        if ($this->isDigitAt($data, $pos)) {
            return Data::$encodingModes['NM'];
        }
        if ($this->isAlphanumericAt($data, $pos)) {
            return Data::$encodingModes['AN'];
        }
        return $this->getEncodingModeKj($data, $pos);
    }

    protected function getEncodingModeKj($data, $pos)
    {
        if (($this->hint == Data::$encodingModes['KJ']) && isset($data[($pos + 1)])) {
            $word = ((ord($data[$pos]) << 8) | ord($data[($pos + 1)]));
            if ((($word >= 0x8140) && ($word <= 0x9ffc)) || (($word >= 0xe040) && ($word <= 0xebbf))) {
                return Data::$encodingModes['KJ'];
            }
        }
        return Data::$encodingModes['8B'];
    }

    public function isDigitAt($str, $pos)
    {
        if (!isset($str[$pos])) {
            return false;
        }
        return ((ord($str[$pos]) >= ord('0')) && (ord($str[$pos]) <= ord('9')));
    }

    public function isAlphanumericAt($str, $pos)
    {
        if (!isset($str[$pos])) {
            return false;
        }
        return ($this->lookAnTable(ord($str[$pos])) >= 0);
    }

    public function lookAnTable($chr)
    {
        return (($chr > 127) ? -1 : Data::$anTable[$chr]);
    }

    public function getLengthIndicator($mode)
    {
        if ($mode == Data::$encodingModes['ST']) {
            return 0;
        }
        if ($this->version <= 9) {
            $len = 0;
        } elseif ($this->version <= 26) {
            $len = 1;
        } else {
            $len = 2;
        }
        return Data::$lengthTableBits[$mode][$len];
    }

    protected function appendBitstream($bitstream, $append)
    {
        if ((!is_array($append)) || (count($append) == 0)) {
            return $bitstream;
        }
        if (count($bitstream) == 0) {
            return $append;
        }
        return array_values(array_merge($bitstream, $append));
    }

    protected function appendNum($bitstream, $bits, $num)
    {
        if ($bits == 0) {
            return 0;
        }
        return $this->appendBitstream($bitstream, $this->newFromNum($bits, $num));
    }

    protected function appendBytes($bitstream, $size, $data)
    {
        if ($size == 0) {
            return 0;
        }
        return $this->appendBitstream($bitstream, $this->newFromBytes($size, $data));
    }

    protected function newFromNum($bits, $num)
    {
        $bstream = $this->allocate($bits);
        $mask = 1 << ($bits - 1);
        for ($idx = 0; $idx < $bits; ++$idx) {
            if ($num & $mask) {
                $bstream[$idx] = 1;
            } else {
                $bstream[$idx] = 0;
            }
            $mask = $mask >> 1;
        }
        return $bstream;
    }

    protected function newFromBytes($size, $data)
    {
        $bstream = $this->allocate($size * 8);
        $pval = 0;
        for ($idx = 0; $idx < $size; ++$idx) {
            $mask = 0x80;
            for ($jdx = 0; $jdx < 8; ++$jdx) {
                if ($data[$idx] & $mask) {
                    $bstream[$pval] = 1;
                } else {
                    $bstream[$pval] = 0;
                }
                $pval++;
                $mask = $mask >> 1;
            }
        }
        return $bstream;
    }

    protected function allocate($setLength)
    {
        return array_fill(0, $setLength, 0);
    }
}
