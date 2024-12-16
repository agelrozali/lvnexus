<?php

namespace Com\Tecnick\Color;

use \Com\Tecnick\Color\Exception as ColorException;

abstract class Css
{
    protected function getColorObjFromJs($color)
    {
        if (!isset($color[2]) || (strpos('tgrc', $color[2]) === false)) {
             throw new ColorException('invalid javascript color: '.$color);
        }
        switch ($color[2]) {
            case 'g':
                $rex = '/[\[][\"\']g[\"\'][\,]([0-9\.]+)[\]]/';
                if (preg_match($rex, $color, $col) !== 1) {
                    throw new ColorException('invalid javascript color: '.$color);
                }
                return new \Com\Tecnick\Color\Model\Gray(array('gray' => $col[1], 'alpha' => 1));
            case 'r':
                $rex = '/[\[][\"\']rgb[\"\'][\,]([0-9\.]+)[\,]([0-9\.]+)[\,]([0-9\.]+)[\]]/';
                if (preg_match($rex, $color, $col) !== 1) {
                    throw new ColorException('invalid javascript color: '.$color);
                }
                return new \Com\Tecnick\Color\Model\Rgb(
                    array(
                        'red'   => $col[1],
                        'green' => $col[2],
                        'blue'  => $col[3],
                        'alpha' => 1
                    )
                );
            case 'c':
                $rex = '/[\[][\"\']cmyk[\"\'][\,]([0-9\.]+)[\,]([0-9\.]+)[\,]([0-9\.]+)[\,]([0-9\.]+)[\]]/';
                if (preg_match($rex, $color, $col) !== 1) {
                    throw new ColorException('invalid javascript color: '.$color);
                }
                return new \Com\Tecnick\Color\Model\Cmyk(
                    array(
                        'cyan'    => $col[1],
                        'magenta' => $col[2],
                        'yellow'  => $col[3],
                        'key'     => $col[4],
                        'alpha'   => 1
                    )
                );
        }
        return null;
    }

    protected function getColorObjFromCss($type, $color)
    {
        switch ($type) {
            case 'g':
                return $this->getColorObjFromCssGray($color);
            case 'rgb':
            case 'rgba':
                return $this->getColorObjFromCssRgb($color);
            case 'hsl':
            case 'hsla':
                return $this->getColorObjFromCssHsl($color);
            case 'cmyk':
            case 'cmyka':
                return $this->getColorObjFromCssCmyk($color);
        }
        return null;
    }

    private function getColorObjFromCssGray($color)
    {
        $rex = '/[\(]([0-9\%]+)[\)]/';
        if (preg_match($rex, $color, $col) !== 1) {
            throw new ColorException('invalid css color: '.$color);
        }
        return new \Com\Tecnick\Color\Model\Gray(
            array(
                'gray' => $this->normalizeValue($col[1], 255),
                'alpha' => 1
            )
        );
    }

    private function getColorObjFromCssRgb($color)
    {
        $rex = '/[\(]([0-9\%]+)[\,]([0-9\%]+)[\,]([0-9\%]+)[\,]?([0-9\.]*)[\)]/';
        if (preg_match($rex, $color, $col) !== 1) {
            throw new ColorException('invalid css color: '.$color);
        }
        return new \Com\Tecnick\Color\Model\Rgb(
            array(
                'red'   => $this->normalizeValue($col[1], 255),
                'green' => $this->normalizeValue($col[2], 255),
                'blue'  => $this->normalizeValue($col[3], 255),
                'alpha' => (isset($col[4][0]) ? $col[4] : 1)
            )
        );
    }

    private function getColorObjFromCssHsl($color)
    {
        $rex = '/[\(]([0-9\%]+)[\,]([0-9\%]+)[\,]([0-9\%]+)[\,]?([0-9\.]*)[\)]/';
        if (preg_match($rex, $color, $col) !== 1) {
            throw new ColorException('invalid css color: '.$color);
        }
        return new \Com\Tecnick\Color\Model\Hsl(
            array(
                'hue'        => $this->normalizeValue($col[1], 360),
                'saturation' => $this->normalizeValue($col[2], 1),
                'lightness'  => $this->normalizeValue($col[3], 1),
                'alpha'      => (isset($col[4][0]) ? $col[4] : 1)
            )
        );
    }

    private function getColorObjFromCssCmyk($color)
    {
        $rex = '/[\(]([0-9\%]+)[\,]([0-9\%]+)[\,]([0-9\%]+)[\,]([0-9\%]+)[\,]?([0-9\.]*)[\)]/';
        if (preg_match($rex, $color, $col) !== 1) {
            throw new ColorException('invalid css color: '.$color);
        }
        return new \Com\Tecnick\Color\Model\Cmyk(
            array(
                'cyan'    => $this->normalizeValue($col[1], 100),
                'magenta' => $this->normalizeValue($col[2], 100),
                'yellow'  => $this->normalizeValue($col[3], 100),
                'key'     => $this->normalizeValue($col[4], 100),
                'alpha'   => (isset($col[5][0]) ? $col[5] : 1)
            )
        );
    }
}
