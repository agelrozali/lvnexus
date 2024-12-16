<?php

namespace Com\Tecnick\Color;

use \Com\Tecnick\Color\Exception as ColorException;
use \Com\Tecnick\Color\Web;
use \Com\Tecnick\Color\Spot;

class Pdf extends \Com\Tecnick\Color\Spot
{
    protected static $jscolor = array(
        'transparent',
        'black',
        'white',
        'red',
        'green',
        'blue',
        'cyan',
        'magenta',
        'yellow',
        'dkGray',
        'gray',
        'ltGray',
    );

    public function getJsMap()
    {
        return self::$jscolor;
    }

    public function getJsColorString($color)
    {
        if (in_array($color, self::$jscolor)) {
            return 'color.'.$color;
        }
        try {
            if (($colobj = $this->getColorObj($color)) !== null) {
                return $colobj->getJsPdfColor();
            }
        } catch (ColorException $e) {
        }
        return 'color.'.self::$jscolor[0];
    }

    public function getColorObject($color)
    {
        try {
            return $this->getSpotColorObj($color);
        } catch (ColorException $e) {
        }
        try {
            return $this->getColorObj($color);
        } catch (ColorException $e) {
        }
        return null;
    }

    public function getPdfColor($color, $stroke = false, $tint = 1)
    {
        try {
            $col = $this->getSpotColor($color);
            $tint = sprintf('cs %F scn', (max(0, min(1, (float) $tint))));
            if ($stroke) {
                $tint = strtoupper($tint);
            }
            return sprintf('/CS%d %s'."\n", $col['i'], $tint);
        } catch (ColorException $e) {
        }
        try {
            $col = $this->getColorObj($color);
            if ($col !== null) {
                return $col->getPdfColor($stroke);
            }
        } catch (ColorException $e) {
        }
        return '';
    }

    public function getPdfRgbComponents($color)
    {
        $col = $this->getColorObject($color);
        if ($col === null) {
            return '';
        }
        $cmp = $col->toRgbArray();
        return sprintf('%F %F %F', $cmp['red'], $cmp['green'], $cmp['blue']);
    }
}
