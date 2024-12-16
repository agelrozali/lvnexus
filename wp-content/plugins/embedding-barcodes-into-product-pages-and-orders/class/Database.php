<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\Files;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

require_once ABSPATH . 'wp-admin/includes/upgrade.php'; 

class Database
{


    public static $tablePaperFormats = "barcode_gen2_paper_formats";
    public static $tableLabelSheets = "barcode_gen2_label_sheets";
    public static $tableTemplates = "barcode_gen2_templates";
    public static $tableTemplateToUser = "barcode_gen2_template_to_user";
    public static $tableFieldsMatching = "barcode_gen2_fields_matching";
    public static $tableUserSettings = "barcode_gen2_user_settings";
    public static $tableProfiles = "barcode_gen2_profiles";
    public static $tableShortcodes = "barcode_gen2_shortcodes";
    public static $tableFiles = "barcode_gen2_files";
    public static $tableCodesFiles = "barcode_gen2_codes_files";
    public static $tableCodes = "barcode_gen2_codes";
    public static $tableDimension = "barcode_gen2_dimension";

    public static $optionSettingsOrdersKey = "barcode_gen2_settings_orders";
    public static $optionSettingsProductsKey = "barcode_gen2_settings_products";
    public static $optionSettingsCodePrefixKey = "barcode_gen2_generator_barcode_prefix";
    public static $optionSettingsCfPriorityKey = "barcode_gen2_generator_custom_fields_priority";
    public static $optionSettingsCurrencySymbolKey = "barcode_gen2_generator_currency_symbol";
    public static $optionSettingsLK = "barcode_gen2_generator_lk";
    public static $optionSettings = "barcode_gen2_settings";
    public static $optionPostImageSize = "barcode_gen2_post_image_size";

    public static function checkTables()
    {
        global $wpdb;

        try {
            $db = $wpdb->dbname;
            $key = "Tables_in_{$db}";
            $plTables = array();


            $plTables = array(
                $wpdb->prefix . self::$tableTemplates,
                $wpdb->prefix . self::$tableTemplateToUser,
                $wpdb->prefix . self::$tableFieldsMatching,
                $wpdb->prefix . self::$tableUserSettings,
                $wpdb->prefix . self::$tableShortcodes,
                $wpdb->prefix . self::$tableFiles,
                $wpdb->prefix . self::$tableCodesFiles,
                $wpdb->prefix . self::$tableCodes,
                $wpdb->prefix . self::$tableDimension,
            );

            $result = $wpdb->get_results("SHOW TABLES");
            $tables = array();

            foreach ($result as $value) {
                $tables[] = $value->$key;
            }

            if (count(array_diff($plTables, $tables)) > 0) {
                self::createTables();
            }
        } catch (\Throwable $th) {
        }
    }


