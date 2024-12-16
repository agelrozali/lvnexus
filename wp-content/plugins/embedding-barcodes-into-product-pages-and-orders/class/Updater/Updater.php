<?php

namespace UkrSolution\BarcodesDigital\Updater;

use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use UkrSolution\BarcodesDigital\Helpers\Variables;

class Updater
{
    public function __construct()
    {
        $this->initServer();
    }

    private function initServer()
    {
        add_action('init', function () {
            try {
                $generalSettings = UserSettings::getGeneral();
                $plugin_current_version = '2.0.2';
                $plugin_slug = Variables::$A4B_PLUGIN_BASE_NAME;
                $plugin_remote_path = 'https://www.ukrsolution.com/CheckUpdates/BarcodeGenerator-v2.json';
                $license_user = 'ef54e760210d8937045c33643b91eded';
                $license_key = ($generalSettings && isset($generalSettings["lk"])) ? $generalSettings["lk"] : "";
                new WpAutoUpdate($plugin_current_version, $plugin_remote_path, $plugin_slug, $license_user, $license_key);
            } catch (\Throwable $th) {
            }
        });
    }
}
