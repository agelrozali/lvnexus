<?php

namespace UkrSolution\BarcodesDigital;

use Melgrati\CodeValidator\CodeValidator;
use UkrSolution\BarcodesDigital\Generators\Generator;
use UkrSolution\BarcodesDigital\Generators\BarcodeImage;

class Barcodes
{
    private $uploadPath = null;
    private $imageUrl = '';
    private $prefixPath = ''; 

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';

        $uploadDirData = wp_upload_dir();
        $this->uploadPath = $uploadDirData['basedir'] . '/' . $config['uploads'];

        $this->imageUrl = '/' . $config['uploads'];
    }

    public function generateFile($data)
    {
        $w = $h = 250;

        $barcodeGenerator = new Generator();
        $barcodeGenerator->setStorPath($this->uploadPath . '/');
        $fileName = $barcodeGenerator->getGeneratedBarcodeSVGFileName($data['lineBarcode'], $data['algorithm'], $w, $h, 'black');

        return $this->imageUrl . '/' . $fileName;
    }

    public function generateXml($data)
    {
        $w = $h = 500;

        $withoutFile = true;

        $barcodeGenerator = new Generator();
        $barcodeGenerator->setStorPath($this->uploadPath . '/');

        return $barcodeGenerator->getGeneratedBarcodeSVGFileName($data['lineBarcode'], $data['algorithm'], $w, $h, 'black', $withoutFile);
    }

    public function generateImageUrl($data, $options, $saveToFile = false)
    {
        $barcodeData = array(
            $data['lineBarcode'],
            $data['fieldLine1'],
            $data['fieldLine2'],
            $data['fieldLine3'],
            $data['fieldLine4'],
            $options["templateId"],
        );

        if (isset($data['algorithm']) && $data['algorithm']) {
            $barcodeData[] = $data['algorithm'];
        }

        $barcodeData = base64_encode(json_encode($barcodeData));

        if ($saveToFile) {
            $barcodeImage = new BarcodeImage();
            $imageUrl = $barcodeImage->parseHash($barcodeData, array(
                "path" => $this->uploadPath,
                "url" => $this->imageUrl,
                "options" => $options
            ));

            return $imageUrl;
        } else {
            $fileName = '.png';
            $imageUrl = get_site_url() . '/d-barcodes/' . $barcodeData . $fileName;

            return $imageUrl;
        }
    }

    public function validateBarcode(&$code, $algorithm, $data = array())
    {
        $validData = array(
            'message' => '',
            'is_valid' => true,
        );

        $field = '"Barcode"';

        if (isset($data["lineBarcode"])) {
            $field = "\"{$data["lineBarcode"]["value"]}\"";
        }

        if (0 == mb_strlen($code)) {
            $validData['is_valid'] = false;
            $msg = __("The %field% field is not specified for this product.", 'wpbcu-barcode-generator');
            $validData['message'] = str_replace('%field%', $field, $msg);

            return $validData;
        }
        switch ($algorithm) {
            case 'EAN8': 
                $code = str_replace(' ', '', $code);
                $validData['is_valid'] = CodeValidator::IsValidEAN8($code);
                if (!$validData['is_valid']) {
                    $msg = __("%field% field contains incorrect data \"%code%\". It must contain 8 digits. 7 digits and 8th is a checksum digit calculated by formula. Check more on <a target='_blank' href='https://en.wikipedia.org/wiki/EAN-8'>Wiki</a>.", 'wpbcu-barcode-generator');
                    $msg = str_replace('%code%', $code, $msg);
                    $validData['message'] = str_replace('%field%', $field, $msg);
                }
                break;
            case 'EAN13': 
                $code = str_replace(' ', '', $code);
                $validData['is_valid'] = 12 === strlen($code)
                    ? CodeValidator::IsValidEAN13($code .= CodeValidatorEx::calculateEANCheckDigit(strrev($code)))
                    : CodeValidator::IsValidEAN13($code);

                if (!$validData['is_valid']) {
                    $msg = __("%field% field contains incorrect data \"%code%\". It must contain 13 digits. 12 digits and 13th is a checksum digit calculated by formula. Check more on <a target='_blank' href='https://en.wikipedia.org/wiki/International_Article_Number'>Wiki</a>.", 'wpbcu-barcode-generator');
                    $msg = str_replace('%code%', $code, $msg);
                    $validData['message'] = str_replace('%field%', $field, $msg);
                }
                break;
            case 'UPCA': 
                $code = str_replace(' ', '', $code);
                $validData['is_valid'] = 11 === strlen($code)
                    ? CodeValidator::IsValidUPCA($code .= CodeValidatorEx::calculateEANCheckDigit($code))
                    : CodeValidator::IsValidUPCA($code);
                if (!$validData['is_valid']) {
                    $msg = __("%field% field contains incorrect data \"%code%\". It must contain 12 digits. 11 digits and 12th is a checksum digit calculated by formula. Check more on <a target='_blank' href='https://en.wikipedia.org/wiki/Universal_Product_Code'>Wiki</a>.", 'wpbcu-barcode-generator');
                    $msg = str_replace('%code%', $code, $msg);
                    $validData['message'] = str_replace('%field%', $field, $msg);
                }
                break;
            case 'UPCE': 
                $code = str_replace(' ', '', $code);
                $codeLength = strlen($code);
                if (7 === $codeLength) {
                    if ('0' === $code[0]) {
                        $code = substr($code, 1, 7);
                    }
                }

                $validData['is_valid'] = CodeValidator::IsValidUPCE($code);
                if (!$validData['is_valid']) {
                    $msg = __("%field% field contains incorrect data \"%code%\". It must contain 6 digits. UPC-E is a variation of UPC-A which allows for a more compact barcode by eliminating 'extra' zeros. Check more on <a target='_blank' href='https://en.wikipedia.org/wiki/Universal_Product_Code#UPC-E'>Wiki</a>.", 'wpbcu-barcode-generator');
                    $msg = str_replace('%code%', $code, $msg);
                    $validData['message'] = str_replace('%field%', $field, $msg);
                }
                break;
            case 'C128': 
                $patern = '/^[ -~]+$/';
                $validData['is_valid'] = preg_match($patern, $code);
                if (!$validData['is_valid']) {
                    $msg = __("%field% field contains incorrect data \"%code%\". Code 128 supports alphanumeric or numeric-only barcodes. It can encode all 128 characters of ASCII encoding.", 'wpbcu-barcode-generator');
                    $msg = str_replace('%code%', $code, $msg);
                    $validData['message'] = str_replace('%field%', $field, $msg);
                }
                break;
            case 'C39': 
                $patern = "#^[0-9a-zA-Z\-\.\ \$\/\+\%]+$#ui";
                if (false == preg_match($patern, $code)) {
                    $validData['is_valid'] = false;
                    $msg = __("%field% field contains incorrect data \"%code%\". Code 39 supports 43 characters, consisting of letters (A-Z), numeric digits (0 through 9) and a number of special characters (-, ., $, /, +, %, and space).", 'wpbcu-barcode-generator');
                    $msg = str_replace('%code%', $code, $msg);
                    $validData['message'] = str_replace('%field%', $field, $msg);
                }
                break;
            case 'QRCODE': 
                $validData['is_valid'] = true;
                $validData['message'] = '';
                break;
            case 'DATAMATRIX': 
                $validData['is_valid'] = true;
                $validData['message'] = '';
                break;
            default:
                $validData['is_valid'] = false;
                $validData['message'] = __("Unsupported code type provided", 'wpbcu-barcode-generator');
        }

        return $validData;
    }
}
