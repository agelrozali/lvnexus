<?php

namespace UkrSolution\BarcodesDigital\Helpers;

class Sanitize
{
    public static function getData($keys = array(), $method = "")
    {
        $data = array();

        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                if (is_array($_POST[$key])) {
                    $data[$key] = json_encode(self::recursiveSanitizeTextField($_POST[$key]));
                } else {
                    $data[$key] = sanitize_text_field($_POST[$key]);
                }
            }
        }

        return $data;
    }

    public static function recursiveSanitizeTextField($array)
    {
        foreach ($array as &$value) {
            $value = is_array($value) ? self::recursiveSanitizeTextField($value) : sanitize_text_field($value);
        }

        return $array;
    }
}
