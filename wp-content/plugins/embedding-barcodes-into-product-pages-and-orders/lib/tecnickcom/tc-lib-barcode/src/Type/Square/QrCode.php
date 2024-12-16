<?php

namespace Com\Tecnick\Barcode\Type\Square;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use \Com\Tecnick\Barcode\Type\Square\QrCode\ByteStream;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Split;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Encoder;

class QrCode extends \Com\Tecnick\Barcode\Type\Square
{
    protected $format = 'QRCODE';

    protected $version = 0;

    protected $level = 0;

    protected $hint = 2;

    protected $case_sensitive = true;

    protected $random_mask = false;

    protected $best_mask = true;

    protected $default_mask = 2;

    protected $bsObj;

    protected function setParameters()
    {
        parent::setParameters();
        if (!isset($this->params[0]) || !isset(Data::$errCorrLevels[$this->params[0]])) {
            $this->params[0] = 'L';
        }
        $this->level = Data::$errCorrLevels[$this->params[0]];

        if (!isset($this->params[1]) || !isset(Data::$encodingModes[$this->params[1]])) {
            $this->params[1] = '8B';
        }
        $this->hint = Data::$encodingModes[$this->params[1]];

        if (!isset($this->params[2]) || ($this->params[2] < 0) || ($this->params[2] > Data::QRSPEC_VERSION_MAX)) {
            $this->params[2] = 0;
        }
        $this->version = intval($this->params[2]);

        if (!isset($this->params[3])) {
            $this->params[3] = 1;
        }
        $this->case_sensitive = (bool)$this->params[3];

        if (!empty($this->params[4])) {
            $this->random_mask = intval($this->params[4]);
        }

        if (!isset($this->params[5])) {
            $this->params[5] = 1;
        }
        $this->best_mask = (bool)$this->params[5];

        if (!isset($this->params[6])) {
            $this->params[6] = 2;
        }
        $this->default_mask = intval($this->params[6]);
    }

    protected function setBars()
    {
        if (strlen((string)$this->code) == 0) {
            throw new BarcodeException('Empty input');
        }
        $this->bsObj = new ByteStream($this->hint, $this->version, $this->level);
        $this->processBinarySequence(
            $this->binarize(
                $this->encodeString($this->code)
            )
        );
    }

    protected function binarize($frame)
    {
        $len = count($frame);
        foreach ($frame as &$frameLine) {
            for ($idx = 0; $idx < $len; ++$idx) {
                $frameLine[$idx] = (ord($frameLine[$idx]) & 1) ? '1' : '0';
            }
        }
        return $frame;
    }

    protected function encodeString($data)
    {
        if (!$this->case_sensitive) {
            $data = $this->toUpper($data);
        }
        $split = new Split($this->bsObj, $this->hint, $this->version);
        $datacode = $this->bsObj->getByteStream($split->getSplittedString($data));
        $this->version = $this->bsObj->version;
        $enc = new Encoder(
            $this->version,
            $this->level,
            $this->random_mask,
            $this->best_mask,
            $this->default_mask
        );
        return $enc->encodeMask(-1, $datacode);
    }

    protected function toUpper($data)
    {
        $len = strlen($data);
        $pos = 0;

                while ($pos < $len) {
            $mode = $this->bsObj->getEncodingMode($data, $pos);
            if ($mode == Data::$encodingModes['KJ']) {
                $pos += 2;
            } else {
                if ((ord($data[$pos]) >= ord('a')) && (ord($data[$pos]) <= ord('z'))) {
                    $data[$pos] = chr(ord($data[$pos]) - 32);
                }
                $pos++;
            }
        }
        return $data;
    }
}
