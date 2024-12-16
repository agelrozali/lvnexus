<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Estimate;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Spec;

class ByteStream extends \Com\Tecnick\Barcode\Type\Square\QrCode\Encode
{
    protected $hint = 2;

    public $version = 0;

    protected $level = 0;

    public function __construct($hint, $version, $level)
    {
        $this->hint = $hint;
        $this->version = $version;
        $this->level = $level;
    }

    public function getByteStream($items)
    {
        return $this->bitstreamToByte(
            $this->appendPaddingBit(
                $this->mergeBitStream($items)
            )
        );
    }

    protected function bitstreamToByte($bstream)
    {
        $size = count($bstream);
        if ($size == 0) {
            return array();
        }
        $data = array_fill(0, (int)(($size + 7) / 8), 0);
        $bytes = (int)($size / 8);
        $pos = 0;
        for ($idx = 0; $idx < $bytes; ++$idx) {
            $val = 0;
            for ($jdx = 0; $jdx < 8; ++$jdx) {
                $val = $val << 1;
                $val |= $bstream[$pos];
                $pos++;
            }
            $data[$idx] = $val;
        }
        if ($size & 7) {
            $val = 0;
            for ($jdx = 0; $jdx < ($size & 7); ++$jdx) {
                $val = $val << 1;
                $val |= $bstream[$pos];
                $pos++;
            }
            $data[$bytes] = $val;
        }
        return $data;
    }

    protected function mergeBitStream($items)
    {
        $items = $this->convertData($items);
        $bstream = array();
        foreach ($items as $item) {
            $bstream = $this->appendBitstream($bstream, $item['bstream']);
        }
        return $bstream;
    }

    protected function convertData($items)
    {
        $ver = $this->estimateVersion($items, $this->level);
        if ($ver > $this->version) {
            $this->version = $ver;
        }
        while (true) {
            $cbs = $this->createBitStream($items);
            $items = $cbs[0];
            $bits = $cbs[1];
            if ($bits < 0) {
                throw new BarcodeException('Negative Bits value');
            }
            $ver = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
            if ($ver > $this->version) {
                $this->version = $ver;
            } else {
                break;
            }
        }
        return $items;
    }

    protected function createBitStream($items)
    {
        $total = 0;
        foreach ($items as $key => $item) {
            $items[$key] = $this->encodeBitStream($item, $this->version);
            $bits = count($items[$key]['bstream']);
            $total += $bits;
        }
        return array($items, $total);
    }

    public function encodeBitStream($inputitem, $version)
    {
        $inputitem['bstream'] = array();
        $specObj = new Spec;
        $words = $specObj->maximumWords($inputitem['mode'], $version);
        if ($inputitem['size'] > $words) {
            $st1 = $this->newInputItem($inputitem['mode'], $words, $inputitem['data']);
            $st2 = $this->newInputItem(
                $inputitem['mode'],
                ($inputitem['size'] - $words),
                array_slice($inputitem['data'], $words)
            );
            $st1 = $this->encodeBitStream($st1, $version);
            $st2 = $this->encodeBitStream($st2, $version);
            $inputitem['bstream'] = array();
            $inputitem['bstream'] = $this->appendBitstream($inputitem['bstream'], $st1['bstream']);
            $inputitem['bstream'] = $this->appendBitstream($inputitem['bstream'], $st2['bstream']);
        } else {
            switch ($inputitem['mode']) {
                case Data::$encodingModes['NM']:
                    $inputitem = $this->encodeModeNum($inputitem, $version);
                    break;
                case Data::$encodingModes['AN']:
                    $inputitem = $this->encodeModeAn($inputitem, $version);
                    break;
                case Data::$encodingModes['8B']:
                    $inputitem = $this->encodeMode8($inputitem, $version);
                    break;
                case Data::$encodingModes['KJ']:
                    $inputitem = $this->encodeModeKanji($inputitem, $version);
                    break;
                case Data::$encodingModes['ST']:
                    $inputitem = $this->encodeModeStructure($inputitem);
                    break;
            }
        }
        return $inputitem;
    }

    protected function appendPaddingBit($bstream)
    {
        if (is_null($bstream)) {
            return null;
        }
        $bits = count($bstream);
        $specObj = new Spec;
        $maxwords = $specObj->getDataLength($this->version, $this->level);
        $maxbits = $maxwords * 8;
        if ($maxbits == $bits) {
            return $bstream;
        }
        if ($maxbits - $bits < 5) {
            return $this->appendNum($bstream, $maxbits - $bits, 0);
        }
        $bits += 4;
        $words = (int)(($bits + 7) / 8);
        $padding = array();
        $padding = $this->appendNum($padding, $words * 8 - $bits + 4, 0);
        $padlen = $maxwords - $words;
        if ($padlen > 0) {
            $padbuf = array();
            for ($idx = 0; $idx < $padlen; ++$idx) {
                $padbuf[$idx] = (($idx & 1) ? 0x11 : 0xec);
            }
            $padding = $this->appendBytes($padding, $padlen, $padbuf);
        }
        return $this->appendBitstream($bstream, $padding);
    }
}
