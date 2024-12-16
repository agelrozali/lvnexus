<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use UkrSolution\BarcodesDigital\Helpers\Variables;

class PrintPage
{
    public static function displayPrint()
    {
    }

    private static function render($tableTemplates, $tableTemplateToUser, $fileName)
    {
        global $wpdb;
        global $current_user;

        $userId = get_current_user_id();

        $userTemplate = self::getUserTemplate($tableTemplateToUser, $userId);
        $chosenTemplateRow = self::getChosenTemplateRow($userTemplate, $tableTemplates);

        $generalSettings = UserSettings::getGeneral();
        if (!$generalSettings) {
            $generalSettings = array();
        }


        $userSettings = UserSettings::get();

        $websiteUrl = get_bloginfo("url");
        $uid = get_current_user_id();

        $Dimensions = new Dimensions();
        $dimensions = $Dimensions->get(false);

        $jsL10n = require Variables::$A4B_PLUGIN_BASE_PATH . 'config/jsL10n.php';

        include_once Variables::$A4B_PLUGIN_BASE_PATH . 'templates/' . $fileName . '.php';
        die();
    }

    private static function getUserTemplate($tableTemplateToUser, $userId)
    {
        global $wpdb;

        $userTemplate = null;

        $userTemplate = $wpdb->get_row("SELECT * FROM `{$tableTemplateToUser}` WHERE `userId` = {$userId}");

        return $userTemplate;
    }

    private static function getChosenTemplateRow($userTemplate, $tableTemplates)
    {
        global $wpdb;

        if ($userTemplate) {
            $chosenTemplateRow = $wpdb->get_row("SELECT * FROM `{$tableTemplates}` WHERE `id` = {$userTemplate->templateId}");
        } else {
            $chosenTemplateRow = $wpdb->get_row("SELECT * FROM `{$tableTemplates}` WHERE `slug` = 'default-1'");
        }

        $dimensions = new Dimensions();
        $activeDimension = $dimensions->getActive();

        if ($chosenTemplateRow->is_base && $chosenTemplateRow->is_default && $activeDimension) {
            $chosenTemplateRow->uol_id = $activeDimension;
        }

        return $chosenTemplateRow;
    }
}
