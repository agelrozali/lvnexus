<?php

namespace Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

abstract class Process extends \Com\Tecnick\Barcode\Type\Linear
{
    protected function getNumericSequence($code)
    {
        $sequence = array();
        $len = strlen($code);
        $numseq = array();
        preg_match_all('/([0-9]{4,})/', $code, $numseq, PREG_OFFSET_CAPTURE);
        if (!empty($numseq[1])) {
            $end_offset = 0;
            foreach ($numseq[1] as $val) {
                $offset = $val[1];

                $slen = strlen($val[0]);
                if (($slen % 2) != 0) {
                    --$slen;
                    ++$offset;
                }

                                if ($offset > $end_offset) {
                    $sequence = array_merge(
                        $sequence,
                        $this->get128ABsequence(substr($code, $end_offset, ($offset - $end_offset)))
                    );
                }
                $sequence[] = array('C', substr($code, $offset, $slen), $slen);
                $end_offset = $offset + $slen;
            }
            if ($end_offset < $len) {
                $sequence = array_merge($sequence, $this->get128ABsequence(substr($code, $end_offset)));
            }
        } else {
            $sequence = array_merge($sequence, $this->get128ABsequence($code));
        }
        return $sequence;
    }

    protected function get128ABsequence($code)
    {
        $len = strlen($code);
        $sequence = array();
        $aseq = array();
        preg_match_all('/([\x00-\x1f])/', $code, $aseq, PREG_OFFSET_CAPTURE);
        if (!empty($aseq[1])) {
            preg_match_all('/([\x00-\x5f]+)/', $code, $aseq, PREG_OFFSET_CAPTURE);
            $end_offset = 0;
            foreach ($aseq[1] as $val) {
                $offset = $val[1];
                if ($offset > $end_offset) {
                    $sequence[] = array(
                        'B',
                        substr($code, $end_offset, ($offset - $end_offset)),
                        ($offset - $end_offset)
                    );
                }
                $slen = strlen($val[0]);
                $sequence[] = array('A', substr($code, $offset, $slen), $slen);
                $end_offset = $offset + $slen;
            }
            if ($end_offset < $len) {
                $sequence[] = array('B', substr($code, $end_offset), ($len - $end_offset));
            }
        } else {
            $sequence[] = array('B', $code, $len);
        }
        return $sequence;
    }


    protected function getCodeDataA(&$code_data, $code, $len)
    {
        for ($pos = 0; $pos < $len; ++$pos) {
            $char = $code[$pos];
            $char_id = ord($char);
            if (($char_id >= 241) && ($char_id <= 244)) {
                $code_data[] = $this->fnc_a[$char_id];
            } elseif (($char_id >= 0) && ($char_id <= 95)) {
                $code_data[] = strpos($this->keys_a, $char);
            } else {
                throw new BarcodeException('Invalid character sequence');
            }
        }
    }

    protected function getCodeDataB(&$code_data, $code, $len)
    {
        for ($pos = 0; $pos < $len; ++$pos) {
            $char = $code[$pos];
            $char_id = ord($char);
            if (($char_id >= 241) && ($char_id <= 244)) {
                $code_data[] = $this->fnc_b[$char_id];
            } elseif (($char_id >= 32) && ($char_id <= 127)) {
                $code_data[] = strpos($this->keys_b, $char);
            } else {
                throw new BarcodeException('Invalid character sequence: '.$char_id);
            }
        }
    }

    protected function getCodeDataC(&$code_data, $code)
    {
        $blocks = explode(chr(241), $code);

        foreach ($blocks as $blk) {
            $len = strlen($blk);

              if (($len % 2) != 0) {
                throw new BarcodeException('The length of each FNC1-separated code block must be even');
            }

            for ($pos = 0; $pos < $len; $pos += 2) {
                $chrnum = $blk[$pos].$blk[($pos + 1)];
                if (preg_match('/([0-9]{2})/', $chrnum) > 0) {
                    $code_data[] = intval($chrnum);
                } else {
                    throw new BarcodeException('Invalid character sequence');
                }
            }

            $code_data[] = 102;
        }

        array_pop($code_data);
    }

    protected function finalizeCodeData($code_data, $startid)
    {
        $sum = $startid;
        foreach ($code_data as $key => $val) {
            $sum += ($val * ($key + 1));
        }
        $code_data[] = ($sum % 103);

        $code_data[] = 106;
        $code_data[] = 107;
        array_unshift($code_data, $startid);

        return $code_data;
    }
}
