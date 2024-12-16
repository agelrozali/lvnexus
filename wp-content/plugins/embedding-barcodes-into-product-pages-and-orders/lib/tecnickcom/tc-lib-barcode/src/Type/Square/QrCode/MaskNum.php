<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;

abstract class MaskNum
{
    protected function makeMaskNo($maskNo, $width, $frame, &$mask)
    {
        $bnum = 0;
        $bitMask = $this->generateMaskNo($maskNo, $width, $frame);
        $mask = $frame;
        for ($ypos = 0; $ypos < $width; ++$ypos) {
            for ($xpos = 0; $xpos < $width; ++$xpos) {
                if ($bitMask[$ypos][$xpos] == 1) {
                    $mask[$ypos][$xpos] = chr(ord($frame[$ypos][$xpos]) ^ ((int)($bitMask[$ypos][$xpos])));
                }
                $bnum += (int)(ord($mask[$ypos][$xpos]) & 1);
            }
        }
        return $bnum;
    }

    protected function generateMaskNo($maskNo, $width, $frame)
    {
        $bitMask = array_fill(0, $width, array_fill(0, $width, 0));
        for ($ypos = 0; $ypos < $width; ++$ypos) {
            for ($xpos = 0; $xpos < $width; ++$xpos) {
                if (ord($frame[$ypos][$xpos]) & 0x80) {
                    $bitMask[$ypos][$xpos] = 0;
                } else {
                    $maskFunc = call_user_func(array($this, 'mask'.$maskNo), $xpos, $ypos);
                    $bitMask[$ypos][$xpos] = (($maskFunc == 0) ? 1 : 0);
                }
            }
        }
        return $bitMask;
    }

    protected function mask0($xpos, $ypos)
    {
        return (($xpos + $ypos) & 1);
    }

    protected function mask1($xpos, $ypos)
    {
        $xpos = null;
        return ($ypos & 1);
    }

    protected function mask2($xpos, $ypos)
    {
        $ypos = null;
        return ($xpos % 3);
    }

    protected function mask3($xpos, $ypos)
    {
        return (($xpos + $ypos) % 3);
    }

    protected function mask4($xpos, $ypos)
    {
        return ((((int)($ypos / 2)) + ((int)($xpos / 3))) & 1);
    }

    protected function mask5($xpos, $ypos)
    {
        return ((($xpos * $ypos) & 1) + ($xpos * $ypos) % 3);
    }

    protected function mask6($xpos, $ypos)
    {
        return (((($xpos * $ypos) & 1) + ($xpos * $ypos) % 3) & 1);
    }

    protected function mask7($xpos, $ypos)
    {
        return (((($xpos * $ypos) % 3) + (($xpos + $ypos) & 1)) & 1);
    }
}
