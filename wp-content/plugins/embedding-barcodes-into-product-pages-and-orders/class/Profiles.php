<?php

namespace UkrSolution\BarcodesDigital;

class Profiles
{
    public function saveProfile()
    {
        Request::ajaxRequestAccess();
        global $wpdb;
        $post = array();
        foreach (array(
            'name', 'templateId', 'paperId', 'sheetId', 'update',
            'barcodeRotate', 'fontSize', 'fontAlgorithm', 'fontLineBreak', 'barcodePosition', 'imageTextGap',
            'barcodeHeightAuto', 'barcodeHeight', 'barcodeWidth', 'basePadding', 'showBarcode'
        ) as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        if (isset($_POST['params'])) {
            $post['params'] = USWBG_a4bRecursiveSanitizeTextField($_POST['params']);
        }

        $validationOptions = array(
            'name' => 'required|string',
            'templateId' => 'required|numeric',
            'paperId' => 'required|numeric',
            'sheetId' => 'required|numeric',
            'update' => 'string',
            'params' => 'array',
            'barcodeRotate' => 'required|numeric',
            'fontSize' => 'string',
            'fontAlgorithm' => 'required|string',
            'fontLineBreak' => 'required|string',
            'barcodePosition' => 'required|string',
            'imageTextGap' => 'required|string',
            'barcodeHeightAuto' => 'required|string',
            'showBarcode' => 'required|string',
            'barcodeHeight' => 'required|string',
            'barcodeWidth' => 'required|string',
            'basePadding' => 'required|string',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();

        $tableProfile = $wpdb->prefix . Database::$tableProfiles;
        $result = array();
        $profileParams = (isset($data["params"])) ? json_encode($data["params"]) : "";

        $profile = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$tableProfile}` WHERE `name` = %s;", $data["name"])
        );

        if ($data["update"] === "false") {
            if ($profile) {
                uswbg_a4bJsonResponse(array(
                    "need_approving" => 1,
                    "error" => "Profile exists",
                ));
            } else {
                $result['created'] = $this->createProfile($data, $profileParams);
            }
        } else {
            if ($profile) {
                $wpdb->update(
                    $tableProfile,
                    array(
                        'userId' => get_current_user_id(),
                        'templateId' => $data['templateId'],
                        'paperId' => $data['paperId'],
                        'sheetId' => $data['sheetId'],
                        'params' => $profileParams,
                        'barcodeRotate' => $data['barcodeRotate'],
                        'fontSize' => $data['fontSize'],
                        'fontAlgorithm' => $data['fontAlgorithm'],
                        'fontLineBreak' => $data['fontLineBreak'],
                        'barcodePosition' => $data['barcodePosition'],
                        'imageTextGap' => $data['imageTextGap'],
                        'barcodeHeightAuto' => $data['barcodeHeightAuto'],
                        'barcodeHeight' => $data['barcodeHeight'],
                        'barcodeWidth' => $data['barcodeWidth'],
                        'basePadding' => $data['basePadding'],
                        'showBarcode' => $data['showBarcode'],
                    ),
                    array('ID' => $profile->id),
                    array(
                        '%d', '%d', '%d', '%d', '%s',
                        '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                        '%s',
                    )
                );
                $result['updated'] = $profile->id;
            } else {
                $result['created'] = $this->createProfile($data, $profileParams);
            }
        }

        uswbg_a4bJsonResponse($result);
    }

    public function updateProfile()
    {
        Request::ajaxRequestAccess();
        global $wpdb;
        $post = array();
        foreach (array('profileId', 'templateId', 'paperId', 'sheetId') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationOptions = array(
            'profileId' => 'required|numeric',
            'templateId' => 'required|numeric',
            'paperId' => 'required|numeric',
            'sheetId' => 'required|numeric',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tableProfile = $wpdb->prefix . Database::$tableProfiles;

        $profile = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableProfile}` WHERE `id` = %d;", $data["profileId"]));
        $result = array();

        if ($profile) {
            $wpdb->update(
                $tableProfile,
                array(
                    'templateId' => $data['templateId'],
                    'paperId' => $data['paperId'],
                    'sheetId' => $data['sheetId'],
                ),
                array('ID' => $profile->id),
                array('%d', '%d', '%d')
            );
            $result['updated'] = $profile->id;


            $result["listProfiles"] = $wpdb->get_results("SELECT * FROM {$tableProfile} ORDER BY `datetime` DESC;", ARRAY_A);
        }

        uswbg_a4bJsonResponse($result);
    }

    private function createProfile($data, $profileParams = "")
    {
        global $wpdb;

        $tableProfile = $wpdb->prefix . Database::$tableProfiles;

        $wpdb->insert(
            $tableProfile,
            array(
                'name' => $data['name'],
                'userId' => get_current_user_id(),
                'templateId' => $data['templateId'],
                'paperId' => $data['paperId'],
                'sheetId' => $data['sheetId'],
                'params' => $profileParams,
                'barcodeRotate' => $data['barcodeRotate'],
                'fontSize' => $data['fontSize'],
                'fontAlgorithm' => $data['fontAlgorithm'],
                'fontLineBreak' => $data['fontLineBreak'],
                'barcodePosition' => $data['barcodePosition'],
                'imageTextGap' => $data['imageTextGap'],
                'barcodeHeightAuto' => $data['barcodeHeightAuto'],
                'barcodeHeight' => $data['barcodeHeight'],
                'barcodeWidth' => $data['barcodeWidth'],
                'basePadding' => $data['basePadding'],
                'showBarcode' => $data['showBarcode'],
            ),
            array(
                '%s', '%d', '%d', '%d', '%d', '%s',
                '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                '%s',
            )
        );

        return $wpdb->insert_id;
    }

    public function deleteProfile($data)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        if (isset($_POST['id'])) {
            $post['id'] = sanitize_key($_POST['id']);
        }

        $validationOptions = array(
            'id' => 'required|numeric',
        );

        $data = Validator::create($post, $validationOptions, true)->validate();
        $tableProfile = $wpdb->prefix . Database::$tableProfiles;
        $wpdb->delete($tableProfile, array('id' => $data["id"]));

        uswbg_a4bJsonResponse(array(
            'success' => true,
        ));
    }

    public function getProfile($id)
    {
        global $wpdb;

        $tableProfile = $wpdb->prefix . Database::$tableProfiles;

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$tableProfile}` WHERE `id` = %d;", $id)
        );
    }
}
