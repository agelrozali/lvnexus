<?php


namespace Composer\Autoload;

class ComposerStaticInitdigital6ef396a4dc448dde7e07aff6fe5d6eda
{
    public static $files = array (
    );

    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'chillerlan\\Settings\\' => 20,
            'chillerlan\\QRCode\\' => 18,
        ),
        'U' => 
        array (
            'UkrSolution\\BarcodesDigital\\' => 32,
        ),
        'S' => 
        array (
            'Salla\\ZATCA\\' => 12,
            'SVG\\' => 4,
        ),
        'M' => 
        array (
            'Milon\\Barcode\\' => 14,
            'Melgrati\\CodeValidator\\' => 23,
        ),
        'K' => 
        array (
            'Kreativekorp\\' => 13,
        ),
        'C' => 
        array (
            'Com\\Tecnick\\Color\\' => 18,
            'Com\\Tecnick\\Barcode\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'chillerlan\\Settings\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-settings-container/src',
        ),
        'chillerlan\\QRCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-qrcode/src',
        ),
        'UkrSolution\\BarcodesDigital\\' => 
        array (
            0 => __DIR__ . '/../..' . '/class',
        ),
        'Salla\\ZATCA\\' => 
        array (
            0 => __DIR__ . '/..' . '/salla/zatca/src',
        ),
        'SVG\\' => 
        array (
            0 => __DIR__ . '/..' . '/meyfa/php-svg/src',
        ),
        'Milon\\Barcode\\' => 
        array (
            0 => __DIR__ . '/..' . '/barcode_generator',
        ),
        'Melgrati\\CodeValidator\\' => 
        array (
            0 => __DIR__ . '/..' . '/barcode_validator',
        ),
        'Kreativekorp\\' => 
        array (
            0 => __DIR__ . '/..' . '/kreativekorp',
        ),
        'Com\\Tecnick\\Color\\' => 
        array (
            0 => __DIR__ . '/..' . '/tecnickcom/tc-lib-color',
            1 => __DIR__ . '/..' . '/tecnickcom/tc-lib-color/src',
        ),
        'Com\\Tecnick\\Barcode\\' => 
        array (
            0 => __DIR__ . '/..' . '/tecnickcom/tc-lib-barcode',
            1 => __DIR__ . '/..' . '/tecnickcom/tc-lib-barcode/src',
        ),
    );

    public static function getInitializer(BarcodesDigitalClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdigital6ef396a4dc448dde7e07aff6fe5d6eda::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdigital6ef396a4dc448dde7e07aff6fe5d6eda::$prefixDirsPsr4;
        }, null, BarcodesDigitalClassLoader::class);
    }
}
