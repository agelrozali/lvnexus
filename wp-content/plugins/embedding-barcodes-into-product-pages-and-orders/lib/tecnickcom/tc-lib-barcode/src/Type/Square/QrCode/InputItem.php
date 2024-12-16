<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;

abstract class InputItem extends \Com\Tecnick\Barcode\Type\Square\QrCode\Estimate
{
    public function appendNewInputItem($items, $mode, $size, $data)
    {
        $newitem = $this->newInputItem($mode, $size, $data);
        if (!empty($newitem)) {
            $items[] = $newitem;
        }
        return $items;
    }

    protected function newInputItem($mode, $size, $data, $bstream = null)
    {
        $setData = array_slice($data, 0, $size);
        if (count($setData) < $size) {
            $setData = array_merge($setData, array_fill(0, ($size - count($setData)), 0));
        }
        if (!$this->check($mode, $size, $setData)) {
            throw new BarcodeException('Invalid input item');
        }
        return array(
            'mode'    => $mode,
            'size'    => $size,
            'data'    => $setData,
            'bstream' => $bstream,
        );
    }

    protected function check($mode, $size, $data)
    {
        if ($size <= 0) {
            return false;
        }
        switch ($mode) {
            case Data::$encodingModes['NM']:
                return $this->checkModeNum($size, $data);
            case Data::$encodingModes['AN']:
                return $this->checkModeAn($size, $data);
            case Data::$encodingModes['KJ']:
                return $this->checkModeKanji($size, $data);
            case Data::$encodingModes['8B']:
                return true;
            case Data::$encodingModes['ST']:
                return true;
        }
        return false;
    }

    protected function checkModeNum($size, $data)
    {
        for ($idx = 0; $idx < $size; ++$idx) {
            if ((ord($data[$idx]) < ord('0')) || (ord($data[$idx]) > ord('9'))) {
                return false;
            }
        }
        return true;
    }

    protected function checkModeAn($size, $data)
    {
        for ($idx = 0; $idx < $size; ++$idx) {
            if ($this->lookAnTable(ord($data[$idx])) == -1) {
                return false;
            }
        }
        return true;
    }

    protected function checkModeKanji($size, $data)
    {
        if ($size & 1) {
            return false;
        }
        for ($idx = 0; $idx < $size; $idx += 2) {
            $val = (ord($data[$idx]) << 8) | ord($data[($idx + 1)]);
            if (($val < 0x8140) || (($val > 0x9ffc) && ($val < 0xe040)) || ($val > 0xebbf)) {
                return false;
            }
        }
        return true;
    }
}
