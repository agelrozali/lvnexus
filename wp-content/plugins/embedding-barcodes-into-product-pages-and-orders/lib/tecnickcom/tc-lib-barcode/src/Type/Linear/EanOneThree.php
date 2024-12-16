<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class EanOneThree extends \Com\Tecnick\Barcode\Type\Linear
{
    protected $format = 'EAN13';

    protected $code_length = 13;

    protected $check = '';

    protected $chbar = array(
        'A' => array( 
            '0' => '0001101',
            '1' => '0011001',
            '2' => '0010011',
            '3' => '0111101',
            '4' => '0100011',
            '5' => '0110001',
            '6' => '0101111',
            '7' => '0111011',
            '8' => '0110111',
            '9' => '0001011'
        ),
        'B' => array( 
            '0' => '0100111',
            '1' => '0110011',
            '2' => '0011011',
            '3' => '0100001',
            '4' => '0011101',
            '5' => '0111001',
            '6' => '0000101',
            '7' => '0010001',
            '8' => '0001001',
            '9' => '0010111'
        ),
        'C' => array( 
            '0' => '1110010',
            '1' => '1100110',
            '2' => '1101100',
            '3' => '1000010',
            '4' => '1011100',
            '5' => '1001110',
            '6' => '1010000',
            '7' => '1000100',
            '8' => '1001000',
            '9' => '1110100'
        )
    );

    protected $parities = array(
        '0' => 'AAAAAA',
        '1' => 'AABABB',
        '2' => 'AABBAB',
        '3' => 'AABBBA',
        '4' => 'ABAABB',
        '5' => 'ABBAAB',
        '6' => 'ABBBAA',
        '7' => 'ABABAB',
        '8' => 'ABABBA',
        '9' => 'ABBABA'
    );

    protected function getChecksum($code)
    {
        $data_len = ($this->code_length - 1);
        $code_len = strlen($code);
        $sum_a = 0;
        for ($pos = 1; $pos < $data_len; $pos += 2) {
            $sum_a += $code[$pos];
        }
        if ($this->code_length > 12) {
            $sum_a *= 3;
        }
        $sum_b = 0;
        for ($pos = 0; $pos < $data_len; $pos += 2) {
            $sum_b += ($code[$pos]);
        }
        if ($this->code_length < 13) {
            $sum_b *= 3;
        }
        $this->check = ($sum_a + $sum_b) % 10;
        if ($this->check > 0) {
            $this->check = (10 - $this->check);
        }
        if ($code_len == $data_len) {
            return $this->check;
        } elseif ($this->check !== intval($code[$data_len])) {
            throw new BarcodeException('Invalid check digit: '.$this->check);
        }
        return '';
    }

    protected function formatCode()
    {
        $code = str_pad($this->code, ($this->code_length - 1), '0', STR_PAD_LEFT);
        $this->extcode = $code.$this->getChecksum($code);
    }

    protected function setBars()
    {
        if (!is_numeric($this->code)) {
            throw new BarcodeException('Input code must be a number');
        }
        $this->formatCode();
        $seq = '101'; 
        $half_len = intval(ceil($this->code_length / 2));
        $parity = $this->parities[$this->extcode[0]];
        for ($pos = 1; $pos < $half_len; ++$pos) {
            $seq .= $this->chbar[$parity[($pos - 1)]][$this->extcode[$pos]];
        }
        $seq .= '01010'; 
        for ($pos = $half_len; $pos < $this->code_length; ++$pos) {
            $seq .= $this->chbar['C'][$this->extcode[$pos]];
        }
        $seq .= '101'; 
        $this->processBinarySequence($seq);
    }
}
