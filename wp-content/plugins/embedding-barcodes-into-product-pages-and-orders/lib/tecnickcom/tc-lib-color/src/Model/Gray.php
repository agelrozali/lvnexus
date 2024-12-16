<?php

namespace Com\Tecnick\Color\Model;

class Gray extends \Com\Tecnick\Color\Model implements \Com\Tecnick\Color\Model\Template
{
    protected $type = 'GRAY';

    protected $cmp_gray = 0.0;

    public function getArray()
    {
        return array(
            'G' => $this->cmp_gray,
            'A' => $this->cmp_alpha
        );
    }

    public function getNormalizedArray($max)
    {
        return array(
            'G' => $this->getNormalizedValue($this->cmp_gray, $max),
            'A' => $this->cmp_alpha
        );
    }

    public function getCssColor()
    {
        return 'rgba('
            .$this->getNormalizedValue($this->cmp_gray, 100).'%,'
            .$this->getNormalizedValue($this->cmp_gray, 100).'%,'
            .$this->getNormalizedValue($this->cmp_gray, 100).'%,'
            .$this->cmp_alpha
            .')';
    }

    public function getJsPdfColor()
    {
        if ($this->cmp_alpha == 0) {
            return '["T"]'; 
        }
        return sprintf('["G",%F]', $this->cmp_gray);
    }

    public function getComponentsString()
    {
        return sprintf('%F', $this->cmp_gray);
    }

    public function getPdfColor($stroke = false)
    {
        $mode = 'g';
        if ($stroke) {
            $mode = strtoupper($mode);
        }
        return $this->getComponentsString().' '.$mode."\n";
    }

    public function toGrayArray()
    {
        return array(
            'gray'  => $this->cmp_gray,
            'alpha' => $this->cmp_alpha
        );
    }

    public function toRgbArray()
    {
        return array(
            'red'   => $this->cmp_gray,
            'green' => $this->cmp_gray,
            'blue'  => $this->cmp_gray,
            'alpha' => $this->cmp_alpha
        );
    }

    public function toHslArray()
    {
        return array(
            'hue'        => 0,
            'saturation' => 0,
            'lightness'  => $this->cmp_gray,
            'alpha'      => $this->cmp_alpha
        );
    }

    public function toCmykArray()
    {
        return array(
            'cyan'    => 0,
            'magenta' => 0,
            'yellow'  => 0,
            'key'     => $this->cmp_gray,
            'alpha'   => $this->cmp_alpha
        );
    }

    public function invertColor()
    {
        $this->cmp_gray = (1 - $this->cmp_gray);
        return $this;
    }
}
