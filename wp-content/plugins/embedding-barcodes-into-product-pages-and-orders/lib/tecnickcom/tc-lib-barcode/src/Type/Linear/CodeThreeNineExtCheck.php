<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class CodeThreeNineExtCheck extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'C39E+';

    protected $chbar = array(
        '0' => '111331311',
        '1' => '311311113',
        '2' => '113311113',
        '3' => '313311111',
        '4' => '111331113',
        '5' => '311331111',
        '6' => '113331111',
        '7' => '111311313',
        '8' => '311311311',
        '9' => '113311311',
        'A' => '311113113',
        'B' => '113113113',
        'C' => '313113111',
        'D' => '111133113',
        'E' => '311133111',
        'F' => '113133111',
        'G' => '111113313',
        'H' => '311113311',
        'I' => '113113311',
        'J' => '111133311',
        'K' => '311111133',
        'L' => '113111133',
        'M' => '313111131',
        'N' => '111131133',
        'O' => '311131131',
        'P' => '113131131',
        'Q' => '111111333',
        'R' => '311111331',
        'S' => '113111331',
        'T' => '111131331',
        'U' => '331111113',
        'V' => '133111113',
        'W' => '333111111',
        'X' => '131131113',
        'Y' => '331131111',
        'Z' => '133131111',
        '-' => '131111313',
        '.' => '331111311',
        ' ' => '133111311',
        '$' => '131313111',
        '/' => '131311131',
        '+' => '131113131',
        '%' => '111313131',
        '*' => '131131311'
    );

    protected $extcodes = array(
        '%U', '$A', '$B', '$C',
        '$D', '$E', '$F', '$G',
        '$H', '$I', '$J', '$K',
        '$L', '$M', '$N', '$O',
        '$P', '$Q', '$R', '$S',
        '$T', '$U', '$V', '$W',
        '$X', '$Y', '$Z', '%A',
        '%B', '%C', '%D', '%E',
        ' ',  '/A', '/B', '/C',
        '/D', '/E', '/F', '/G',
        '/H', '/I', '/J', '/K',
        '/L', '-',  '.',  '/O',
        '0',  '1',  '2',  '3',
        '4',  '5',  '6',  '7',
        '8',  '9',  '/Z', '%F',
        '%G', '%H', '%I', '%J',
        '%V', 'A',  'B',  'C',
        'D',  'E',  'F',  'G',
        'H',  'I',  'J',  'K',
        'L',  'M',  'N',  'O',
        'P',  'Q',  'R',  'S',
        'T',  'U',  'V',  'W',
        'X',  'Y',  'Z',  '%K',
        '%L', '%M', '%N', '%O',
        '%W', '+A', '+B', '+C',
        '+D', '+E', '+F', '+G',
        '+H', '+I', '+J', '+K',
        '+L', '+M', '+N', '+O',
        '+P', '+Q', '+R', '+S',
        '+T', '+U', '+V', '+W',
        '+X', '+Y', '+Z', '%P',
        '%Q', '%R', '%S', '%T'
    );

    protected $chksum = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
        'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
        'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%'
    );

    protected function getExtendCode($code)
    {
        $ext = '';
        $clen = strlen($code);
        for ($chr = 0; $chr < $clen; ++$chr) {
            $item = ord($code[$chr]);
            if ($item > 127) {
                throw new BarcodeException('Invalid character: chr('.$item.')');
            }
            $ext .= $this->extcodes[$item];
        }
        return $ext;
    }

    protected function getChecksum($code)
    {
        $sum = 0;
        $clen = strlen($code);
        for ($chr = 0; $chr < $clen; ++$chr) {
            $key = array_keys($this->chksum, $code[$chr]);
            $sum += $key[0];
        }
        $idx = ($sum % 43);
        return $this->chksum[$idx];
    }

    protected function formatCode()
    {
        $code = $this->getExtendCode(strtoupper($this->code));
        $this->extcode = '*'.$code.$this->getChecksum($code).'*';
    }

    protected function setBars()
    {
        $this->ncols = 0;
        $this->nrows = 1;
        $this->bars = array();
        $this->formatCode();
        $clen = strlen($this->extcode);
        for ($chr = 0; $chr < $clen; ++$chr) {
            $char = $this->extcode[$chr];
            if (!isset($this->chbar[$char])) {
                throw new BarcodeException('Invalid character: chr('.ord($char).')');
            }
            for ($pos = 0; $pos < 9; ++$pos) {
                $bar_width = intval($this->chbar[$char][$pos]);
                if ((($pos % 2) == 0) && ($bar_width > 0)) {
                    $this->bars[] = array($this->ncols, 0, $bar_width, 1);
                }
                $this->ncols += $bar_width;
            }
            $this->ncols += 1;
        }
        --$this->ncols;
    }
}
