<?php

namespace UkrSolution\BarcodesDigital\BarcodeTemplates;

use UkrSolution\BarcodesDigital\Generators\Generator;

class BarcodeView
{
    private $code = "";
    private $algorithm = "";
    public function __construct($code, $algorithm)
    {
        $this->code = $code;
        $this->algorithm = $algorithm;

        $this->renderImage();
    }

    private function renderImage()
    {
        header('Content-type: image/svg+xml');
        header('Cache-Control: public, max-age=2692000');
        header("Pragma: cache");

        $timestamp = time();
        $timestampEnd = $timestamp + (60 * 60 * 24);

        $tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
        $tsstringEnd = gmdate('D, d M Y H:i:s ', $timestampEnd) . 'GMT';

        header("Last-Modified: $tsstring");
        header("Expires: $tsstringEnd");

        $language = "en";
        $etag = md5($language . $timestamp);

        header("ETag: \"{$etag}\"");

        $w = $h = 250;

        $barcodeGenerator = new Generator();
        echo $barcodeGenerator->getGeneratedBarcodeSVGFileName($this->code, $this->algorithm, $w, $h, 'black', true);
    }
}
