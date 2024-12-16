<?php

namespace UkrSolution\BarcodesDigital\Helpers;

use UkrSolution\BarcodesDigital\Database;

class Files
{
    public static function createFile($itemId, $shortcodeId, $parentItemId, $type, $shortcodeTimestamp, $itemTimestamp, $templateTimestamp, $hash = null)
    {
        global $wpdb;

        $tableFiles = $wpdb->prefix . Database::$tableFiles;

        if ($hash === null) {
            $existingRecord = self::get($itemId, $shortcodeId, $parentItemId, $type);
        } else {
            $existingRecord = self::getByHash($hash, $shortcodeId, $type);
        }

        if ($existingRecord) {
            $id = $existingRecord->id;
        } else {
            $wpdb->insert("{$tableFiles}", array(
                'itemId' => $itemId,
                'shortcodeId' => $shortcodeId,
                'parentItemId' => $parentItemId,
                'type' => $type,
                'shortcodeTimestamp' => $shortcodeTimestamp,
                'templateTimestamp' => $templateTimestamp,
                'itemTimestamp' => $itemTimestamp,
                'version' => 0,
                'hash' => $hash,
            ));
            $id = $wpdb->insert_id;
        }


        $config = require __DIR__ . '/../../config/config.php';
        $uploadDirData = wp_upload_dir();

        $subFolder = $type . 's' . '/' . $shortcodeId;

        if ($parentItemId) {
            $subFolder .= '/' . $parentItemId;
        }

        $filePath = $uploadDirData['basedir'] . '/' . $config['uploads'] . '/' . $subFolder;

        $fileName =  $itemId . '.png';

        wp_mkdir_p($filePath);

        $wpdb->update($tableFiles, array(
            'shortcodeTimestamp' => $shortcodeTimestamp,
            'templateTimestamp' => $templateTimestamp,
            'itemTimestamp' => $itemTimestamp,
            'path' => $subFolder . '/' . $fileName,
        ), array('id' => $id));

        return self::getById($id);
    }

    public static function get($itemId, $shortcodeId, $parentItemId, $type)
    {
        global $wpdb;

        $tableFiles = $wpdb->prefix . Database::$tableFiles;

        if ($parentItemId) {
            $file = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$tableFiles} WHERE `itemId` = %s AND `shortcodeId` = %d AND `parentItemId` = %d AND `type` = %s;",
                    $itemId,
                    $shortcodeId,
                    $parentItemId,
                    $type
                )
            );
        } else {
            $file = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$tableFiles} WHERE `itemId` = %s AND `shortcodeId` = %d AND `type` = %s;",
                    $itemId,
                    $shortcodeId,
                    $type
                )
            );
        }

        return $file;
    }

    public static function getByHash($hash, $shortcodeId, $type)
    {
        global $wpdb;

        $tableFiles = $wpdb->prefix . Database::$tableFiles;

        $file = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$tableFiles} WHERE `hash` = %s AND `shortcodeId` = %d AND `type` = %s;",
                $hash,
                $shortcodeId,
                $type
            )
        );

        return $file;
    }

    public static function getById($id)
    {
        global $wpdb;

        $tableFiles = $wpdb->prefix . Database::$tableFiles;

        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tableFiles} WHERE `id` = %d;", $id));

        return $file;
    }

    public static function checkFile($itemId, $shortcodeId, $parentItemId, $type, $shortcodeTimestamp, $itemTimestamp, $templateTimestamp, $hash = null)
    {
        if ($hash === null) {
            $fileRecord = self::get($itemId, $shortcodeId, $parentItemId, $type);
        } else {
            $fileRecord = self::getByHash($hash, $shortcodeId, $type);
        }

        if (!$fileRecord) return '';


        $config = require __DIR__ . '/../../config/config.php';
        $uploadDirData = wp_upload_dir();

        if (!file_exists($uploadDirData['basedir'] . '/' . $config['uploads'] . '/' . $fileRecord->path)) {
            return false;
        }

        if (
            (int)$fileRecord->shortcodeTimestamp !== (int)$shortcodeTimestamp
            || (int)$fileRecord->itemTimestamp !== (int)$itemTimestamp
            || (int)$fileRecord->templateTimestamp !== (int)$templateTimestamp
        ) {
            return false;
        }

        $randomStr = md5($shortcodeTimestamp . $itemTimestamp . $templateTimestamp);

        return '/' . $config['uploads'] . '/' . $fileRecord->path . '?r=' . $randomStr;
    }


    public static function updateVersion($id, $version = 1)
    {
        global $wpdb;

        $version = 0;

        $tableFiles = $wpdb->prefix . Database::$tableFiles;
        $wpdb->update($tableFiles, array('version' => $version), array('id' => $id));

        return true;
    }

    public static function resetAllTimestamps()
    {
        global $wpdb;

        $tableFiles = $wpdb->prefix . Database::$tableFiles;

        $wpdb->query(
            $wpdb->prepare("UPDATE $tableFiles SET `itemTimestamp` = %d, `shortcodeTimestamp` = %d, `templateTimestamp` = %d", 0, 0, 0)
        );
    }
}
