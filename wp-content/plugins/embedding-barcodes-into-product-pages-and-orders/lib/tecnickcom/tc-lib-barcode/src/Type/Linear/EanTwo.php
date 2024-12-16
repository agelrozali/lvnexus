<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class EanTwo extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'EAN2';

    protected $code_length = 2;

    protected $chbar = array(
        'A' => array( 
            '0'=>'0001101',
            '1'=>'0011001',
            '2'=>'0010011',
            '3'=>'0111101',
            '4'=>'0100011',
            '5'=>'0110001',
            '6'=>'0101111',
            '7'=>'0111011',
            '8'=>'0110111',
            '9'=>'0001011'
        ),
        'B' => array( 
            '0'=>'0100111',
            '1'=>'0110011',
            '2'=>'0011011',
            '3'=>'0100001',
            '4'=>'0011101',
            '5'=>'0111001',
            '6'=>'0000101',
            '7'=>'0010001',
            '8'=>'0001001',
            '9'=>'0010111'
        )
    );

    protected $parities = array(
        '0' => array('A','A'),
        '1' => array('A','B'),
        '2' => array('B','A'),
        '3' => array('B','B')
    );

    protected function getChecksum($code)
    {
        return (intval($code) % 4);
    }

    protected function formatCode()
    {
        $this->extcode = str_pad($this->code, $this->code_length, '0', STR_PAD_LEFT);
    }

    protected function setBars()
    {
        $this->formatCode();
        $chk = $this->getChecksum($this->extcode);
        $parity = $this->parities[$chk];
        $seq = '1011'; 
        $seq .= $this->chbar[$parity[0]][$this->extcode[0]];
        $len = strlen($this->extcode);
        for ($pos = 1; $pos < $len; ++$pos) {
            $seq .= '01'; 
            $seq .= $this->chbar[$parity[$pos]][$this->extcode[$pos]];
        }
        $this->processBinarySequence($seq);
    }
}
