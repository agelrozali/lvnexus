<?php

namespace UkrSolution\BarcodesDigital\Generators;

use SVG\SVG;
use UkrSolution\BarcodesDigital\Barcodes;
use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Helpers\Variables;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class BarcodeImage
{
    protected $options = array(
        'default-1' => array('width' => 400, 'height' => 112),
        'default-2' => array('line1' => array('x' => 'center', 'y' => 137), 'width' => 400, 'height' => 144),
        'default-3' => array(
            'line1' => array('x' => 'center', 'y' => 24),
            'line2' => array('x' => 'center', 'y' => 169),
            'width' => 400,
            'height' => 179,
        ),
        'default-4' => array(
            'line1' => array('x' => 'center', 'y' => 25),
            'line2' => array('x' => 'center', 'y' => 54),
            'line3' => array('x' => 'center', 'y' => 201),
            'line4' => array('x' => 'center', 'y' => 232),
            'width' => 400,
            'height' => 240,
        ),
        'default-5' => array('width' => 400, 'height' => 400),
        'default-6' => array(
            'line1' => array('x' => 'center', 'y' => 32, 'fs' => 25),
            'line2' => array('x' => 'center', 'y' => 438, 'fs' => 25),
            'width' => 320,
            'height' => 490,
        ),
        'default-7' => array(
            'line1' => array('x' => 280, 'y' => 53, 'fs' => 27),
            'line2' => array('x' => 280, 'y' => 112, 'fs' => 27),
            'line3' => array('x' => 280, 'y' => 174, 'fs' => 27),
            'line4' => array('x' => 280, 'y' => 229, 'fs' => 27),
            'width' => 700,
            'height' => 264,
        ),
        'default-8' => array('width' => 360, 'height' => 251),
        'default-9' => array('width' => 360, 'height' => 136),
        'zatca-qr' => array('width' => 408, 'height' => 408),
    );
    private $zoomX = 1;
    private $zoomY = 1;
    public $barcodeCPref = "";
    public $barcodeCode = "";
    public $barcodeCSuf = "";

    public function getBarcodeSizes()
    {
        $sizes = array();

        foreach ($this->options as $key => $value) {
            $sizes[$key] = array(
                'width' => $value['width'],
                'height' => $value['height'],
            );
        }

        return $sizes;
    }

    public function parseHash($hash, $fileData = array())
    {
        if (!function_exists("imagecreate")) {
            echo "GD not even installed.";
            return;
        }

        $customTemplates = new BarcodeTemplatesController();

        $json = isset($hash) ? base64_decode($hash) : "[]";
        $data = json_decode($json, true);

        $code = isset($data[0]) ? $data[0] : "";

        $lines = array(
            'line1' => isset($data[1]) ? $data[1] : "",
            'line2' => isset($data[2]) ? $data[2] : "",
            'line3' => isset($data[3]) ? $data[3] : "",
            'line4' => isset($data[4]) ? $data[4] : "",
        );

        $templateId = isset($data[5]) ? $data[5] : "";

        $barcodeType = isset($data[6]) ? $data[6] : null;

        $width = isset($data[7]) ? $data[7] : null;

        $height = isset($data[8]) ? $data[8] : null;

        $dummy = isset($data[9]) ? $data[9] : null;

        $padding = isset($data[10]) && $data[10] != "" ? $data[10] : null;

        $template = $customTemplates->getTemplateById($templateId);

        $algorithm = $barcodeType !== null ? $barcodeType : (!empty($template) ? $template->barcode_type : null);
        $algorithm = $algorithm ? $algorithm : "C128";

        if ($padding === null) {
            $padding = (!empty($template) && $template->base_padding_uol) ? $template->base_padding_uol : 0;
        }

        if ($padding && $width && $height) {
            $width -= $padding * 2;
            $height -= $padding * 2;
        }


        if ($template && $template->template) {
            $barcodeAttributes = $customTemplates->getConstantAttributes($template, "barcode_img_url");


            if (!$width && !$height && isset($fileData["options"]) && isset($fileData["options"]["size"])) {
                $width = $fileData["options"]["size"]["width"];
                $height = $fileData["options"]["size"]["height"];


                if ($padding && $width && $height) {
                    $width -= $padding * 2;
                    $height -= $padding * 2;
                }
            }

            $this->calcZoom($template, $width, $height, $algorithm);

            $texts = $customTemplates->getNodes($template->template, "text");

            $barcodeGenerator = new Generator();

            if ($width && $height) {
                $barcodeWidth = ($width === $height) ? $width : $this->zoomX * $barcodeAttributes["width"];
                $barcodeHeight = ($width === $height) ? $height : $this->zoomY * $barcodeAttributes["height"];
            } else {
                if (in_array(strtoupper($algorithm), array("EAN13", "EAN8", "UPCA", "UPCE"))) {
                    $barcodeWidth = $this->zoomX * $barcodeAttributes["width"];
                    $barcodeHeight = $this->zoomY * $barcodeAttributes["height"];
                } else {
                    if ($template->slug === "default-7") {
                        $barcodeWidth = $template->height;
                        $barcodeHeight = $template->height;
                    } else {
                        $barcodeWidth = $template->width;

                        if (in_array(strtoupper($algorithm), array("QRCODE", "DATAMATRIX"))) {
                            $barcodeHeight = $template->width;
                        } else {
                            $barcodeHeight = $template->height;
                        }
                    }
                }
            }

            if ((int) $templateId === 10 && $dummy) {
                $ZATCA = new ZATCA();
                $code = $ZATCA->generatePreviewLineBarcodeData("Seller's name", "VAT number");
            }

            $svg = $barcodeGenerator->getGeneratedBarcodeSVGFileName($code, $algorithm, $barcodeWidth, $barcodeHeight, 'black', true);
            $svg2png = SVG::fromString($svg);
            $png = $svg2png->toRasterImage($barcodeWidth, $barcodeHeight);

            Variables::initCodes($this, array(
                json_decode(Variables::getString(Variables::$codePref)),
                json_decode(Variables::getString(Variables::$code)),
                json_decode(Variables::getString(Variables::$codeSuf)),
            ));

            if ($width && $height) {
                $image = $this->createImage($width, $height);
            } else {
                $image = $this->createImage($template->width, $template->height);
            }

            $this->addBarcode($image, $png, $barcodeAttributes, $templateId);

            $this->fillLines($image, $lines, $template->slug, $algorithm);

            if ($padding) {
                if ($width && $height) {
                    $w = $width;
                    $h = $height;
                } else {
                    $w = $template->width;
                    $h = $template->height;
                }

                $image = $this->createPadding($image, $w, $h, $padding);
            }

            if (isset($fileData["options"]) && isset($fileData["options"]["storage"]) && $fileData["options"]["storage"] === "base64") {
                ob_start();
                imagepng($image);
                $imagedata = ob_get_clean();

                return 'data:image/png;base64,' . base64_encode($imagedata);
            } else if ($fileData) {
                return $this->saveToFile($image, $fileData);
            } else {
                $this->render($image);
            }
        }
    }

    private function saveTofile($image, $fileData)
    {
        $imagePath = $fileData['path'] . '/' . $fileData['options']['fileName'];
        $imageUrl = $fileData['url'] . '/' . $fileData['options']['fileName'];

        imagepng($image, $imagePath);

        return $imageUrl;
    }

    private function calcZoom($template, $width, $height, $algorithm)
    {
        if (isset($this->options[$template->slug])) {
            $defWidth = $this->options[$template->slug]["width"];
            $defHeight = $this->options[$template->slug]["height"];
        } else {
            $defWidth = $template->width;
            $defHeight = $template->height;
        }


        if ($width) {
            $this->zoomX = (($width * 100) / $defWidth) / 100;
        } else {
            $this->zoomX = (($template->width * 100) / $defWidth) / 100;
        }

        if (in_array(strtoupper($algorithm), array("QRCODE"))) {
            $this->zoomY = $this->zoomX;
        } else if ($height) {
            $this->zoomY = (($height * 100) / $defHeight) / 100;
        } else {
            $this->zoomY = (($template->height * 100) / $defHeight) / 100;
        }

    }

    public function createImage($width, $height)
    {
        $image = imagecreate($width, $height);

        $white = imagecolorallocatealpha($image, 255, 255, 255, 0);

        imagefill($image, 0, 0, $white);

        return $image;
    }

    public function createPadding($labelImage, $width, $height, $padding)
    {
        $image = imagecreate($width + $padding * 2, $height + $padding * 2);

        $white = imagecolorallocatealpha($image, 255, 255, 255, 0);

        imagefill($image, 0, 0, $white);

        imagecopymerge($image, $labelImage, $padding, $padding, 0, 0, $width, $height, 100);

        return $image;
    }

    public function addBarcode($image, $barcode, $barcodeAttributes, $templateId = "")
    {
        $x = isset($barcodeAttributes["x"]) ? $this->zoomX * $barcodeAttributes["x"] : 0;
        $y = isset($barcodeAttributes["y"]) ? $this->zoomY * $barcodeAttributes["y"] : 0;

        $barcodeWidth = $this->zoomX * $barcodeAttributes["width"];
        $barcodeHeight = $this->zoomY * $barcodeAttributes["height"];

        if ((int) $templateId === 9) {
            $y = - ($barcodeHeight * 0.45);
        }

        imagecopymerge($image, $barcode, $x, $y, 0, 0, $barcodeWidth, $barcodeHeight, 100);
    }

    public function fillLines(&$image, $lines, $templateType, $algorithm)
    {
        foreach ($lines as $key => $value) {
            if (isset($this->options[$templateType]) && !isset($this->options[$templateType][$key])) {
                continue;
            }
            if ($value) {
                if ($templateType === "default-6") {
                    $parts = $this->getPartsLines($image, $key, $value, $templateType);

                    if (count($parts) === 1) {
                        $this->addLine($image, $key, $value, $templateType, true, 0);
                    } else if (count($parts) > 1) {
                        $this->addLine($image, $key, $parts[0], $templateType, true, 1);
                        $this->addLine($image, $key, $parts[1], $templateType, true, 2);
                    }
                } else {
                    $this->addLine($image, $key, $value, $templateType);
                }
            }
        }
    }

    private function addLine(&$image, $line, $text, $templateType, $isPart = false, $part = 0)
    {
        $position = $this->getLinePosition($templateType, $line);

        $fontSize = (isset($position["fs"])) ? $position["fs"] : 19;
        $fontSize = $this->zoomY * $fontSize;
        $angle = 0;
        $font = dirname(__FILE__) . "/../../assets/fonts/arial.ttf";

        $dimensions = imagettfbbox($fontSize, $angle, $font, $text);
        $textWidth = abs($dimensions[4] - $dimensions[0]);

        if ($position['x'] === 'center') {
            $position['x'] = (imagesx($image) - $textWidth) / 2;
        } elseif ($position['x'] === 'right') {
            $position['x'] = imagesx($image) - $textWidth;
        } else {
            $position['x'] *= $this->zoomX;
        }

        $textColor = imagecolorallocate($image, 0, 0, 0);

        if ($isPart && $part === 2) {
            $lh = 1.6;
            $position['y'] += isset($position['fs']) ? $position['fs'] * $lh : 19 * $lh;
        } else if ($isPart && $part === 0) {
            $lh = 1.6;
            $position['y'] += isset($position['fs']) ? ($position['fs'] * $lh) / 2 : (19 * $lh) / 2;
        }

        $x = $position['x'];
        $y = $position['y'] > 0 ? $this->zoomY * $position['y'] : $position['y'];
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $font, $text);
    }

    private function getLinePosition($templateType, $line)
    {
        $position = array('x' => 0, 'y' => -50);

        if (isset($this->options[$templateType]) && isset($this->options[$templateType][$line])) {
            return $this->options[$templateType][$line];
        }

        return $position;
    }

    private function getPartsLines($image, $line, $text, $templateType)
    {
        $words = explode(" ", $text);
        $wnum = count($words);
        $maxwidth = imagesx($image);
        $font = dirname(__FILE__) . "/../../assets/fonts/arial.ttf";
        $textTemp = '';
        $textSep = '';
        $position = $this->getLinePosition($templateType, $line);
        $fontSize = (isset($position["fs"])) ? $this->zoomY * $position["fs"] : $this->zoomY * 19;

        for ($i = 0; $i < $wnum; $i++) {
            $textTemp .= $words[$i];
            $dimensions = imagettfbbox($fontSize, 0, $font, $textTemp);
            $lineWidth = $dimensions[2] - $dimensions[0] + 5;

            if ($lineWidth > $maxwidth) {
                $textSep .= ($textSep != '' ? '||||' . $words[$i] . ' ' : $words[$i] . ' ');
                $textTemp = $words[$i] . ' ';
            } else {
                $textSep .= $words[$i] . ' ';
                $textTemp .= ' ';
            }
        }

        return explode("||||", $textSep);
    }

    public function render($image, $isHtml = false)
    {
        if ($isHtml) {
            ob_start();
            imagepng($image);
            $imagedata = ob_get_clean();
            echo '<img src="data:image/png;base64,' . base64_encode($imagedata) . '"  />';
        } else {
            header('Content-Type: image/png');
            imagepng($image);
        }

        exit;
    }

    public static function createXML($code, $format)
    {
        $svg = '';
        $args = array(
            'lineBarcode' => array('value' => $code, 'type' => 'custom'),
            'format' => $format,
        );

        $Barcodes = new Barcodes();
        $validationResult = $Barcodes->validateBarcode($code, $format, $args);

        if ($validationResult['is_valid']) {
            $svg = $Barcodes->generateXml(array(
                'lineBarcode' => $code,
                'algorithm' => $format,
            ));
        }

        return $svg;
    }
}
