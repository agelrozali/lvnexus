<?php

namespace Com\Tecnick\Color;

abstract class Model
{
    protected $type;

    protected $cmp_alpha = 1.0;

    public function __construct($components)
    {
        foreach ($components as $color => $value) {
            $property = 'cmp_'.$color;
            if (property_exists($this, $property)) {
                $this->$property = (max(0, min(1, floatval($value))));
            }
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getNormalizedValue($value, $max)
    {
        return round(max(0, min($max, ($max * floatval($value)))));
    }

    public function getHexValue($value, $max)
    {
        return sprintf('%02x', $this->getNormalizedValue($value, $max));
    }

    public function getRgbaHexColor()
    {
        $rgba = $this->toRgbArray();
        return '#'
            .$this->getHexValue($rgba['red'], 255)
            .$this->getHexValue($rgba['green'], 255)
            .$this->getHexValue($rgba['blue'], 255)
            .$this->getHexValue($rgba['alpha'], 255);
    }

    public function getRgbHexColor()
    {
        $rgba = $this->toRgbArray();
        return '#'
            .$this->getHexValue($rgba['red'], 255)
            .$this->getHexValue($rgba['green'], 255)
            .$this->getHexValue($rgba['blue'], 255);
    }
}
