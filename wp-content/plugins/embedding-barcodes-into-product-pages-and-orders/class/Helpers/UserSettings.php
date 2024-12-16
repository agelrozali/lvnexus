<?php

namespace UkrSolution\BarcodesDigital\Helpers;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Database;
use UkrSolution\BarcodesDigital\Shortcodes;

class UserSettings
{
    protected static $generalSettings = null;

    public static function migrateSettings()
    {
        global $wpdb;

        $tableUserSettings = $wpdb->prefix . Database::$tableUserSettings;

        if (!self::getoption('codePrefix', '') && self::getoption('codePrefix', null) === null) {
            $codePrefix = get_option(Database::$optionSettingsCodePrefixKey, "");
            $wpdb->insert("{$tableUserSettings}", array('param' => 'codePrefix', 'value' => $codePrefix));
        }

        if (!self::getoption('cfPriority', '')) {
            $cfPriority = get_option(Database::$optionSettingsCfPriorityKey, "variation");
            $wpdb->insert("{$tableUserSettings}", array('param' => 'cfPriority', 'value' => $cfPriority));
        }

        if (!self::getoption('currencySymbol', '')) {
            $currencySymbol = get_option(Database::$optionSettingsCurrencySymbolKey, true);
            $wpdb->insert("{$tableUserSettings}", array('param' => 'currencySymbol', 'value' => $currencySymbol));
        }

        if (!self::getoption('lk', '')) {
            $lk = get_option(Database::$optionSettingsLK, '');
            $wpdb->insert("{$tableUserSettings}", array('param' => 'lk', 'value' => $lk));
        }

        $table =  $wpdb->prefix . Database::$tableTemplates;
        $wpdb->query("UPDATE {$table} AS T SET T.label_margin_top = T.base_padding_uol, T.label_margin_right = T.base_padding_uol , T.label_margin_bottom = T.base_padding_uol , T.label_margin_left = T.base_padding_uol WHERE T.label_margin_top IS NULL;");
    }

    public static function get()
    {
        global $wpdb;

        $userId = get_current_user_id();
        $tableUserSettings = $wpdb->prefix . Database::$tableUserSettings;

        $settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tableUserSettings} WHERE `userId` = %d;", $userId), ARRAY_A);

        return self::convertToJsFormat($settings);
    }

    public static function getGeneral()
    {
        global $wpdb;

        $result = self::$generalSettings;

        if ($result === null) {
            $tableUserSettings = $wpdb->prefix . Database::$tableUserSettings;
            $settings = $wpdb->get_results("SELECT * FROM {$tableUserSettings} WHERE `userId` IS NULL;", ARRAY_A);

            $result = self::convertToJsFormat($settings);

            if (!isset($result['booster_for_wc'])) {
                $result['booster_for_wc'] = 0;
            }
            if (!isset($result['wc_pdf_ips_status'])) {
                $result['wc_pdf_ips_status'] = 0;
            }

            if (!isset($result['cfPriority'])) {
                $result['cfPriority'] = 'variation';
                $result['currencySymbol'] = true;
                $result['gridOnPrint'] = false;
            }

            if (!isset($result['attrPriority'])) $result['attrPriority'] = 'one';

            if (!isset($result['defaultProfile'])) $result['defaultProfile'] = '';


            if (!isset($result['lwhFormat'])) $result['lwhFormat'] = '%L x %W x %H';

            if (!isset($result['customCheckboxSelector'])) {
            }

            if (!isset($result['customButtonSelector'])) {
            }

            if (!isset($result['attributeIsntSpec'])) {
                $result['attributeIsntSpec'] = 'nothing';
            }

            if (!isset($result['clearProfileLabels'])) {
                $result['clearProfileLabels'] = 1;
            }

            if (!isset($result['excludedProdStatuses'])) {
                $result['excludedProdStatuses'] = "";
            }

            self::$generalSettings = $result;
        }

        return $result;
    }

    public static function getoption($option, $default = '')
    {
        $settings = self::getGeneral();

        $value = (isset($settings[$option])) ? $settings[$option] : $default;

        return $value;
    }

    public static function getJsonSectionOption($option, $type = 'product', $isEnabled = 0)
    {
        global $wpdb;

        $settings = self::getGeneral();
        $value = array();

        if ((isset($settings[$option]))) {
            $value = @json_decode($settings[$option], true);

            if (!$value) {
                $value = array();
            }
        }

        if (empty($value)) {
            $tableShortcodes = $wpdb->prefix . Database::$tableShortcodes;

            $sid = $type === "product" ? 1 : 2;

            $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$tableShortcodes}` WHERE `id` = %d;", $sid));

            if (!$shortcode) return $value;

            $shortcodes = new Shortcodes();
            $shortcode = $shortcodes->prepareShortcodeMatching($shortcode);
            $settings = $shortcode->matching ? (array)$shortcode->matching : array();

            if ($settings && isset($settings['template'])) {
                $templatesController = new BarcodeTemplatesController();
                $template = $templatesController->getTemplateById($settings['template']);

                if (!$template) {
                    $template = $templatesController->getTemplateById(1);
                }

                if (!$template) return $value;

                $value = array(
                    "status" => $isEnabled,
                    "width" => $template->width,
                    "height" => $template->height,
                    "position" => "",
                    "shortcode" => $sid,
                );
            }
        }

        return $value;
    }

    private static function convertToJsFormat($settings)
    {
        $data = array();

        foreach ($settings as $value) {
            if (in_array($value["param"], ['paper_sheet', 'product_paper_sheet', 'order_paper_sheet'])) {
                $data[$value["param"]] = @json_decode($value["value"]);
            } else {
                $data[$value["param"]] = $value["value"];
            }
        }

        return $data;
    }
}
