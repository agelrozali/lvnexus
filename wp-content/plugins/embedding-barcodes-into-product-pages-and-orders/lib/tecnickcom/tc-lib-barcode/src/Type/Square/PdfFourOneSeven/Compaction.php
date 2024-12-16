<?php

namespace Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven\Data;

abstract class Compaction extends \Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven\Sequence
{
    protected function processTextCompactionSub(&$txtarr, &$submode, $sub, $code, $key, $idx, $codelen)
    {
        if (((($idx + 1) == $codelen) || ((($idx + 1) < $codelen)
            && (array_search(ord($code[($idx + 1)]), Data::$textsubmodes[$submode]) !== false)))
            && (($sub == 3) || (($sub == 0) && ($submode == 1)))
        ) {
            if ($sub == 3) {
                $txtarr[] = 29;
            } else {
                $txtarr[] = 27;
            }
        } else {
            $txtarr = array_merge($txtarr, Data::$textlatch[''.$submode.$sub]);
            $submode = $sub;
        }
        $txtarr[] = $key;
    }

    protected function processTextCompaction($code, &$codewords)
    {
        $submode = 0; 
        $txtarr = array(); 
        $codelen = strlen($code);
        for ($idx = 0; $idx < $codelen; ++$idx) {
            $chval = ord($code[$idx]);
            if (($key = array_search($chval, Data::$textsubmodes[$submode])) !== false) {
                $txtarr[] = $key;
            } else {
                for ($sub = 0; $sub < 4; ++$sub) {
                    if (($sub != $submode) && (($key = array_search($chval, Data::$textsubmodes[$sub])) !== false)) {
                        $this->processTextCompactionSub($txtarr, $submode, $sub, $code, $key, $idx, $codelen);
                        break;
                    }
                }
            }
        }
        $txtarrlen = count($txtarr);
        if (($txtarrlen % 2) != 0) {
            $txtarr[] = 29;
            ++$txtarrlen;
        }
        for ($idx = 0; $idx < $txtarrlen; $idx += 2) {
            $codewords[] = (30 * $txtarr[$idx]) + $txtarr[($idx + 1)];
        }
    }

    protected function processByteCompaction($code, &$codewords)
    {
        while (($codelen = strlen($code)) > 0) {
            if ($codelen > 6) {
                $rest = substr($code, 6);
                $code = substr($code, 0, 6);
                $sublen = 6;
            } else {
                $rest = '';
                $sublen = strlen($code);
            }
            if ($sublen == 6) {
                $tdg = bcmul(''.ord($code[0]), '1099511627776');
                $tdg = bcadd($tdg, bcmul(''.ord($code[1]), '4294967296'));
                $tdg = bcadd($tdg, bcmul(''.ord($code[2]), '16777216'));
                $tdg = bcadd($tdg, bcmul(''.ord($code[3]), '65536'));
                $tdg = bcadd($tdg, bcmul(''.ord($code[4]), '256'));
                $tdg = bcadd($tdg, ''.ord($code[5]));
                $cw6 = array();
                for ($idx = 0; $idx < 5; ++$idx) {
                    $ddg = bcmod($tdg, '900');
                    $tdg = bcdiv($tdg, '900');
                    array_unshift($cw6, $ddg);
                }
                $codewords = array_merge($codewords, $cw6);
            } else {
                for ($idx = 0; $idx < $sublen; ++$idx) {
                    $codewords[] = ord($code[$idx]);
                }
            }
            $code = $rest;
        }
    }

    protected function processNumericCompaction($code, &$codewords)
    {
        while (($codelen = strlen($code)) > 0) {
            $rest = '';
            if ($codelen > 44) {
                $rest = substr($code, 44);
                $code = substr($code, 0, 44);
            }
            $tdg = '1'.$code;
            do {
                $ddg = bcmod($tdg, '900');
                $tdg = bcdiv($tdg, '900');
                array_unshift($codewords, $ddg);
            } while ($tdg != '0');
            $code = $rest;
        }
    }

    protected function getCompaction($mode, $code, $addmode = true)
    {
        $codewords = array(); 
        switch ($mode) {
            case 900:
                $this->processTextCompaction($code, $codewords);
                break;
            case 901:
            case 924:
                $this->processByteCompaction($code, $codewords);
                break;
            case 902:
                $this->processNumericCompaction($code, $codewords);
                break;
            case 913:
                $codewords[] = ord($code);
                break;
        }
        if ($addmode) {
            array_unshift($codewords, $mode);
        }
        return $codewords;
    }
}
