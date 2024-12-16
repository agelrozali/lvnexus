<?php

namespace Salla\ZATCA;

use chillerlan\QRCode\QRCode;
use InvalidArgumentException;
use chillerlan\QRCode\QROptions;

class GenerateQrCode
{
    protected $data = [];

    private function __construct($data)
    {
        $this->data = array_filter($data, function ($tag) {
            return $tag instanceof Tag;
        });

        if (\count($this->data) === 0) {
            throw new InvalidArgumentException('malformed data structure');
        }
    }

    public static function fromArray(array $data): GenerateQrCode
    {
        return new self($data);
    }

    public function toTLV(): string
    {
        return implode('', array_map(function ($tag) {
            return (string) $tag;
        }, $this->data));
    }

    public function toBase64(): string
    {
        return base64_encode($this->toTLV());
    }

    public function render($scale = 5): string
    {
        $options = new QROptions(
            [
                'scale' => $scale,
            ]
        );
        return (new QRCode($options))->render($this->toBase64());
    }
}
