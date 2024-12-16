<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeOneTwoEight extends \Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight\Process
{
    protected $format = 'C128';

    protected $chbar = array(
        '212222', 
        '222122', 
        '222221', 
        '121223', 
        '121322', 
        '131222', 
        '122213', 
        '122312', 
        '132212', 
        '221213', 
        '221312', 
        '231212', 
        '112232', 
        '122132', 
        '122231', 
        '113222', 
        '123122', 
        '123221', 
        '223211', 
        '221132', 
        '221231', 
        '213212', 
        '223112', 
        '312131', 
        '311222', 
        '321122', 
        '321221', 
        '312212', 
        '322112', 
        '322211', 
        '212123', 
        '212321', 
        '232121', 
        '111323', 
        '131123', 
        '131321', 
        '112313', 
        '132113', 
        '132311', 
        '211313', 
        '231113', 
        '231311', 
        '112133', 
        '112331', 
        '132131', 
        '113123', 
        '113321', 
        '133121', 
        '313121', 
        '211331', 
        '231131', 
        '213113', 
        '213311', 
        '213131', 
        '311123', 
        '311321', 
        '331121', 
        '312113', 
        '312311', 
        '332111', 
        '314111', 
        '221411', 
        '431111', 
        '111224', 
        '111422', 
        '121124', 
        '121421', 
        '141122', 
        '141221', 
        '112214', 
        '112412', 
        '122114', 
        '122411', 
        '142112', 
        '142211', 
        '241211', 
        '221114', 
        '413111', 
        '241112', 
        '134111', 
        '111242', 
        '121142', 
        '121241', 
        '114212', 
        '124112', 
        '124211', 
        '411212', 
        '421112', 
        '421211', 
        '212141', 
        '214121', 
        '412121', 
        '111143', 
        '111341', 
        '131141', 
        '114113', 
        '114311', 
        '411113', 
        '411311', 
        '113141', 
        '114131', 
        '311141', 
        '411131', 
        '211412', 
        '211214', 
        '211232', 
        '233111', 
        '200000'  
    );

    protected $keys_a = '';

    protected $keys_b = '';

    protected $fnc_a = array(241 => 102, 242 => 97, 243 => 96, 244 => 101);

    protected $fnc_b = array(241 => 102, 242 => 97, 243 => 96, 244 => 100);

    protected function setAsciiMaps()
    {
        $this->keys_a = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_'
            .chr(0).chr(1).chr(2).chr(3).chr(4).chr(5).chr(6).chr(7).chr(8).chr(9)
            .chr(10).chr(11).chr(12).chr(13).chr(14).chr(15).chr(16).chr(17).chr(18).chr(19)
            .chr(20).chr(21).chr(22).chr(23).chr(24).chr(25).chr(26).chr(27).chr(28).chr(29)
            .chr(30).chr(31);

        $this->keys_b = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]'
            .'^_`abcdefghijklmnopqrstuvwxyz{|}~'.chr(127);
    }

    protected function getCodeData()
    {
        $code = $this->code;
        $code_data = array();
        $sequence = $this->getNumericSequence($code);
        $startid = 0;
        foreach ($sequence as $key => $seq) {
            $processMethod = 'processSequence'.$seq[0];
            $this->$processMethod($sequence, $code_data, $startid, $key, $seq);
        }
        return $this->finalizeCodeData($code_data, $startid);
    }

    protected function processSequenceA(&$sequence, &$code_data, &$startid, $key, $seq)
    {
        if ($key == 0) {
            $startid = 103;
        } elseif ($sequence[($key - 1)][0] != 'A') {
            if (($seq[2] == 1)
                && ($key > 0)
                && ($sequence[($key - 1)][0] == 'B')
                && (!isset($sequence[($key - 1)][3]))
            ) {
                $code_data[] = 98;
                $sequence[$key][3] = true;
            } elseif (!isset($sequence[($key - 1)][3])) {
                $code_data[] = 101;
            }
        }
        $this->getCodeDataA($code_data, $seq[1], $seq[2]);
    }

    protected function processSequenceB(&$sequence, &$code_data, &$startid, $key, $seq)
    {
        if ($key == 0) {
            $this->processSequenceBA($sequence, $code_data, $startid, $key, $seq);
        } elseif ($sequence[($key - 1)][0] != 'B') {
            $this->processSequenceBB($sequence, $code_data, $key, $seq);
        }
        $this->getCodeDataB($code_data, $seq[1], $seq[2]);
    }

    protected function processSequenceBA(&$sequence, &$code_data, &$startid, $key, $seq)
    {
        $tmpchr = ord($seq[1][0]);
        if (($seq[2] == 1)
            && ($tmpchr >= 241)
            && ($tmpchr <= 244)
            && isset($sequence[($key + 1)])
            && ($sequence[($key + 1)][0] != 'B')
        ) {
            switch ($sequence[($key + 1)][0]) {
                case 'A':
                    $startid = 103;
                    $sequence[$key][0] = 'A';
                    $code_data[] = $this->fnc_a[$tmpchr];
                    break;
                case 'C':
                    $startid = 105;
                    $sequence[$key][0] = 'C';
                    $code_data[] = $this->fnc_a[$tmpchr];
                    break;
            }
        } else {
            $startid = 104;
        }
    }

    protected function processSequenceBB(&$sequence, &$code_data, $key, $seq)
    {
        if (($seq[2] == 1)
            && ($key > 0)
            && ($sequence[($key - 1)][0] == 'A')
            && (!isset($sequence[($key - 1)][3]))
        ) {
            $code_data[] = 98;
            $sequence[$key][3] = true;
        } elseif (!isset($sequence[($key - 1)][3])) {
            $code_data[] = 100;
        }
    }

    protected function processSequenceC(&$sequence, &$code_data, &$startid, $key, $seq)
    {
        if ($key == 0) {
            $startid = 105;
        } elseif ($sequence[($key - 1)][0] != 'C') {
            $code_data[] = 99;
        }
        $this->getCodeDataC($code_data, $seq[1]);
    }

    protected function setBars()
    {
        $this->setAsciiMaps();
        $code_data = $this->getCodeData();
        $this->ncols = 0;
        $this->nrows = 1;
        $this->bars = array();
        foreach ($code_data as $val) {
            $seq = $this->chbar[$val];
            for ($pos = 0; $pos < 6; ++$pos) {
                $bar_width = intval($seq[$pos]);
                if ((($pos % 2) == 0) && ($bar_width > 0)) {
                    $this->bars[] = array($this->ncols, 0, $bar_width, 1);
                }
                $this->ncols += $bar_width;
            }
        }
    }
}
