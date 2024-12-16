<?php

namespace UkrSolution\BarcodesDigital\Makers;

use UkrSolution\BarcodesDigital\Barcodes;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class WoocommercePostsA4BarcodesMakerDemo
{
    protected $items = array();
    protected $barcodes = array();
    protected $data;
    protected $type;
    protected $success = array();
    protected $errors = array();
    protected $templateShortcodesArgs = array();
    protected $a4barcodes;
    protected $showName = true;
    protected $showLine3 = true;
    protected $showLine4 = true;

    public function __construct($data, $type = '')
    {
        $this->data = $data;
        $this->type = $type;
        $this->a4barcodes = new Barcodes();
    }

    public function make($options = array())
    {
        $this->getItems();

        $this->generateBarcodes($options);

        return $this->getResult();
    }

    protected function getItems()
    {
        $this->items = array(null);
    }
    protected function generateBarcodes($options = array())
    {
        $algorithm = $this->data['format'];

        $codePrefix = UserSettings::getOption('codePrefix', '');

        foreach ($this->items as $itemData) {
            $fileOptions = $this->getFileOptions($itemData, $algorithm);

            if (!empty($codePrefix)) {
                $fileOptions['lineBarcode'] = $codePrefix . $fileOptions['lineBarcode'];
            }

            $validationResult = $this->a4barcodes->validateBarcode($fileOptions['lineBarcode'], $fileOptions['algorithm'], $this->data);

            if ($validationResult['is_valid']) {
                if (isset($options['imageByUrl'])) {
                    $fileImage = $this->a4barcodes->generateImageUrl($fileOptions, $options, true);
                } else {
                    $fileImage = $this->a4barcodes->generateXml($fileOptions);
                }

                $svgContent = "";

                $barcodeData = array(
                    'image' => $fileImage,
                    'svgContent' => $svgContent,
                    'post_image' => $fileOptions['post_image'],
                    'lineBarcode' => $fileOptions['lineBarcode'],
                    'fieldLine1' => $fileOptions['fieldLine1'],
                    'fieldLine2' => $fileOptions['fieldLine2'],
                    'fieldLine3' => $fileOptions['fieldLine3'],
                    'fieldLine4' => $fileOptions['fieldLine4'],
                    'format' => $fileOptions['algorithm'],
                    'replacements' => $fileOptions['replacements'],
                );

                for ($i = $fileOptions['quantity']; $i > 0; --$i) {
                    $this->barcodes[] = $barcodeData;
                }
            } else { 
                $this->errors[] = array(
                    'id' => is_object($itemData) ? $itemData->ID : "",
                    'lineBarcode' => $validationResult['message'] ? $validationResult['message'] : $fileOptions['lineBarcode'],
                    'line1' => "",
                    'line2' => "",
                    'line3' => "",
                    'line4' => "",
                    'format' => $fileOptions['algorithm'],
                );
            }
        }
    }

    protected function getFileOptions($post, $algorithm)
    {
        $quantity = 1;

        return array(
            'quantity' => $quantity,
            'post_image' => '',
            'lineBarcode' => 'demo', 
            'fieldLine1' => isset($this->data['fieldLine1']) ? "01234567" : '', 
            'fieldLine2' => isset($this->data['fieldLine2']) ? $this->getFieldLine('Line 2', $this->data['fieldLine2']) : '', 
            'fieldLine3' => isset($this->data['fieldLine3']) ? $this->getFieldLine('Line 3', $this->data['fieldLine3']) : '', 
            'fieldLine4' => isset($this->data['fieldLine4']) ? $this->getFieldLine('Line 4', $this->data['fieldLine4']) : '', 
            'algorithm' => $algorithm, 
            'showName' => $this->showName, 
            'showLine3' => $this->showLine3, 
            'showLine4' => $this->showLine4,
            'replacements' => $this->getTemplateReplacements(null),
        );
    }

    protected function getFieldLine($text, $params)
    {
        $value = (isset($params['value']) && $params['value']) ? $params['value'] : '';

        $value = trim(str_replace("_", " ", $value));

        if ($value) {
            $value = "({$value})";
        }

        return "{$text} {$value}";
    }

    protected function getTemplateReplacements($item)
    {
        return new \ArrayObject();
    }

    protected function getResult()
    {
        $result = array(
            'listItems' => $this->barcodes,
            'success' => $this->success,
            'error' => $this->errors,
        );

        return $result;
    }
}
