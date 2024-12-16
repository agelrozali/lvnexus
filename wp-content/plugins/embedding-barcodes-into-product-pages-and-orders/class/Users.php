<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Helpers\Variables;
use UkrSolution\BarcodesDigital\Makers\UsersA4BarcodesMaker;

class Users
{
    public function addImportButton($which)
    {
        if ($which === 'top' && is_admin()) {
            include Variables::$A4B_PLUGIN_BASE_PATH . 'templates/users/users-import-button.php';
        }

        return $which;
    }

    public function getBarcodes()
    {
        Request::ajaxRequestAccess();
        global $current_user;
        $customTemplatesController = new BarcodeTemplatesController();
        $activeTemplate = $customTemplatesController->getActiveTemplate();


        $post = array();
        foreach (array('format', 'isUseApi', 'lineSeparator1', 'lineSeparator2', 'lineSeparator3', 'lineSeparator4', 'profileId', 'page') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        foreach (array(
            'usersIds',
            'lineBarcode',
            'fieldLine1',
            'fieldLine2',
            'fieldLine3',
            'fieldLine4',
            'fieldSepLine1',
            'fieldSepLine2',
            'fieldSepLine3',
            'fieldSepLine4',
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = USWBG_a4bRecursiveSanitizeTextField($_POST[$key]);
            }
        }

        $validationRules = array(
            'format' => 'required',
            'usersIds' => 'required|array|bail', 
            'lineBarcode' => $activeTemplate->code_match ? 'array' : 'required|array',
            'fieldLine1' => 'array',
            'fieldLine2' => 'array',
            'fieldLine3' => 'array',
            'fieldLine4' => 'array',
            'fieldSepLine1' => 'array',
            'fieldSepLine2' => 'array',
            'fieldSepLine3' => 'array',
            'fieldSepLine4' => 'array',
            'lineSeparator1' => 'string',
            'lineSeparator2' => 'string',
            'lineSeparator3' => 'string',
            'lineSeparator4' => 'string',
            'page' => 'string',
            'profileId' => 'numeric',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $postsBarcodesGenerator = new UsersA4BarcodesMaker($data);
        $result = $postsBarcodesGenerator->make();


        if ($current_user && isset($data['page']) && $data['page'] === "template-editor") {
            if (isset($data['usersIds']) && count($data['usersIds'])) {
                \update_user_meta($current_user->ID, "usplp_product_preview", $data['usersIds'][0] . ":user");
            }
        }

        uswbg_a4bJsonResponse($result);
    }
}
