<?php

namespace Com\Tecnick\Color\Model;

class Cmyk extends \Com\Tecnick\Color\Model implements \Com\Tecnick\Color\Model\Template
{
    protected $type = 'CMYK';

    protected $cmp_cyan = 0.0;

    protected $cmp_magenta = 0.0;

    protected $cmp_yellow = 0.0;

    protected $cmp_key = 0.0;

    public function getArray()
    {
        return array(
            'C' => $this->cmp_cyan,
            'M' => $this->cmp_magenta,
            'Y' => $this->cmp_yellow,
            'K' => $this->cmp_key,
            'A' => $this->cmp_alpha
        );
    }

    public function getNormalizedArray($max)
    {
        return array(
            'C' => $this->getNormalizedValue($this->cmp_cyan, $max),
            'M' => $this->getNormalizedValue($this->cmp_magenta, $max),
            'Y' => $this->getNormalizedValue($this->cmp_yellow, $max),
            'K' => $this->getNormalizedValue($this->cmp_key, $max),
            'A' => $this->cmp_alpha,
        );
    }

    public function getCssColor()
    {
        $rgb = $this->toRgbArray();
        return 'rgba('
            .$this->getNormalizedValue($rgb['red'], 100).'%,'
            .$this->getNormalizedValue($rgb['green'], 100).'%,'
            .$this->getNormalizedValue($rgb['blue'], 100).'%,'
            .$rgb['alpha']
            .')';
    }

    public function getJsPdfColor()
    {
        if ($this->cmp_alpha == 0) {
            return '["T"]'; 
        }
        return sprintf('["CMYK",%F,%F,%F,%F]', $this->cmp_cyan, $this->cmp_magenta, $this->cmp_yellow, $this->cmp_key);
    }

    public function getComponentsString()
    {
        return sprintf('%F %F %F %F', $this->cmp_cyan, $this->cmp_magenta, $this->cmp_yellow, $this->cmp_key);
    }

    public function getPdfColor($stroke = false)
    {
        $mode = 'k';
        if ($stroke) {
            $mode = strtoupper($mode);
        }
        return $this->getComponentsString().' '.$mode."\n";
    }

    public function toGrayArray()
    {
        return array(
            'gray'  => $this->cmp_key,
            'alpha' => $this->cmp_alpha
        );
    }

    public function toRgbArray()
    {
        return array(
            'red'   => max(0, min(1, (1 - (($this->cmp_cyan    * (1 - $this->cmp_key)) + $this->cmp_key)))),
            'green' => max(0, min(1, (1 - (($this->cmp_magenta * (1 - $this->cmp_key)) + $this->cmp_key)))),
            'blue'  => max(0, min(1, (1 - (($this->cmp_yellow  * (1 - $this->cmp_key)) + $this->cmp_key)))),
            'alpha' => $this->cmp_alpha
        );
    }

    public function toHslArray()
    {
        $rgb = new \Com\Tecnick\Color\Model\Rgb($this->toRgbArray());
        return $rgb->toHslArray();
    }

    public function toCmykArray()
    {
        return array(
            'cyan'    => $this->cmp_cyan,
            'magenta' => $this->cmp_magenta,
            'yellow'  => $this->cmp_yellow,
            'key'     => $this->cmp_key,
            'alpha'   => $this->cmp_alpha
        );
    }

    public function invertColor()
    {
        $this->cmp_cyan    = (1 - $this->cmp_cyan);
        $this->cmp_magenta = (1 - $this->cmp_magenta);
        $this->cmp_yellow  = (1 - $this->cmp_yellow);
        $this->cmp_key     = (1 - $this->cmp_key);
        return $this;
    }
}
