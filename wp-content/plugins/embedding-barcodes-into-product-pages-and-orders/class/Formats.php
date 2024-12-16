<?php

namespace UkrSolution\BarcodesDigital;

class Formats
{
    public function deleteFormat()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_text_field($_POST['id']);
        }

        $validationOptions = array('id' => 'required|numeric');

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tableLabelSheets = Database::$tableLabelSheets;

        if ($wpdb->delete("{$wpdb->prefix}{$tableLabelSheets}", array('id' => $data['id']), array('%d'))) {
            $success = array(__('Data successfully deleted.', 'wpbcu-barcode-generator'));
            $error = array();
        } else {
            $success = array();
            $error = array(__('Data was not deleted.', 'wpbcu-barcode-generator') . ' ' . $wpdb->last_error);
        }

        $sheets = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tableLabelSheets};", ARRAY_A);

        uswbg_a4bJsonResponse(compact('success', 'error', 'sheets'));
    }

    public function saveFormat()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        foreach (array(
            'id',
            'name',
            'width',
            'height',
            'around',
            'across',
            'marginLeft',
            'marginRight',
            'marginTop',
            'marginBottom',
            'aroundCount',
            'acrossCount',
            'paperId'
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationOptions = array(
            'id' => 'numeric',
            'name' => 'required',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'around' => 'required|numeric',
            'across' => 'required|numeric',
            'marginLeft' => 'required|numeric',
            'marginRight' => 'required|numeric',
            'marginTop' => 'required|numeric',
            'marginBottom' => 'required|numeric',
            'aroundCount' => 'required|numeric',
            'acrossCount' => 'required|numeric',
            'paperId' => 'required|numeric',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tableLabelSheets = Database::$tableLabelSheets;
        $data['userId'] = get_current_user_id();

        if (isset($data['id'])) {
            $result = $wpdb->update("{$wpdb->prefix}{$tableLabelSheets}", $data, array('id' => $data['id'], 'default' => 0));
            $id = $data['id'];
        } else {
            $result = $wpdb->insert("{$wpdb->prefix}{$tableLabelSheets}", $data);
            $id = $wpdb->insert_id;
        }

        if (false !== $result) {
            $success = array(__('Data successfully saved.', 'wpbcu-barcode-generator'));
            $error = array();
        } else {
            $success = array();
            $error = array(__('Data was not saved.', 'wpbcu-barcode-generator') . ' ' . $wpdb->last_error);
        }

        $sheets = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tableLabelSheets};", ARRAY_A);

        uswbg_a4bJsonResponse(compact('success', 'error', 'id', 'sheets'));
    }

    public function getAllFormats()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $tableLabelSheets = Database::$tableLabelSheets;

        $listFormats = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tableLabelSheets} ", ARRAY_A);

        uswbg_a4bJsonResponse(array(
            'listFormats' => $listFormats,
            'error' => empty($listFormats) ? array(__('No data found on request.') . ' ' . $wpdb->last_error) : array(),
            'success' => array(),
        ));
    }

    public function getFormat()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_key($_POST['id']);
        }

        $validationOptions = array('id' => 'required|numeric');

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tableLabelSheets = Database::$tableLabelSheets;

        $formatData = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$tableLabelSheets} where id='%d'", $data['id']), ARRAY_A);

        if (!empty($formatData)) {
            $response = $formatData;
        } else {
            $response = array('error' => array(__('No data found on request') . ' ' . $wpdb->last_error));
        }

        uswbg_a4bJsonResponse($response);
    }

    public function getPapers()
    {
        global $wpdb;

        $tablePaperFormats = Database::$tablePaperFormats;

        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tablePaperFormats} WHERE `name` <> 'future-reserved-paper-format'", ARRAY_A);
    }

    public function getSheets()
    {
        global $wpdb;

        $tableLabelSheets = Database::$tableLabelSheets;

        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tableLabelSheets};", ARRAY_A);
    }

    public function getAllPaperFormats($isAjax = true)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $tablePaperFormats = Database::$tablePaperFormats;
        $tableLabelSheets = Database::$tableLabelSheets;
        $tableProfiles = Database::$tableProfiles;

        $listFormats = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tablePaperFormats} WHERE `name` <> 'future-reserved-paper-format'", ARRAY_A);

        $listSheets = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tableLabelSheets};", ARRAY_A);

        $listProfiles = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tableProfiles} ORDER BY `datetime` DESC;", ARRAY_A);

        foreach ($listProfiles as &$profile) {
            if ($profile["params"]) {
                $profile["params"] = @json_decode($profile["params"]);
            }
        }

        $dimensions = new Dimensions();

        $data = array(
            'dimension' => $dimensions->getActive(),
            'listFormats' => $listFormats,
            'listSheets' => $listSheets,
            'listProfiles' => $listProfiles,
            'error' => empty($listFormats) ? array(__('No data found on request.') . ' ' . $wpdb->last_error) : array(),
            'success' => array(),
        );

        if ($isAjax === false) {
            return $data;
        } else {
            uswbg_a4bJsonResponse($data);
        }
    }

    public function savePaperFormat()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        foreach (array('id', 'name', 'width', 'height', 'landscape') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);

                if ($post[$key] === 'true') $post[$key] = 1;
                elseif ($post[$key] === 'false') $post[$key] = 0;
            }
        }

        $validationOptions = array(
            'id' => 'numeric',
            'name' => 'required',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'landscape' => 'required',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();

        $data['is_editable'] = 1;

        $tablePaperFormats = Database::$tablePaperFormats;

        $dimensions = new Dimensions();
        $dimensionId = $dimensions->getActive();

        if (!isset($data['id'])) {
            $data['uol_id'] = $dimensionId;
            $result = $wpdb->insert("{$wpdb->prefix}{$tablePaperFormats}", $data);
            $id = $wpdb->insert_id;
        } else {
            $result = $wpdb->update("{$wpdb->prefix}{$tablePaperFormats}", $data, array('id' => $data['id'], 'is_editable' => 1));
            $id = $data['id'];
        }

        if (false !== $result) {
            $success = array(__('Data successfully saved.', 'wpbcu-barcode-generator'));
            $error = array();
        } else {
            $success = array();
            $error = array(__('Data was not saved.', 'wpbcu-barcode-generator') . ' ' . $wpdb->last_error);
        }

        if (isset($data['uol_id'])) {
            $this->setDimension($data['uol_id']);
        } else {
            $data['uol_id'] = $dimensionId;
        }

        $listFormats = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$tablePaperFormats} "
                . " WHERE `name` <> 'future-reserved-paper-format'"
                . " AND `uol_id` = %d", $data['uol_id']),
            ARRAY_A
        );

        uswbg_a4bJsonResponse(compact('success', 'error', 'id', 'listFormats'));
    }

    private function setDimension($id)
    {
        global $wpdb;

        $tableDimension = Database::$tableDimension;

        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}{$tableDimension} SET `is_default` = %d", 0));

        $wpdb->update("{$wpdb->prefix}{$tableDimension}", array('is_default' => 1), array('id' => $id));
    }

    public function getFormatsByPaper()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        foreach (array('paperId', 'profileId') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationOptions = array(
            'paperId' => 'required|numeric',
            'profileId' => 'numeric',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tableLabelSheets = Database::$tableLabelSheets;
        $tablePaperFormats = Database::$tablePaperFormats;
        $tableProfiles = Database::$tableProfiles;
        $tableTemplates = Database::$tableTemplates;

        $sheetId = null;
        $templateId = null;

        if (isset($data["profileId"])) {
            $profile = $wpdb->get_row($wpdb->prepare("SELECT sheetId, templateId FROM {$wpdb->prefix}{$tableProfiles} WHERE id = '%d';", $data['profileId']));

            if ($profile && $profile->sheetId) {
                $sheetId = $profile->sheetId;
            }

            if ($profile && $profile->templateId) {
                $templateId = $profile->templateId;
            }
        }

        if ($sheetId !== null) {
            $preparedSql = $wpdb->prepare("
                SELECT sf.*, pf.uol
                FROM {$wpdb->prefix}{$tableLabelSheets} as sf, {$wpdb->prefix}{$tablePaperFormats} AS pf
                WHERE pf.id = sf.paperId AND sf.paperId='%d' AND sf.id='%d'
            ", $data['paperId'], $sheetId);
        } else {
            $preparedSql = $wpdb->prepare("
                SELECT sf.*, pf.uol
                FROM {$wpdb->prefix}{$tableLabelSheets} as sf, {$wpdb->prefix}{$tablePaperFormats} AS pf
                WHERE pf.id = sf.paperId AND sf.paperId='%d'
            ", $data['paperId']);
        }

        $listFormats = $wpdb->get_results($preparedSql, ARRAY_A);

        $profileTemplate = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$tableTemplates} WHERE id='%d';", $templateId));

        uswbg_a4bJsonResponse(array(
            'listFormats' => $listFormats,
            'profileTemplate' => $profileTemplate,
            'error' => array(),
            'success' => array(),
        ));
    }

    public function deletePaperFormat()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_key($_POST['id']);
        }

        $validationOptions = array('id' => 'required|numeric');

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tablePaperFormats = Database::$tablePaperFormats;

        if ($wpdb->delete("{$wpdb->prefix}{$tablePaperFormats}", array('id' => $data['id']), array('%d'))) {
            $success = array(__('Data successfully deleted.', 'wpbcu-barcode-generator'));
            $error = array();
        } else {
            $success = array();
            $error = array(__('Data was not deleted.', 'wpbcu-barcode-generator') . ' ' . $wpdb->last_error);
        }

        $listFormats = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tablePaperFormats} WHERE `name` <> 'future-reserved-paper-format'", ARRAY_A);

        uswbg_a4bJsonResponse(compact('success', 'error', 'listFormats'));
    }
}
