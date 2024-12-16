<?php

namespace UkrSolution\BarcodesDigital;

class PostData
{
    public static $postData = array();

    public static function set($data = array())
    {
        self::$postData = $data;

        if (isset(self::$postData['action'])) {
            unset(self::$postData['action']);
        }

        if (isset(self::$postData['infinity'])) {
            self::$postData['infinity'] = ('true' == self::$postData['infinity']) ? true : (('false' == self::$postData['infinity']) ? false : self::$postData['infinity']);
        }

        if (isset(self::$postData['hideCode'])) {
            self::$postData['hideCode'] = ('true' == self::$postData['hideCode']) ? true : (('false' == self::$postData['hideCode']) ? false : self::$postData['hideCode']);
        }

        if (isset(self::$postData['landscape'])) {
            self::$postData['landscape'] = ('true' == self::$postData['landscape']) ? true : (('false' == self::$postData['landscape']) ? false : self::$postData['landscape']);
        }

        if (isset(self::$postData['withVariations'])) {
            self::$postData['withVariations'] = ('true' == self::$postData['withVariations']) ? true : (('false' == self::$postData['withVariations']) ? false : self::$postData['withVariations']);
        }

    }

    public static function get()
    {
        return self::$postData;
    }
}
