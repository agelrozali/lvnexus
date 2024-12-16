<?php

namespace Com\Tecnick\Barcode\Type\Square;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven\Data;

class PdfFourOneSeven extends \Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven\Compaction
{
    protected $format = 'PDF417';

    protected $row_height = 2;

    protected $quiet_vertical = 2;

    protected $quiet_horizontal = 2;

    protected $aspectratio = 2;

    protected $ecl = -1;

    protected $macro = array();

    protected function setParameters()
    {
        parent::setParameters();
        if (!empty($this->params[0]) && (($aspectratio = floatval($this->params[0])) >= 1)) {
            $this->aspectratio = $aspectratio;
        }
        if (isset($this->params[1]) && (($ecl = intval($this->params[1])) >= 0) && ($ecl <= 8)) {
            $this->ecl = $ecl;
        }
        $this->setMacroBlockParam();
    }

    protected function setMacroBlockParam()
    {
        if (isset($this->params[4])
            && ($this->params[2] !== '')
            && ($this->params[3] !== '')
            && ($this->params[4] !== '')
        ) {
            $this->macro['segment_total'] = intval($this->params[2]);
            $this->macro['segment_index'] = intval($this->params[3]);
            $this->macro['file_id'] = strtr($this->params[4], "\xff", ',');
            for ($idx = 0; $idx < 7; ++$idx) {
                $opt = $idx + 5;
                if (isset($this->params[$opt]) && ($this->params[$opt] !== '')) {
                    $this->macro['option_'.$idx] = strtr($this->params[$opt], "\xff", ',');
                }
            }
        }
    }

    protected function setBars()
    {
        if (strlen((string)$this->code) == 0) {
            throw new BarcodeException('Empty input');
        }
        $barcode = $this->getBinSequence();
        $this->processBinarySequence($barcode);
    }

    protected function getMacroBlock(&$numcw)
    {
        if (empty($this->macro)) {
            return array();
        }
        $macrocw = array();
        $macrocw[] = 928;
        $cdw = $this->getCompaction(902, sprintf('%05d', $this->macro['segment_index']), false);
        $macrocw = array_merge($macrocw, $cdw);
        $cdw = $this->getCompaction(900, $this->macro['file_id'], false);
        $macrocw = array_merge($macrocw, $cdw);
        $optmodes = array(900,902,902,900,900,902,902);
        $optsize = array(-1,2,4,-1,-1,-1,2);
        foreach ($optmodes as $key => $omode) {
            if (isset($this->macro['option_'.$key])) {
                $macrocw[] = 923;
                $macrocw[] = $key;
                if ($optsize[$key] == 2) {
                    $this->macro['option_'.$key] = sprintf('%05d', $this->macro['option_'.$key]);
                } elseif ($optsize[$key] == 4) {
                    $this->macro['option_'.$key] = sprintf('%010d', $this->macro['option_'.$key]);
                }
                $cdw = $this->getCompaction($omode, $this->macro['option_'.$key], false);
                $macrocw = array_merge($macrocw, $cdw);
            }
        }
        if ($this->macro['segment_index'] == ($this->macro['segment_total'] - 1)) {
            $macrocw[] = 922;
        }
        $numcw += count($macrocw);
        return $macrocw;
    }

    public function getCodewords(&$rows, &$cols, &$ecl)
    {
        $codewords = array(); 
        $sequence = $this->getInputSequences($this->code);
        foreach ($sequence as $seq) {
            $cws = $this->getCompaction($seq[0], $seq[1], true);
            $codewords = array_merge($codewords, $cws);
        }
        if ($codewords[0] == 900) {
            array_shift($codewords);
        }
        $numcw = count($codewords);
        if ($numcw > 925) {
            throw new BarcodeException('The maximum codeword capaciy has been reached: '.$numcw.' > 925');
        }
        $macrocw = $this->getMacroBlock($numcw);
        $ecl = $this->getErrorCorrectionLevel($this->ecl, $numcw);
        $errsize = (2 << $ecl);
        $nce = ($numcw + $errsize + 1);
        $cols = min(30, max(1, round((sqrt(4761 + (68 * $this->aspectratio * $this->row_height * $nce)) - 69) / 34)));
        $rows = min(90, max(3, ceil($nce / $cols)));
        $size = ($cols * $rows);
        if ($size > 928) {
            if (abs($this->aspectratio - (17 * 29 / 32)) < abs($this->aspectratio - (17 * 16 / 58))) {
                $cols = 29;
                $rows = 32;
            } else {
                $cols = 16;
                $rows = 58;
            }
            $size = 928;
        }
        $pad = ($size - $nce);
        if ($pad > 0) {
            $codewords = array_merge($codewords, array_fill(0, $pad, 900));
        }
        if (!empty($macrocw)) {
            $codewords = array_merge($codewords, $macrocw);
        }
        $sld = ($size - $errsize);
        array_unshift($codewords, $sld);
        $ecw = $this->getErrorCorrection($codewords, $ecl);
        return array_merge($codewords, $ecw);
    }

    public function getBinSequence()
    {
        $rows = 0;
        $cols = 0;
        $ecl = 0;
        $codewords = $this->getCodewords($rows, $cols, $ecl);
        $barcode = '';
        $pstart = str_repeat('0', $this->quiet_horizontal).Data::$start_pattern;
        $this->nrows = ($rows * $this->row_height) + (2 * $this->quiet_vertical);
        $this->ncols = (($cols + 2) * 17) + 35 + (2 * $this->quiet_horizontal);
        $empty_row = ','.str_repeat('0', $this->ncols);
        $empty_rows = str_repeat($empty_row, $this->quiet_vertical);
        $barcode .= $empty_rows;
        $kcw = 0; 
        $cid = 0; 
        for ($rix = 0; $rix < $rows; ++$rix) {
            $row = $pstart;
            switch ($cid) {
                case 0:
                    $rval = ((30 * intval($rix / 3)) + intval(($rows - 1) / 3));
                    $cval = ((30 * intval($rix / 3)) + ($cols - 1));
                    break;
                case 1:
                    $rval = ((30 * intval($rix / 3)) + ($ecl * 3) + (($rows - 1) % 3));
                    $cval = ((30 * intval($rix / 3)) + intval(($rows - 1) / 3));
                    break;
                case 2:
                    $rval = ((30 * intval($rix / 3)) + ($cols - 1));
                    $cval = ((30 * intval($rix / 3)) + ($ecl * 3) + (($rows - 1) % 3));
                    break;
            }
            $row .= sprintf('%17b', Data::$clusters[$cid][$rval]);
            for ($cix = 0; $cix < $cols; ++$cix) {
                $row .= sprintf('%17b', Data::$clusters[$cid][$codewords[$kcw]]);
                ++$kcw;
            }
            $row .= sprintf('%17b', Data::$clusters[$cid][$cval]);
            $row .= Data::$stop_pattern.str_repeat('0', $this->quiet_horizontal);
            $brow = ','.str_repeat($row, $this->row_height);
            $barcode .= $brow;
            ++$cid;
            if ($cid > 2) {
                $cid = 0;
            }
        }
        $barcode .= $empty_rows;
        return $barcode;
    }
}
