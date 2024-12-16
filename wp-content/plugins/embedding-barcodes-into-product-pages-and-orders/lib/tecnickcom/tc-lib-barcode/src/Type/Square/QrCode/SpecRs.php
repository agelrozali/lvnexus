<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;

abstract class SpecRs
{
    public function rsBlockNum($spec)
    {
        return ($spec[0] + $spec[3]);
    }

    public function rsBlockNum1($spec)
    {
        return $spec[0];
    }

    public function rsDataCodes1($spec)
    {
        return $spec[1];
    }

    public function rsEccCodes1($spec)
    {
        return $spec[2];
    }

    public function rsBlockNum2($spec)
    {
        return $spec[3];
    }

    public function rsDataCodes2($spec)
    {
        return $spec[4];
    }

    public function rsEccCodes2($spec)
    {
        return $spec[2];
    }

    public function rsDataLength($spec)
    {
        return ($spec[0] * $spec[1]) + ($spec[3] * $spec[4]);
    }

    public function rsEccLength($spec)
    {
        return ($spec[0] + $spec[3]) * $spec[2];
    }

    public function createFrame($version)
    {
        $width = Data::$capacity[$version][Data::QRCAP_WIDTH];
        $frameLine = str_repeat("\0", $width);
        $frame = array_fill(0, $width, $frameLine);
        $frame = $this->putFinderPattern($frame, 0, 0);
        $frame = $this->putFinderPattern($frame, $width - 7, 0);
        $frame = $this->putFinderPattern($frame, 0, $width - 7);
        $yOffset = $width - 7;
        for ($ypos = 0; $ypos < 7; ++$ypos) {
            $frame[$ypos][7] = "\xc0";
            $frame[$ypos][$width - 8] = "\xc0";
            $frame[$yOffset][7] = "\xc0";
            ++$yOffset;
        }
        $setPattern = str_repeat("\xc0", 8);
        $frame = $this->qrstrset($frame, 0, 7, $setPattern);
        $frame = $this->qrstrset($frame, $width-8, 7, $setPattern);
        $frame = $this->qrstrset($frame, 0, $width - 8, $setPattern);
        $setPattern = str_repeat("\x84", 9);
        $frame = $this->qrstrset($frame, 0, 8, $setPattern);
        $frame = $this->qrstrset($frame, $width - 8, 8, $setPattern, 8);
        $yOffset = $width - 8;
        for ($ypos = 0; $ypos < 8; ++$ypos, ++$yOffset) {
            $frame[$ypos][8] = "\x84";
            $frame[$yOffset][8] = "\x84";
        }
        $wdo = $width - 15;
        for ($idx = 1; $idx < $wdo; ++$idx) {
            $frame[6][(7 + $idx)] = chr(0x90 | ($idx & 1));
            $frame[(7 + $idx)][6] = chr(0x90 | ($idx & 1));
        }
        $frame = $this->putAlignmentPattern($version, $frame, $width);
        if ($version >= 7) {
            $vinf = $this->getVersionPattern($version);
            $val = $vinf;
            for ($xpos = 0; $xpos < 6; ++$xpos) {
                for ($ypos = 0; $ypos < 3; ++$ypos) {
                    $frame[(($width - 11) + $ypos)][$xpos] = chr(0x88 | ($val & 1));
                    $val = $val >> 1;
                }
            }
            $val = $vinf;
            for ($ypos = 0; $ypos < 6; ++$ypos) {
                for ($xpos = 0; $xpos < 3; ++$xpos) {
                    $frame[$ypos][($xpos + ($width - 11))] = chr(0x88 | ($val & 1));
                    $val = $val >> 1;
                }
            }
        }
        $frame[$width - 8][8] = "\x81";
        return $frame;
    }
}
