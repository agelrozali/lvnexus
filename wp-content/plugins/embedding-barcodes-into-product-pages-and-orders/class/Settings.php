<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Helpers\Files;
use UkrSolution\BarcodesDigital\Helpers\Sanitize;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

class Settings
{
    public $defProfilesSettingsKeys = array("paper_sheet", "product_paper_sheet", "order_paper_sheet");
    private function saveGeneral($data)
    {
        if (isset($data["productShortcode"])) {
            $this->setDefaultShortcodes("product", $data["productShortcode"]);
        }

        if (isset($data["orderShortcode"])) {
            $this->setDefaultShortcodes("order", $data["orderShortcode"]);
        }

        if (isset($data["dimensionId"])) {
            $this->setDefaultDimesion($data["dimensionId"]);
        }
    }

    public function saveSession()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();

        foreach (array('session', 'stamp') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        $validationRules = array('session' => 'string', 'stamp' => 'string');
        $data = Validator::create($post, $validationRules, true)->validate();

        if (isset($data["session"])) {
            $value = $data["session"];
            $stamp = $data["stamp"];
            $tableUserSettings = $wpdb->prefix . Database::$tableUserSettings;

            $option = $wpdb->get_row("SELECT id FROM {$tableUserSettings} WHERE `param` = 'session';");

            if ($option) {
                $wpdb->update("{$tableUserSettings}", array('value' => $value), array('param' => 'session'));
            } else {
                $wpdb->insert("{$tableUserSettings}", array('param' => 'session', 'value' => $value));
            }

            $option = $wpdb->get_row("SELECT id FROM {$tableUserSettings} WHERE `param` = 'sessionStamp';");

            if ($option) {
                $wpdb->update("{$tableUserSettings}", array('value' => $stamp), array('param' => 'sessionStamp'));
            } else {
                $wpdb->insert("{$tableUserSettings}", array('param' => 'sessionStamp', 'value' => $stamp));
            }
        }

        uswbg_a4bJsonResponse(array("success" => true));
    }

