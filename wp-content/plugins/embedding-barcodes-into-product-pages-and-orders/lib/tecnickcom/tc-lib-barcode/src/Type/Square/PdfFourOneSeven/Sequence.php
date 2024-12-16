<?php

namespace Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven\Data;

abstract class Sequence extends \Com\Tecnick\Barcode\Type\Square
{
    protected function getErrorCorrectionLevel($ecl, $numcw)
    {
        $maxecl = 8; 
        $maxerrsize = (928 - $numcw); 
        while (($maxecl > 0) && ($maxerrsize < (2 << $maxecl))) {
            --$maxecl;
        }
        if (($ecl < 0) || ($ecl > 8)) {
            if ($numcw < 41) {
                $ecl = 2;
            } elseif ($numcw < 161) {
                $ecl = 3;
            } elseif ($numcw < 321) {
                $ecl = 4;
            } elseif ($numcw < 864) {
                $ecl = 5;
            } else {
                $ecl = $maxecl;
            }
        }
        return min($maxecl, $ecl);
    }

    protected function getErrorCorrection($codewords, $ecl)
    {
        $ecc = Data::$rsfactors[$ecl];
        $eclsize = (2 << $ecl);
        $eclmaxid = ($eclsize - 1);
        $ecw = array_fill(0, $eclsize, 0);
        foreach ($codewords as $cdw) {
            $tk1 = ($cdw + $ecw[$eclmaxid]) % 929;
            for ($idx = $eclmaxid; $idx > 0; --$idx) {
                $tk2 = ($tk1 * $ecc[$idx]) % 929;
                $tk3 = 929 - $tk2;
                $ecw[$idx] = ($ecw[($idx - 1)] + $tk3) % 929;
            }
            $tk2 = ($tk1 * $ecc[0]) % 929;
            $tk3 = 929 - $tk2;
            $ecw[0] = $tk3 % 929;
        }
        foreach ($ecw as $idx => $err) {
            if ($err != 0) {
                $ecw[$idx] = 929 - $err;
            }
        }
        return array_reverse($ecw);
    }

    protected function processSequence(&$sequence_array, $code, $seq, $offset)
    {
        $prevseq = substr($code, $offset, ($seq - $offset));
        $textseq = array();
        preg_match_all('/([\x09\x0a\x0d\x20-\x7e]{5,})/', $prevseq, $textseq, PREG_OFFSET_CAPTURE);
        $textseq[1][] = array('', strlen($prevseq));
        $txtoffset = 0;
        foreach ($textseq[1] as $txtseq) {
            $txtseqlen = strlen($txtseq[0]);
            if ($txtseq[1] > 0) {
                $prevtxtseq = substr($prevseq, $txtoffset, ($txtseq[1] - $txtoffset));
                if (strlen($prevtxtseq) > 0) {
                    if ((strlen($prevtxtseq) == 1)
                        && ((count($sequence_array) > 0)
                        && ($sequence_array[(count($sequence_array) - 1)][0] == 900))
                    ) {
                        $sequence_array[] = array(913, $prevtxtseq);
                    } elseif ((strlen($prevtxtseq) % 6) == 0) {
                        $sequence_array[] = array(924, $prevtxtseq);
                    } else {
                        $sequence_array[] = array(901, $prevtxtseq);
                    }
                }
            }
            if ($txtseqlen > 0) {
                $sequence_array[] = array(900, $txtseq[0]);
            }
            $txtoffset = ($txtseq[1] + $txtseqlen);
        }
    }

    protected function getInputSequences($code)
    {
        $sequence_array = array(); 
        $numseq = array();
        preg_match_all('/([0-9]{13,})/', $code, $numseq, PREG_OFFSET_CAPTURE);
        $numseq[1][] = array('', strlen($code));
        $offset = 0;
        foreach ($numseq[1] as $seq) {
            $seqlen = strlen($seq[0]);
            if ($seq[1] > 0) {
                $this->processSequence($sequence_array, $code, $seq[1], $offset);
            }
            if ($seqlen > 0) {
                $sequence_array[] = array(902, $seq[0]);
            }
            $offset = ($seq[1] + $seqlen);
        }
        return $sequence_array;
    }
}
