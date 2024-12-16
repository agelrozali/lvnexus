<?php

namespace Com\Tecnick\Barcode\Type\Square;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;
use \Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode;

class Datamatrix extends \Com\Tecnick\Barcode\Type\Square
{
    protected $format = 'DATAMATRIX';

    protected $cdw = array();

    protected $grid = array();

    protected $dmx;

    protected $shape = 'S';

    protected $gsonemode = false;

    protected function setParameters()
    {
        parent::setParameters();
        if (isset($this->params[0]) && ($this->params[0] == 'R')) {
            $this->shape = 'R';
        }
        if (isset($this->params[1]) && ($this->params[1] == 'GS1')) {
            $this->gsonemode = true;
        }
    }

    protected function addPadding($size, $ncw)
    {
        if ($size <= $ncw) {
            return;
        }
        if (($this->dmx->last_enc != Data::ENC_ASCII) && ($this->dmx->last_enc != Data::ENC_BASE256)) {
            if ($this->dmx->last_enc == Data::ENC_EDF) {
                $this->cdw[] = 124;
            } else {
                $this->cdw[] = 254;
            }
            ++$ncw;
        }
        if ($size > $ncw) {
            $this->cdw[] = 129;
            ++$ncw;
            for ($i = $ncw; $i < $size; ++$i) {
                $this->cdw[] = $this->dmx->get253StateCodeword(129, $i);
            }
        }
    }

    protected function getCodewords()
    {
        if (strlen((string)$this->code) == 0) {
            throw new BarcodeException('Empty input');
        }

        $this->cdw = $this->getHighLevelEncoding($this->code);

        $ncw = count($this->cdw);

        if ($ncw > 1560) {
            throw new BarcodeException('the input is too large to fit the barcode');
        }

        $params = Data::getPaddingSize($this->shape, $ncw);
        $this->addPadding($params[11], $ncw);

        $errorCorrection = new \Com\Tecnick\Barcode\Type\Square\Datamatrix\ErrorCorrection;
        $this->cdw = $errorCorrection->getErrorCorrection($this->cdw, $params[13], $params[14], $params[15]);

        return $params;
    }

    protected function setGrid(&$idx, &$places, &$row, &$col, &$rdx, &$cdx, &$rdri, &$rdci)
    {
        if ($rdx == 0) {
            $this->grid[$row][$col] = intval(($cdx % 2) == 0);
        } elseif ($rdx == $rdri) {
            $this->grid[$row][$col] = 1;
        } elseif ($cdx == 0) {
            $this->grid[$row][$col] = 1;
        } elseif ($cdx == $rdci) {
            $this->grid[$row][$col] = intval(($rdx % 2) > 0);
        } else {
            if ($places[$idx] < 2) {
                $this->grid[$row][$col] = $places[$idx];
            } else {
                $cdw_id = (floor($places[$idx] / 10) - 1);
                $cdw_bit = pow(2, (8 - ($places[$idx] % 10)));
                $this->grid[$row][$col] = (($this->cdw[$cdw_id] & $cdw_bit) == 0) ? 0 : 1;
            }
            ++$idx;
        }
    }

    protected function getHighLevelEncoding($data)
    {
        $enc = Data::ENC_ASCII; 
        $this->dmx->last_enc = $enc; 
        $pos = 0; 
        $cdw = array(); 
        $cdw_num = 0; 
        $data_length = strlen($data); 
        while ($pos < $data_length) {
            if ($this->gsonemode && ($data[$pos] == chr(232))) {
                $cdw[] = 232;
                ++$pos;
                ++$cdw_num;
                continue;
            }
            switch ($enc) {
                case Data::ENC_ASCII:
                    $this->dmx->encodeASCII($cdw, $cdw_num, $pos, $data_length, $data, $enc);
                    break;
                case Data::ENC_C40:
                case Data::ENC_TXT:
                case Data::ENC_X12:
                    $this->dmx->encodeTXT($cdw, $cdw_num, $pos, $data_length, $data, $enc);
                    break;
                case Data::ENC_EDF:
                    $this->dmx->encodeEDF($cdw, $cdw_num, $pos, $data_length, $field_length, $data, $enc);
                    break;
                case Data::ENC_BASE256:
                    $this->dmx->encodeBase256($cdw, $cdw_num, $pos, $data_length, $field_length, $data, $enc);
                    break;
            }
            $this->dmx->last_enc = $enc;
        }
        return $cdw;
    }

    protected function setBars()
    {
        $this->dmx = new Encode($this->shape);
        $params = $this->getCodewords();
        $this->grid = array_fill(0, ($params[2] * $params[3]), 0);
        $places = $this->dmx->getPlacementMap($params[2], $params[3]);
        $this->grid = array();
        $idx = 0;
        $rdri = ($params[4] - 1);
        $rdci = ($params[5] - 1);
        for ($hr = 0; $hr < $params[8]; ++$hr) {
            for ($rdx = 0; $rdx < $params[4]; ++$rdx) {
                $row = (($hr * $params[4]) + $rdx);
                for ($vr = 0; $vr < $params[9]; ++$vr) {
                    for ($cdx = 0; $cdx < $params[5]; ++$cdx) {
                        $col = (($vr * $params[5]) + $cdx);
                        $this->setGrid($idx, $places, $row, $col, $rdx, $cdx, $rdri, $rdci);
                    }
                }
            }
        }
        $this->processBinarySequence($this->grid);
    }
}