    public function changeUol()
    {
        Request::ajaxRequestAccess();
        $post = array();
        foreach (array('uolId', 'templateId', 'rollWidth', 'rollHeight') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        $validationRules = array(
            'uolId' => 'string',
            'templateId' => 'string',
            'rollWidth' => 'string',
            'rollHeight' => 'string',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $this->setDefaultDimesion($data["uolId"]);

        $customTemplatesController = new BarcodeTemplatesController();

        if (isset($data['rollWidth']) && $data['rollWidth'] && isset($data['rollHeight']) && $data['rollHeight']) {
            $customTemplatesController->updatePaperSize($data['uolId'], $data['rollWidth'], $data['rollHeight']);
        }

        $templateId = (isset($data["templateId"]) && $data["templateId"]) ? $data["templateId"] : 1;
        $customTemplatesController->setActiveTemplate($templateId);

        uswbg_a4bJsonResponse(array("success" => true));
    }

    private function setDefaultShortcodes($type, $id)
    {
        global $wpdb;

        $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

        $wpdb->update($tableShortcodes, array("is_default" => null), array("type" => $type), array('%s'), array('%s'));

        $wpdb->update($tableShortcodes, array("is_default" => 1), array("id" => $id), array('%s'), array('%d'));
    }

    private function setDefaultDimesion($id)
    {
        global $wpdb;

        $tableDimension = $wpdb->prefix . Database::$tableDimension;

        $dimensions = new Dimensions();

        if ($dimensions->getActive() !== $id) {
            $customTemplatesController = new BarcodeTemplatesController();
            $customTemplatesController->setActiveTemplate(1, false);

            $tableUserSettings = $wpdb->prefix . Database::$tableUserSettings;

            if (!isset($_POST['rollWidth']) && !isset($_POST['rollHeight'])) {
                if ((int) $id === 1) {
                    $sheet = $this->getDefaultSheet(1);

                    if ($sheet && isset($sheet->id)) {
                        foreach ($this->defProfilesSettingsKeys as $key) {
                            $wpdb->update("{$tableUserSettings}", array('value' => '{"profileId":"","paperId":"1","sheetId":"' . $sheet->id . '"}'), array('param' => $key));
                        }
                    } else {
                        foreach ($this->defProfilesSettingsKeys as $key) {
                            $wpdb->update("{$tableUserSettings}", array('value' => '{"profileId":"","paperId":"1","sheetId":"11"}'), array('param' => $key));
                        }
                    }
                } else {
                    $sheet = $this->getDefaultSheet(2);

                    if ($sheet && isset($sheet->id)) {
                        foreach ($this->defProfilesSettingsKeys as $key) {
                            $wpdb->update("{$tableUserSettings}", array('value' => '{"profileId":"","paperId":"2","sheetId":"' . $sheet->id . '"}'), array('param' => $key));
                        }
                    } else {
                        foreach ($this->defProfilesSettingsKeys as $key) {
                            $wpdb->update("{$tableUserSettings}", array('value' => '{"profileId":"","paperId":"2","sheetId":"23"}'), array('param' => $key));
                        }
                    }
                }
            }
        }

        $wpdb->query($wpdb->prepare("UPDATE {$tableDimension} SET `is_default` = %d", 0));

        $wpdb->update("{$tableDimension}", array('is_default' => 1), array('id' => $id));
    }

    public function updateUserSettings($data = null, $isAjax = true)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        if (empty($data)) {
            $keys = array(
                "settings-type",
                "productPageShortcode", "adminProductShortcode",
                "lwhFormat", "defaultProfile", "defaultProductProfile", "defaultOrderProfile", "cfPriority", "attrPriority", "currencySymbol",
                "fieldNameL1", "fieldNameL2", "fieldNameL3", "fieldNameL4",
                "attributeIsntSpec", "gridOnPrint", "clearProfileLabels", "dimensionId", "lk", "lken", "excludedProdStatuses",
                "codePrefix", "productField",
                "sectionsParams",
                "barcodesOnProductPageParams",
                "productBarcodeEmailParams", "orderBarcodeEmailParams",
                "adminProductPageParams", "adminOrderPageParams", "adminOrderItemPageParams", "adminOrderPreviewParams", "adminOrderPreviewParams",
                "wc_pdf_ips_product_hook_params", "wc_pdf_ips_order_hook_params",
                "productShortcode", "orderShortcode",
                "paper_sheet", "product_paper_sheet", "order_paper_sheet", "settings_wizard", "customCheckboxSelector", "customButtonSelector",
                "wc_pdf_ips_status", "booster_for_wc",
                "removeDomainBarcodeUrl", "disableBrowserCache",
                "customField", "customFieldLabel", "customFieldName"
            );
            $data = (new Sanitize())::getData($keys);
        }

        $tableUserSettings = $wpdb->prefix . Database::$tableUserSettings;
        $userId = get_current_user_id();

        $this->saveGeneral($data);

        Files::resetAllTimestamps();

        if ($data) {
            if (isset($data['settings-type']) && $data['settings-type'] === 'general') {
                $userId = null;
            }

            if (isset($data['lken'])) {
                $prefix = 'ukrsolution_upgrade_digital_barcodes_';
                @delete_transient($prefix . '2.0.2');
            }

            foreach ($data as $param => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if ($userId === null) {
                    $option = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$tableUserSettings} WHERE `userId` IS NULL AND `param` = '%s';", $param));
                } else {
                    $option = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$tableUserSettings} WHERE `userId` = '%d' AND `param` = '%s';", $userId, $param));
                }

                if ($option) {
                    $wpdb->update("{$tableUserSettings}", array('value' => $value), array('userId' => $userId, 'param' => $param));
                } else {
                    $wpdb->insert("{$tableUserSettings}", array('userId' => $userId, 'param' => $param, 'value' => $value));
                }
            }
        }

