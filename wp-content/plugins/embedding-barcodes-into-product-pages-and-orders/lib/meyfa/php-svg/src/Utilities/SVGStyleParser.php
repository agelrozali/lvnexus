<?php

namespace SVG\Utilities;

abstract class SVGStyleParser
{
    public static function parseStyles($string)
    {
        $styles = array();
        if (empty($string)) {
            return $styles;
        }

        $declarations = preg_split('/\s*;\s*/', $string);

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ($declaration === '') {
                continue;
            }
            $split             = preg_split('/\s*:\s*/', $declaration);
            $styles[$split[0]] = $split[1];
        }

        return $styles;
    }

    public static function parseCss($css)
    {
        $result = array();
        preg_match_all('/(?ims)([a-z0-9\s\,\.\:#_\-@^*()\[\]\"\'=]+)\{([^\}]*)\}/', $css, $arr);

        foreach ($arr[0] as $i => $x) {
            $selectors = explode(',', trim($arr[1][$i]));
            if (in_array($selectors[0], array('@font-face', '@keyframes', '@media'))) {
                continue;
            }
            $rules = self::parseStyles(trim($arr[2][$i]));
            foreach ($selectors as $selector) {
                $result[trim($selector)] = $rules;
            }
        }

        return $result;
    }
}