    public static function setupTables($network_wide)
    {
        global $wpdb;

        if (is_multisite() && $network_wide) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                self::createTables();
                restore_current_blog();
            }
        } else {
            self::createTables();
        }

        self::createTables();

        UserSettings::migrateSettings();

        Files::resetAllTimestamps();
    }

    public static function createTables()
    {
        self::setupFormatsTables();
        self::setupTemplatesTable();
        self::setDefaultValues();
    }

    protected static function setupFormatsTables()
    {
        global $wpdb;



        $tblTemplateToUser = $wpdb->prefix . self::$tableTemplateToUser;
        $sql = "CREATE TABLE `{$tblTemplateToUser}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` BIGINT(20) DEFAULT NULL,
            `templateId` int(10) DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $tblFieldsToUser = $wpdb->prefix . self::$tableFieldsMatching;
        $sql = "CREATE TABLE `{$tblFieldsToUser}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` BIGINT(20) DEFAULT NULL,
            `field` varchar(255) DEFAULT NULL,
            `matching` LONGTEXT DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $tblUserSettings = $wpdb->prefix . self::$tableUserSettings;
        $sql = "CREATE TABLE `{$tblUserSettings}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` BIGINT(20) DEFAULT NULL,
            `param` varchar(255) DEFAULT NULL,
            `value` LONGTEXT DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);


        $tblShortcodes = $wpdb->prefix . self::$tableShortcodes;
        $sql = "CREATE TABLE `{$tblShortcodes}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` BIGINT(20) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `shortcode` text DEFAULT NULL,
            `matching` LONGTEXT DEFAULT NULL,
            `type` varchar(50) DEFAULT NULL,
            `is_default` tinyint(1) DEFAULT NULL,
            `is_edit` tinyint(1) DEFAULT 1,
            `is_base` tinyint(1) DEFAULT 0,
            `slug` varchar(255) DEFAULT '',
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $tblFiles = $wpdb->prefix . self::$tableFiles;
        $sql = "CREATE TABLE `{$tblFiles}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `itemId` int(11) DEFAULT NULL,
            `hash` varchar(255) DEFAULT NULL,
            `shortcodeId` int(11) DEFAULT NULL,
            `parentItemId` int(11) DEFAULT NULL,
            `type` varchar(255) DEFAULT NULL,
            `path` varchar(255) DEFAULT NULL,
            `itemTimestamp` int(11) DEFAULT NULL,
            `shortcodeTimestamp` int(11) DEFAULT NULL,
            `templateTimestamp` int(11) DEFAULT NULL,
            `version` int(11) DEFAULT 0,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $CodesFiles = $wpdb->prefix . self::$tableCodesFiles;
        $sql = "CREATE TABLE `{$CodesFiles}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `filename` varchar(255) DEFAULT NULL,
            `md5` varchar(255) DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $tblCodes = $wpdb->prefix . self::$tableCodes;
        $sql = "CREATE TABLE `{$tblCodes}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `fileId` int(11) DEFAULT NULL,
            `code` varchar(255) DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $tblDimension = $wpdb->prefix . self::$tableDimension;
        $sql = "CREATE TABLE `{$tblDimension}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `label` varchar(255) DEFAULT NULL,
            `is_default` tinyint(1) DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $dataDimension = require __DIR__ . '/../config/dimension.php';


        $dataShortcodes = require __DIR__ . '/../config/shortcodes.php';

        for ($i = 0; $i < count($dataDimension); ++$i) {
            $shortcode = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM `{$tblDimension}` WHERE (`id` = %s);",
                    array($dataDimension[$i]['id'])
                )
            );

            if (!$shortcode) {
                $wpdb->insert($tblDimension, $dataDimension[$i]);
            }
        }


        for ($i = 0; $i < count($dataShortcodes); ++$i) {
            if ($dataShortcodes[$i]["type"] === "custom") {
                unset($dataShortcodes[$i]["id"]);

                $shortcode = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM `{$tblShortcodes}` WHERE (`slug` = %s);",
                        array($dataShortcodes[$i]['slug'])
                    )
                );
            } else {
                $shortcode = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM `{$tblShortcodes}` WHERE (`id` = %s);",
                        array($dataShortcodes[$i]['id'])
                    )
                );
            }

            if (!$shortcode) {
                $wpdb->insert($tblShortcodes, $dataShortcodes[$i]);

                if ($dataShortcodes[$i]["type"] === "custom") {
                    $wpdb->update(
                        $tblShortcodes,
                        array(
                            'shortcode' => str_replace("SHORTCODE_ID", $wpdb->insert_id, $dataShortcodes[$i]["shortcode"]),
                        ),
                        array('id' => $wpdb->insert_id)
                    );
                }
            }
        }
    }

    protected static function setupTemplatesTable()
    {
        global $wpdb;

        $tbl = $wpdb->prefix . self::$tableTemplates;

        $sql = "CREATE TABLE `{$tbl}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `type` varchar(255) DEFAULT 'label',
            `name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `template` TEXT,
            `matching` LONGTEXT DEFAULT NULL,
            `matchingType` varchar(255) DEFAULT NULL,
            `readonlyMatching` tinyint(1) NOT NULL DEFAULT '0',
            `is_default` tinyint(1) NOT NULL DEFAULT '0',
            `is_base` tinyint(1) NOT NULL DEFAULT '0',
            `height` decimal(12,4) DEFAULT 37 NULL,
            `width` decimal(12,4) DEFAULT 70 NULL,
            `uol_id` int(11) DEFAULT 1 NULL,
            `base_padding_uol` decimal(12,4) NULL,
            `label_margin_top` decimal(12,4) NULL,
            `label_margin_right` decimal(12,4) NULL,
            `label_margin_bottom` decimal(12,4) NULL,
            `label_margin_left` decimal(12,4) NULL,
            `code_match` TINYINT(4) DEFAULT 0  NOT NULL,
            `single_product_code` TEXT,
            `variable_product_code` TEXT,
            `fontStatus` TINYINT(4) DEFAULT 1 NOT NULL,
            `fontTagLink` TEXT,
            `fontCssRules` TEXT,
            `customCss` TEXT,
            `jsAfterRender` TEXT,
            `barcode_type` varchar(255) DEFAULT NULL,
            `logo` varchar(255) DEFAULT NULL,
            `senderAddress` LONGTEXT DEFAULT NULL,
            `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $data = require __DIR__ . '/../config/templates.php';

        foreach ($data as $datum) {
            $datum['template'] = preg_replace('/\[logo_img_url]/', plugin_dir_url(dirname(__FILE__)) . 'assets/img/amazon-200x113.jpg', $datum['template']);

            $datumExists = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `{$tbl}` WHERE `slug` = %s AND `is_default` = 1", array($datum['slug']))
            );

            if ($datumExists) {
                $wpdb->update(
                    $tbl,
                    array(
                        'name' => $datum['name'],
                        'template' => $datum['template'],
                        'uol_id' => $datum['uol_id'],
                    ),
                    array('slug' => $datum['slug'], 'is_default' => 1)
                );
            } else {
                $wpdb->insert($tbl, $datum);
            }
        }
    }

    protected static function setDefaultValues()
    {
        global $wpdb;

        $tbl = $wpdb->prefix . self::$tableTemplates;
        $templates = $wpdb->get_results("SELECT * FROM `{$tbl}` WHERE `is_default` = 1 AND `is_base` = 1", ARRAY_A);

        foreach ($templates as $template) {
            if (!$template['matching']) {
                $defMatching = '{"lineBarcode":{"value":"ID","label":"Product Id","type":"standart","fieldType":"standart","customType":"label"},"fieldLine1":{"value":"post_title","label":"Name","type":"standart","fieldType":"standart","customType":"label"},"fieldLine2":{"value":"_price","label":"Actual price","type":"custom","fieldType":"standart","customType":"label"},"fieldLine3":{"value":"ID","label":"Product Id","type":"standart","fieldType":"standart","customType":"label"},"fieldLine4":{"value":"wc_category","label":"Category","type":"wc_category","fieldType":"standart","customType":"label"}}';
                $defMatchingType = 'products';
                $defBarcodeType = 'C128';

                $wpdb->update(
                    $tbl,
                    array('matching' => $defMatching, 'matchingType' => $defMatchingType, 'barcode_type' => $defBarcodeType,),
                    array('id' => $template['id'])
                );
            }
        }
    }
}