        if ($isAjax) uswbg_a4bJsonResponse(array("success" => true, "userSettings" => UserSettings::get()));
    }

    public function updateUserFieldMatching()
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        if (isset($_POST['field'])) {
            $post['field'] = sanitize_text_field($_POST['field']);
        }
        if (isset($_POST['value'])) {
            $post['value'] = USWBG_a4bRecursiveSanitizeTextField($_POST['value']);
        }

        $validationRules = array(
            'field' => 'string',
            'value' => 'array',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $matching = json_encode($data["value"]);
        $tableFieldsMatching = $wpdb->prefix . Database::$tableFieldsMatching;
        $userId = get_current_user_id();

        if (!$data['field']) {
            return;
        }

        $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tableFieldsMatching} WHERE `userId` = '%d' AND `field` = '%s';", $userId, $data['field']));

        if ($field) {
            $wpdb->update("{$tableFieldsMatching}", array('matching' => $matching), array('id' => $field->id));
        } else {
            $wpdb->insert("{$tableFieldsMatching}", array('userId' => $userId, 'field' => $data['field'], 'matching' => $matching));
        }

        uswbg_a4bJsonResponse(array("success" => true));
    }

    public function updateTemplateFieldMatching($isAjax = true)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $post = array();
        foreach (array('templateId', 'matchingType', 'readonlyMatching') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        if (isset($_POST['matching'])) {
            $post['matching'] = USWBG_a4bRecursiveSanitizeTextField($_POST['matching']);
        }

        $validationRules = array(
            'templateId' => 'numeric',
            'matchingType' => 'string',
            'readonlyMatching' => 'string',
            'matching' => 'array',
        );

        $data = Validator::create($post, $validationRules, true)->validate();
        $tableTemplates = $wpdb->prefix . Database::$tableTemplates;
        $matching = json_encode($data["matching"]);
        $readonlyMatching = !isset($data["readonlyMatching"]) || $data["readonlyMatching"] === "false" ? 0 : 1;

        $wpdb->update(
            "{$tableTemplates}",
            array('matching' => $matching, 'matchingType' => $data["matchingType"], 'readonlyMatching' => $readonlyMatching),
            array('id' => $data["templateId"])
        );

        if ($isAjax) {
            uswbg_a4bJsonResponse(array("success" => true));
        } else {
            return true;
        }
    }

    public function clearTemplateFieldMatching($isAjax = true)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $tableFieldsMatching = $wpdb->prefix . Database::$tableFieldsMatching;
        $userId = get_current_user_id();

        $wpdb->query($wpdb->prepare("DELETE FROM {$tableFieldsMatching} WHERE `userId` = '%d';", $userId));

        if ($isAjax || $isAjax === "") {
            uswbg_a4bJsonResponse(array("success" => true));
        } else {
            return true;
        }
    }

    private function getDefaultSheet($paperId)
    {
        global $wpdb;

        $tableLabelSheets = Database::$tableLabelSheets;
        $name = "";

        switch ($paperId) {
            case 1:
                $name = "44 labels";
                break;
            case 2:
                $name = "18 labels";
                break;
        }

        $sheet = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}{$tableLabelSheets}` WHERE (`paperId` = %s AND `name` = %s);",
                array($paperId, $name)
            )
        );

        return $sheet;
    }

    public function saveImportSettings($data = null)
    {
        Request::ajaxRequestAccess();
        if (empty($data)) {
            $keys = array(
                "generatorCodeType", "generatorFieldType", "generatorCustomField", "chWithoutEanUpc", "chInstock", "chPriceGreater", "generatorCodeList",
                "productPageShortcode", "orderShortcode", "adminProductShortcode", "productShortcode", "adminOrderShortcode"
            );
            $data = (new Sanitize())::getData($keys);
        }

        $settings = new Settings();
        $settings->updateUserSettings(array(
            "settings-type" => "general",
            "generatorFieldType" => $data["generatorFieldType"],
            "generatorCustomField" => $data["generatorCustomField"],
            "productPageShortcode" => $data["productPageShortcode"],
            "orderShortcode" => $data["orderShortcode"],
            "productShortcode" => $data["productShortcode"],
            "adminProductShortcode" => $data["adminProductShortcode"],
            "adminOrderShortcode" => $data["adminOrderShortcode"],
        ), false);

        Files::resetAllTimestamps();
    }
}
