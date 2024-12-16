<?php

namespace Salla\ZATCA;

class Tag
{
    protected $tag;

    protected $value;

    public function __construct($tag, $value)
    {
        $this->tag = $tag;
        $this->value = $value;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLength()
    {
        return strlen($this->value);
    }

    public function __toString()
    {
        $value = (string) $this->getValue();

        return $this->toHex($this->getTag()) . $this->toHex($this->getLength()) . ($value);
    }

    protected function toHex($value)
    {
        return pack("H*", sprintf("%02X", $value));
    }
}
