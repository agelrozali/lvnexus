<?php

namespace SVG\Utilities\Colors;

use SVG\Utilities\Units\Angle;

final class Color
{
    public static function parse($color)
    {
        $lookupResult = ColorLookup::get($color);
        if (isset($lookupResult)) {
            return $lookupResult;
        }

        if (preg_match('/^#([0-9A-F]+)$/i', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseHexComponents($matches[1]);
        } elseif (preg_match('/^rgba?\((.*)\)$/', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseRGBAComponents($matches[1]);
        } elseif (preg_match('/^hsla?\((.*)\)$/', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseHSLAComponents($matches[1]);
        }

        if (!isset($r) || !isset($g) || !isset($b) || !isset($a)) {
            return array(0, 0, 0, 0);
        }

        return self::clamp($r, $g, $b, $a);
    }

    private static function clamp($r, $g, $b, $a)
    {
        return array(
            $r < 0 ? 0 : ($r > 255 ? 255 : (int) $r),
            $g < 0 ? 0 : ($g > 255 ? 255 : (int) $g),
            $b < 0 ? 0 : ($b > 255 ? 255 : (int) $b),
            $a < 0 ? 0 : ($a > 255 ? 255 : (int) $a),
        );
    }

    private static function parseHexComponents($str)
    {
        $len = strlen($str);

        $r = $g = $b = $a = null;

        if ($len === 6 || $len === 8) {
            $r = hexdec($str[0].$str[1]);
            $g = hexdec($str[2].$str[3]);
            $b = hexdec($str[4].$str[5]);
            $a = $len === 8 ? hexdec($str[6].$str[7]) : 255;
        } elseif ($len === 3 || $len == 4) {
            $r = hexdec($str[0].$str[0]);
            $g = hexdec($str[1].$str[1]);
            $b = hexdec($str[2].$str[2]);
            $a = $len === 4 ? hexdec($str[3].$str[3]) : 255;
        }

        return array($r, $g, $b, $a);
    }

    private static function parseRGBAComponents($str)
    {
        $params = preg_split('/(\s*[\/,]\s*)|(\s+)/', trim($str));
        if (count($params) !== 3 && count($params) !== 4) {
            return array(null, null, null, null);
        }

        $r = self::parseRGBAComponent($params[0]);
        $g = self::parseRGBAComponent($params[1]);
        $b = self::parseRGBAComponent($params[2]);
        $a = count($params) < 4 ? 255 : self::parseRGBAComponent($params[3], 1, 255);

        return array($r, $g, $b, $a);
    }

    private static function parseRGBAComponent($str, $base = 255, $scalar = 1)
    {
        $regex = '/^([+-]?(?:\d+|\d*\.\d+))(%)?$/';
        if (!preg_match($regex, $str, $matches)) {
            return null;
        }
        if (isset($matches[2]) && $matches[2] === '%') {
            return (float) $matches[1] * $base / 100 * $scalar;
        }
        return (float) $matches[1] * $scalar;
    }

    private static function parseHSLAComponents($str)
    {
        $params = preg_split('/(\s*[\/,]\s*)|(\s+)/', trim($str));
        if (count($params) !== 3 && count($params) !== 4) {
            return null;
        }

        $h = Angle::convert($params[0]);
        $s = self::parseRGBAComponent($params[1], 1);
        $l = self::parseRGBAComponent($params[2], 1);

        $r = $g = $b = null;
        if (isset($h) && isset($s) && isset($l)) {
            list($r, $g, $b) = self::convertHSLtoRGB($h, $s, $l);
        }
        $a = count($params) < 4 ? 255 : self::parseRGBAComponent($params[3], 1, 255);

        return array($r, $g, $b, $a);
    }

    private static function convertHSLtoRGB($h, $s, $l)
    {
        $s = min(max($s, 0), 1);
        $l = min(max($l, 0), 1);

        if ($s == 0) {
            return array($l * 255, $l * 255, $l * 255);
        }

        $m2 = ($l <= 0.5) ? ($l * (1 + $s)) : ($l + $s - $l * $s);
        $m1 = 2 * $l - $m2;

        $r = self::convertHSLHueToRGBComponent($m1, $m2, $h + 120);
        $g = self::convertHSLHueToRGBComponent($m1, $m2, $h);
        $b = self::convertHSLHueToRGBComponent($m1, $m2, $h - 120);

        return array($r, $g, $b);
    }

    private static function convertHSLHueToRGBComponent($m1, $m2, $hue)
    {
        $hue = fmod($hue, 360);
        if ($hue < 0) {
            $hue += 360;
        }

        $v = $m1;

        if ($hue < 60) {
            $v = $m1 + ($m2 - $m1) * $hue / 60;
        } elseif ($hue < 180) {
            $v = $m2;
        } elseif ($hue < 240) {
            $v = $m1 + ($m2 - $m1) * (240 - $hue) / 60;
        }

        return $v * 255;
    }
}
