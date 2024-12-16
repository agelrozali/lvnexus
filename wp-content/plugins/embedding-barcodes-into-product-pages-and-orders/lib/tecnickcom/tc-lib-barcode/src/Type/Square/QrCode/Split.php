<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use \Com\Tecnick\Barcode\Type\Square\QrCode\ByteStream;

class Split
{
    protected $bsObj;

    protected $items = array();

    protected $version = 0;

    protected $hint = 2;

    public function __construct($bsObj, $hint, $version)
    {
        $this->bsObj = $bsObj;
        $this->items = array();
        $this->hint = $hint;
        $this->version = $version;
    }

    public function getSplittedString($data)
    {
        while (strlen($data) > 0) {
            $mode = $this->bsObj->getEncodingMode($data, 0);
            switch ($mode) {
                case Data::$encodingModes['NM']:
                    $length = $this->eatNum($data);
                    break;
                case Data::$encodingModes['AN']:
                    $length = $this->eatAn($data);
                    break;
                case Data::$encodingModes['KJ']:
                    if ($this->hint == Data::$encodingModes['KJ']) {
                        $length = $this->eatKanji($data);
                    } else {
                        $length = $this->eat8($data);
                    }
                    break;
                default:
                    $length = $this->eat8($data);
                    break;
            }
            if ($length == 0) {
                break;
            }
            if ($length < 0) {
                throw new BarcodeException('Error while splitting the input data');
            }
            $data = substr($data, $length);
        }
        return $this->items;
    }

    protected function eatNum($data)
    {
        $lng = $this->bsObj->getLengthIndicator(Data::$encodingModes['NM']);
        $pos = 0;
        while ($this->bsObj->isDigitAt($data, $pos)) {
            $pos++;
        }
        $mode = $this->bsObj->getEncodingMode($data, $pos);
        if ($mode == Data::$encodingModes['8B']) {
            $dif = $this->bsObj->estimateBitsModeNum($pos) + 4 + $lng
                + $this->bsObj->estimateBitsMode8(1)         
                - $this->bsObj->estimateBitsMode8($pos + 1); 
            if ($dif > 0) {
                return $this->eat8($data);
            }
        }
        if ($mode == Data::$encodingModes['AN']) {
            $dif = $this->bsObj->estimateBitsModeNum($pos) + 4 + $lng
                + $this->bsObj->estimateBitsModeAn(1)        
                - $this->bsObj->estimateBitsModeAn($pos + 1);
            if ($dif > 0) {
                return $this->eatAn($data);
            }
        }
        $this->items = $this->bsObj->appendNewInputItem(
            $this->items,
            Data::$encodingModes['NM'],
            $pos,
            str_split($data)
        );
        return $pos;
    }

    protected function eatAn($data)
    {
        $lag = $this->bsObj->getLengthIndicator(Data::$encodingModes['AN']);
        $lng = $this->bsObj->getLengthIndicator(Data::$encodingModes['NM']);
        $pos =1 ;
        while ($this->bsObj->isAlphanumericAt($data, $pos)) {
            if ($this->bsObj->isDigitAt($data, $pos)) {
                $qix = $pos;
                while ($this->bsObj->isDigitAt($data, $qix)) {
                    $qix++;
                }
                $dif = $this->bsObj->estimateBitsModeAn($pos) 
                    + $this->bsObj->estimateBitsModeNum($qix - $pos) + 4 + $lng
                    - $this->bsObj->estimateBitsModeAn($qix); 
                if ($dif < 0) {
                    break;
                } else {
                    $pos = $qix;
                }
            } else {
                $pos++;
            }
        }
        if (!$this->bsObj->isAlphanumericAt($data, $pos)) {
            $dif = $this->bsObj->estimateBitsModeAn($pos) + 4 + $lag
                + $this->bsObj->estimateBitsMode8(1) 
                - $this->bsObj->estimateBitsMode8($pos + 1); 
            if ($dif > 0) {
                return $this->eat8($data);
            }
        }
        $this->items = $this->bsObj->appendNewInputItem(
            $this->items,
            Data::$encodingModes['AN'],
            $pos,
            str_split($data)
        );
        return $pos;
    }

    protected function eatKanji($data)
    {
        $pos = 0;
        while ($this->bsObj->getEncodingMode($data, $pos) == Data::$encodingModes['KJ']) {
            $pos += 2;
        }
        $this->items = $this->bsObj->appendNewInputItem(
            $this->items,
            Data::$encodingModes['KJ'],
            $pos,
            str_split($data)
        );
        return $pos;
    }

    protected function eat8($data)
    {
        $lag = $this->bsObj->getLengthIndicator(Data::$encodingModes['AN']);
        $lng = $this->bsObj->getLengthIndicator(Data::$encodingModes['NM']);
        $pos = 1;
        $dataStrLen = strlen($data);
        while ($pos < $dataStrLen) {
            $mode = $this->bsObj->getEncodingMode($data, $pos);
            if ($mode == Data::$encodingModes['KJ']) {
                break;
            }
            if ($mode == Data::$encodingModes['NM']) {
                $qix = $pos;
                while ($this->bsObj->isDigitAt($data, $qix)) {
                    $qix++;
                }
                $dif = $this->bsObj->estimateBitsMode8($pos) 
                    + $this->bsObj->estimateBitsModeNum($qix - $pos) + 4 + $lng
                    - $this->bsObj->estimateBitsMode8($qix); 
                if ($dif < 0) {
                    break;
                } else {
                    $pos = $qix;
                }
            } elseif ($mode == Data::$encodingModes['AN']) {
                $qix = $pos;
                while ($this->bsObj->isAlphanumericAt($data, $qix)) {
                    $qix++;
                }
                $dif = $this->bsObj->estimateBitsMode8($pos)  
                    + $this->bsObj->estimateBitsModeAn($qix - $pos) + 4 + $lag
                    - $this->bsObj->estimateBitsMode8($qix); 
                if ($dif < 0) {
                    break;
                } else {
                    $pos = $qix;
                }
            } else {
                $pos++;
            }
        }
        $this->items = $this->bsObj->appendNewInputItem(
            $this->items,
            Data::$encodingModes['8B'],
            $pos,
            str_split($data)
        );
        return $pos;
    }
}
