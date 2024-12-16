<?php

namespace UkrSolution\BarcodesDigital\Generators;

use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Exception;
use Kreativekorp\Barcode as BarcodeNumbered;

class Generator
{
    protected $storePath;
    protected $barcodeFactory;
    protected $barcodeNumberedFactory;
    protected $barcodeNumberedTypeCodes = array(
        'UPCA' => 'upc-a',
        'UPCE' => 'upc-e',
        'EAN13' => 'ean-13',
        'EAN8' => 'ean-8',
    );

    public function __construct()
    {
        $this->barcodeFactory = new Barcode();
        $this->barcodeNumberedFactory = new BarcodeNumbered();
    }

    public function getGeneratedBarcodeSVGFileName($code, $type, $w = 2, $h = 30, $color = 'black', $withoutFile = false)
    {
        if (
            'UPCA' === $type ||
            'UPCE' === $type ||
            'EAN13' === $type ||
            'EAN8' === $type
        ) {
            if ('UPCE' === $type) {
                $code = $this->prepareUPCECode($code);
            }

            $options = array(
                'p' => 0,
                'pb' => 6,
                'pr' => 2,
            );

            $svgContent = $this->barcodeNumberedFactory->render_svg($this->barcodeNumberedTypeCodes[$type], $code, $options);
        } else {

            $svgContent = $this->barcodeFactory
                ->getBarcodeObj($type, $code, $w, $h, $color)
                ->setBackgroundColor('white')
                ->getSvgCode();
        }
        if ($withoutFile === true) {
            return $svgContent;
        } else {
            $fileName = sha1(md5(uniqid(rand(), 1))) . '.svg';
            $saveFile = $this->checkfile($this->storePath . $fileName);
            $result = file_put_contents($saveFile, $svgContent);

            return false !== $result ? $fileName : '';
        }
    }

    public function setStorPath($path)
    {
        $this->storePath = $path;

        return $this;
    }

    protected function checkfile($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }

        return $path;
    }

    protected function prepareUPCECode($code)
    {
        $codeLength = strlen($code);

        if (7 === $codeLength) {
            $code = '0' === $code[0] ? $code . '*' : '*' . $code;
        } elseif (6 === $codeLength) {
            $code = '*' . $code . '*';
        }

        return $code;
    }
}
