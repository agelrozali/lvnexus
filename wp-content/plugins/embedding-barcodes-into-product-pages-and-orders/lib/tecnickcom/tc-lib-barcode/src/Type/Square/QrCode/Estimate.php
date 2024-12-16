<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;

abstract class Estimate
{
    public function estimateBitsModeNum($size)
    {
        $wdt = (int)($size / 3);
        $bits = ($wdt * 10);
        switch ($size - ($wdt * 3)) {
            case 1:
                $bits += 4;
                break;
            case 2:
                $bits += 7;
                break;
        }
        return $bits;
    }

    public function estimateBitsModeAn($size)
    {
        $bits = (int)($size * 5.5); 
        if ($size & 1) {
            $bits += 6;
        }
        return $bits;
    }

    public function estimateBitsMode8($size)
    {
        return (int)($size * 8);
    }

    public function estimateBitsModeKanji($size)
    {
        return (int)($size * 6.5); 
    }

    public function estimateVersion($items, $level)
    {
        $version = 0;
        $prev = 0;
        do {
            $prev = $version;
            $bits = $this->estimateBitStreamSize($items, $prev);
            $version = $this->getMinimumVersion((int)(($bits + 7) / 8), $level);
            if ($version < 0) {
                return -1;
            }
        } while ($version > $prev);
        return $version;
    }

    protected function getMinimumVersion($size, $level)
    {
        for ($idx = 1; $idx <= Data::QRSPEC_VERSION_MAX; ++$idx) {
            $words = (Data::$capacity[$idx][Data::QRCAP_WORDS] - Data::$capacity[$idx][Data::QRCAP_EC][$level]);
            if ($words >= $size) {
                return $idx;
            }
        }
        throw new BarcodeException(
            'The size of input data is greater than Data::QR capacity, try to lower the error correction mode'
        );
    }

    protected function estimateBitStreamSize($items, $version)
    {
        $bits = 0;
        if ($version == 0) {
            $version = 1;
        }
        foreach ($items as $item) {
            switch ($item['mode']) {
                case Data::$encodingModes['NM']:
                    $bits = $this->estimateBitsModeNum($item['size']);
                    break;
                case Data::$encodingModes['AN']:
                    $bits = $this->estimateBitsModeAn($item['size']);
                    break;
                case Data::$encodingModes['8B']:
                    $bits = $this->estimateBitsMode8($item['size']);
                    break;
                case Data::$encodingModes['KJ']:
                    $bits = $this->estimateBitsModeKanji($item['size']);
                    break;
                case Data::$encodingModes['ST']:
                    return Data::STRUCTURE_HEADER_BITS;
                default:
                    return 0;
            }
            $len = $this->getLengthIndicator($item['mode'], $version);
            $mod = 1 << $len;
            $num = (int)(($item['size'] + $mod - 1) / $mod);
            $bits += $num * (4 + $len);
        }
        return $bits;
    }
}
