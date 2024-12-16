<?php

namespace Com\Tecnick\Color\Model;

class Rgb extends \Com\Tecnick\Color\Model implements \Com\Tecnick\Color\Model\Template
{
    protected $type = 'RGB';

    protected $cmp_red = 0.0;

    protected $cmp_green = 0.0;

    protected $cmp_blue = 0.0;

    public function getArray()
    {
        return array(
            'R' => $this->cmp_red,
            'G' => $this->cmp_green,
            'B' => $this->cmp_blue,
            'A' => $this->cmp_alpha
        );
    }

    public function getNormalizedArray($max)
    {
        return array(
            'R' => $this->getNormalizedValue($this->cmp_red, $max),
            'G' => $this->getNormalizedValue($this->cmp_green, $max),
            'B' => $this->getNormalizedValue($this->cmp_blue, $max),
            'A' => $this->cmp_alpha
        );
    }

    public function getCssColor()
    {
        return 'rgba('
            .$this->getNormalizedValue($this->cmp_red, 100).'%,'
            .$this->getNormalizedValue($this->cmp_green, 100).'%,'
            .$this->getNormalizedValue($this->cmp_blue, 100).'%,'
            .$this->cmp_alpha
            .')';
    }

    public function getJsPdfColor()
    {
        if ($this->cmp_alpha == 0) {
            return '["T"]'; 
        }
        return sprintf('["RGB",%F,%F,%F]', $this->cmp_red, $this->cmp_green, $this->cmp_blue);
    }

    public function getComponentsString()
    {
        return sprintf('%F %F %F', $this->cmp_red, $this->cmp_green, $this->cmp_blue);
    }

    public function getPdfColor($stroke = false)
    {
        $mode = 'rg';
        if ($stroke) {
            $mode = strtoupper($mode);
        }
        return $this->getComponentsString().' '.$mode."\n";
    }

    public function toGrayArray()
    {
        return array(
            'gray'  => (max(0, min(
                1,
                ((0.2126 * $this->cmp_red) + (0.7152 * $this->cmp_green) + (0.0722 * $this->cmp_blue))
            ))),
            'alpha' => $this->cmp_alpha
        );
    }

    public function toRgbArray()
    {
        return array(
            'red'   => $this->cmp_red,
            'green' => $this->cmp_green,
            'blue'  => $this->cmp_blue,
            'alpha' => $this->cmp_alpha
        );
    }

    public function toHslArray()
    {
        $min = min($this->cmp_red, $this->cmp_green, $this->cmp_blue);
        $max = max($this->cmp_red, $this->cmp_green, $this->cmp_blue);
        $lightness = (($min + $max) / 2);
        if ($min == $max) {
            $saturation = 0;
            $hue = 0;
        } else {
            $diff = ($max - $min);
            if ($lightness < 0.5) {
                $saturation = ($diff / ($max + $min));
            } else {
                $saturation = ($diff / (2.0 - $max - $min));
            }
            switch ($max) {
                case $this->cmp_red:
                    $dgb = ($this->cmp_green - $this->cmp_blue);
                    $hue = ($dgb / $diff) + (($dgb < 0) ? 6 : 0);
                    break;
                case $this->cmp_green:
                    $hue = (2.0 + (($this->cmp_blue - $this->cmp_red) / $diff));
                    break;
                case $this->cmp_blue:
                    $hue = (4.0 + (($this->cmp_red - $this->cmp_green) / $diff));
                    break;
            }
            $hue /= 6; 
        }
        return array(
            'hue'        => max(0, min(1, $hue)),
            'saturation' => max(0, min(1, $saturation)),
            'lightness'  => max(0, min(1, $lightness)),
            'alpha'      => $this->cmp_alpha
        );
    }

    public function toCmykArray()
    {
        $cyan = (1 - $this->cmp_red);
        $magenta = (1 - $this->cmp_green);
        $yellow = (1 - $this->cmp_blue);
        $key = 1;
        if ($cyan < $key) {
            $key = $cyan;
        }
        if ($magenta < $key) {
            $key = $magenta;
        }
        if ($yellow < $key) {
            $key = $yellow;
        }
        if ($key == 1) {
            $cyan = 0;
            $magenta = 0;
            $yellow = 0;
        } else {
            $cyan = (($cyan - $key) / (1 - $key));
            $magenta = (($magenta - $key) / (1 - $key));
            $yellow = (($yellow - $key) / (1 - $key));
        }
        return array(
            'cyan'    => max(0, min(1, $cyan)),
            'magenta' => max(0, min(1, $magenta)),
            'yellow'  => max(0, min(1, $yellow)),
            'key'     => max(0, min(1, $key)),
            'alpha'   => $this->cmp_alpha
        );
    }

    public function invertColor()
    {
        $this->cmp_red   = (1 - $this->cmp_red);
        $this->cmp_green = (1 - $this->cmp_green);
        $this->cmp_blue  = (1 - $this->cmp_blue);
        return $this;
    }
}
