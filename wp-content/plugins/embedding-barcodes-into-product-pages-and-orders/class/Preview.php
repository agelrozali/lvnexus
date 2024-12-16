<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Makers\PreviewA4BarcodesMaker;

class Preview
{
    public function getBarcode()
    {
        Request::ajaxRequestAccess();
        $post = array();
        foreach (array('format', 'dummy', 'isUseApi') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        foreach (array('template') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = $_POST[$key];
            }
        }
        foreach (array('lineBarcode', 'fieldLine1', 'fieldLine2', 'fieldLine3', 'fieldLine4') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        $validationRules = array(
            'lineBarcode' => 'array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'template' => 'xml',
            'format' => 'required',
            'dummy' => 'string',
        );

        $validator = Validator::create($post, $validationRules);
        $data = $validator->validate();

        $errors = $validator->getErrors();

        if (!empty($errors)) {
            uswbg_a4bJsonResponse(array('error' => array(array('lineBarcode' => reset($errors)))));
        } else {
            $generator = new PreviewA4BarcodesMaker($data);
            $result = $generator->make();


            uswbg_a4bJsonResponse($result);
        }
    }
}
