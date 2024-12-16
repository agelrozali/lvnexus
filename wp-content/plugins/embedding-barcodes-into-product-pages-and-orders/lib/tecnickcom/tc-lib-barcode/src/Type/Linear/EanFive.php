<?php

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class EanFive extends \Com\Tecnick\Barcode\Type\Linear\EanTwo
{
    protected $format = 'EAN5';

    protected $code_length = 5;

    protected $parities = array(
        '0' => array('B','B','A','A','A'),
        '1' => array('B','A','B','A','A'),
        '2' => array('B','A','A','B','A'),
        '3' => array('B','A','A','A','B'),
        '4' => array('A','B','B','A','A'),
        '5' => array('A','A','B','B','A'),
        '6' => array('A','A','A','B','B'),
        '7' => array('A','B','A','B','A'),
        '8' => array('A','B','A','A','B'),
        '9' => array('A','A','B','A','B')
    );

    protected function getChecksum($code)
    {
        return (((3 * (intval($code[0]) + intval($code[2]) + intval($code[4])))
            + (9 * (intval($code[1]) + intval($code[3])))) % 10);
    }
}
