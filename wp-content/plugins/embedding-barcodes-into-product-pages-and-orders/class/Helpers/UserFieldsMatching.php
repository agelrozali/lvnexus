<?php

namespace UkrSolution\BarcodesDigital\Helpers;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\Database;

class UserFieldsMatching
{
    private static $activeTemplate = null;

    public static function get()
    {
        global $wpdb;

        try {
            $userId = get_current_user_id();
            $tableFieldsMatching = $wpdb->prefix . Database::$tableFieldsMatching;

            $settings = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$tableFieldsMatching} WHERE `userId` = %d;", $userId),
                ARRAY_A
            );

            $data = array();

            foreach ($settings as $value) {
                $data[$value["field"]] = json_decode($value["matching"]);
            }
        } catch (\Throwable $th) {
            return $data;
        }

        return $data;
    }

    public static function prepareFieldValue($isAddFieldName, $fieldName, $value, $lineNumber)
    {
        if (!self::$activeTemplate) {
            $BarcodeTemplatesController = new BarcodeTemplatesController();
            self::$activeTemplate = $BarcodeTemplatesController->getActiveTemplate();
        }

        if ($lineNumber && self::$activeTemplate && self::$activeTemplate->is_base && self::$activeTemplate->is_default) {
            if (!empty($value)) {
                if ($isAddFieldName && !empty($fieldName)) {
                    return  $fieldName . ':' . $value;
                } else {
                    return  $value;
                }
            } else {
                return '';
            }
        } else {
            if ($isAddFieldName && !empty($fieldName) && !empty($value)) {
                return $fieldName . ':' . $value;
            } else {
                return $value;
            }
        }
    }
}
