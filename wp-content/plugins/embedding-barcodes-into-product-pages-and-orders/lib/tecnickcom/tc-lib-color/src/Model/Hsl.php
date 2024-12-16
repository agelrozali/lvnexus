<?php

namespace Com\Tecnick\Color\Model;

class Hsl extends \Com\Tecnick\Color\Model implements \Com\Tecnick\Color\Model\Template
{
    protected $type = 'HSL';

    protected $cmp_hue = 0.0;

    protected $cmp_saturation = 0.0;

    protected $cmp_lightness = 0.0;

    public function getArray()
    {
        return array(
            'H' => $this->cmp_hue,
            'S' => $this->cmp_saturation,
            'L' => $this->cmp_lightness,
            'A' => $this->cmp_alpha
        );
    }

    public function getNormalizedArray($max)
    {
        $max = 360;
        return array(
            'H' => $this->getNormalizedValue($this->cmp_hue, $max),
            'S' => $this->cmp_saturation,
            'L' => $this->cmp_lightness,
            'A' => $this->cmp_alpha
        );
    }

    public function getCssColor()
    {
        return 'hsla('
            .$this->getNormalizedValue($this->cmp_hue, 360).','
            .$this->getNormalizedValue($this->cmp_saturation, 100).'%,'
            .$this->getNormalizedValue($this->cmp_lightness, 100).'%,'
            .$this->cmp_alpha
            .')';
    }

    public function getJsPdfColor()
    {
        $rgb = $this->toRgbArray();
        if ($this->cmp_alpha == 0) {
            return '["T"]'; 
        }
        return sprintf('["RGB",%F,%F,%F]', $rgb['red'], $rgb['green'], $rgb['blue']);
    }

    public function getComponentsString()
    {
        $rgb = $this->toRgbArray();
        return sprintf('%F %F %F', $rgb['red'], $rgb['green'], $rgb['blue']);
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
            'gray'  => $this->cmp_lightness,
            'alpha' => $this->cmp_alpha
        );
    }

    public function toRgbArray()
    {
        if ($this->cmp_saturation == 0) {
            return array(
                'red'   => $this->cmp_lightness,
                'green' => $this->cmp_lightness,
                'blue'  => $this->cmp_lightness,
                'alpha' => $this->cmp_alpha
            );
        }
        if ($this->cmp_lightness < 0.5) {
            $valb = ($this->cmp_lightness * (1 + $this->cmp_saturation));
        } else {
            $valb = (($this->cmp_lightness + $this->cmp_saturation) - ($this->cmp_lightness * $this->cmp_saturation));
        }
        $vala = ((2 * $this->cmp_lightness) - $valb);
        return array(
            'red'   => $this->convertHuetoRgb($vala, $valb, ($this->cmp_hue + (1 / 3))),
            'green' => $this->convertHuetoRgb($vala, $valb, $this->cmp_hue),
            'blue'  => $this->convertHuetoRgb($vala, $valb, ($this->cmp_hue - (1 / 3))),
            'alpha' => $this->cmp_alpha
        );
    }

    private function convertHuetoRgb($vala, $valb, $hue)
    {
        if ($hue < 0) {
            $hue += 1;
        }
        if ($hue > 1) {
            $hue -= 1;
        }
        if ((6 * $hue) < 1) {
            return max(0, min(1, ($vala + (($valb - $vala) * 6 * $hue))));
        }
        if ((2 * $hue) < 1) {
            return max(0, min(1, $valb));
        }
        if ((3 * $hue) < 2) {
            return max(0, min(1, ($vala + (($valb - $vala) * ((2 / 3) - $hue) * 6))));
        }
        return max(0, min(1, $vala));
    }

    public function toHslArray()
    {
        return array(
            'hue'        => $this->cmp_hue,
            'saturation' => $this->cmp_saturation,
            'lightness'  => $this->cmp_lightness,
            'alpha'      => $this->cmp_alpha
        );
    }

    public function toCmykArray()
    {
        $rgb = new \Com\Tecnick\Color\Model\Rgb($this->toRgbArray());
        return $rgb->toCmykArray();
    }

    public function invertColor()
    {
        $this->cmp_hue = ($this->cmp_hue >= 0.5) ? ($this->cmp_hue - 0.5) : ($this->cmp_hue + 0.5);
        return $this;
    }
}
