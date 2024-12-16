<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Spec;

class Encoder extends \Com\Tecnick\Barcode\Type\Square\QrCode\Init
{
    protected $datacode = array();

    protected $ecccode = array();

    protected $blocks;

    protected $rsblocks = array(); 

    protected $count;

    protected $dataLength;

    protected $eccLength;

    protected $bv1;

    protected $width;

    protected $frame;

    protected $xpos;

    protected $ypos;

    protected $dir;

    protected $bit;

    protected $rsitems = array();

    public function encodeMask($maskNo, $datacode)
    {
        $this->datacode = $datacode;
        $spec = $this->spc->getEccSpec($this->version, $this->level, array(0, 0, 0, 0, 0));
        $this->bv1 = $this->spc->rsBlockNum1($spec);
        $this->dataLength = $this->spc->rsDataLength($spec);
        $this->eccLength = $this->spc->rsEccLength($spec);
        $this->ecccode = array_fill(0, $this->eccLength, 0);
        $this->blocks = $this->spc->rsBlockNum($spec);
        $this->init($spec);
        $this->count = 0;
        $this->width = $this->spc->getWidth($this->version);
        $this->frame = $this->spc->createFrame($this->version);
        $this->xpos = ($this->width - 1);
        $this->ypos = ($this->width - 1);
        $this->dir = -1;
        $this->bit = -1;

        for ($idx = 0; $idx < ($this->dataLength + $this->eccLength); $idx++) {
            $code = $this->getCode();
            $bit = 0x80;
            for ($jdx = 0; $jdx < 8; $jdx++) {
                $addr = $this->getNextPosition();
                $this->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                $bit = $bit >> 1;
            }
        }

        $rbits = $this->spc->getRemainder($this->version);
        for ($idx = 0; $idx < $rbits; $idx++) {
            $addr = $this->getNextPosition();
            $this->setFrameAt($addr, 0x02);
        }

        $this->runLength = array_fill(0, (Data::QRSPEC_WIDTH_MAX + 1), 0);
        if ($maskNo < 0) {
            if ($this->qr_find_best_mask) {
                $mask = $this->mask($this->width, $this->frame, $this->level);
            } else {
                $mask = $this->makeMask($this->width, $this->frame, (intval($this->qr_default_mask) % 8), $this->level);
            }
        } else {
            $mask = $this->makeMask($this->width, $this->frame, $maskNo, $this->level);
        }
        if ($mask == null) {
            throw new BarcodeException('Null Mask');
        }
        return $mask;
    }

    protected function getCode()
    {
        if ($this->count < $this->dataLength) {
            $row = ($this->count % $this->blocks);
            $col = floor($this->count / $this->blocks);
            if ($col >= $this->rsblocks[0]['dataLength']) {
                $row += $this->bv1;
            }
            $ret = $this->rsblocks[$row]['data'][$col];
        } elseif ($this->count < ($this->dataLength + $this->eccLength)) {
            $row = (($this->count - $this->dataLength) % $this->blocks);
            $col = floor(($this->count - $this->dataLength) / $this->blocks);
            $ret = $this->rsblocks[$row]['ecc'][$col];
        } else {
            return 0;
        }
        ++$this->count;
        return $ret;
    }

    protected function setFrameAt($pos, $val)
    {
        $this->frame[$pos['y']][$pos['x']] = chr($val);
    }

    protected function getNextPosition()
    {
        do {
            if ($this->bit == -1) {
                $this->bit = 0;
                return array('x' => $this->xpos, 'y' => $this->ypos);
            }
            $xpos = $this->xpos;
            $ypos = $this->ypos;
            $wdt = $this->width;
            $this->getNextPositionB($xpos, $ypos, $wdt);
            if (($xpos < 0) || ($ypos < 0)) {
                throw new BarcodeException('Error getting next position');
            }
            $this->xpos = $xpos;
            $this->ypos = $ypos;
        } while (ord($this->frame[$ypos][$xpos]) & 0x80);

        return array('x' => $xpos, 'y' => $ypos);
    }

    protected function getNextPositionB(&$xpos, &$ypos, $wdt)
    {
        if ($this->bit == 0) {
            --$xpos;
            ++$this->bit;
        } else {
            ++$xpos;
            $ypos += $this->dir;
            --$this->bit;
        }
        if ($this->dir < 0) {
            if ($ypos < 0) {
                $ypos = 0;
                $xpos -= 2;
                $this->dir = 1;
                if ($xpos == 6) {
                    --$xpos;
                    $ypos = 9;
                }
            }
        } else {
            if ($ypos == $wdt) {
                $ypos = $wdt - 1;
                $xpos -= 2;
                $this->dir = -1;
                if ($xpos == 6) {
                    --$xpos;
                    $ypos -= 8;
                }
            }
        }
    }
}
