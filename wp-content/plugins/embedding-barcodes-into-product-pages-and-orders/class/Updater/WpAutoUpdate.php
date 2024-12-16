<?php

namespace UkrSolution\BarcodesDigital\Updater;

if (!class_exists("WpAutoUpdate")) {

    class WpAutoUpdate
    {
        private $current_version;
        private $update_path;
        private $plugin_path;
        private $plugin_slug;
        private $slug;
        private $session_id;
        private $license_key;
        private $loaded = false;
        private $loadedData = null;

        public function __construct($current_version, $update_path, $plugin_slug, $session_id = '', $license_key = '')
        {
            $this->current_version = $current_version;
            $this->update_path = $update_path;
            $this->loaded = false;
            $this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));

            $this->session_id = 'ef54e760210d8937045c33643b91eded';

            $this->license_key = $license_key;

            $this->plugin_slug = $plugin_slug;
            list($t1, $t2) = explode('/', $plugin_slug);
            $this->slug = str_replace('.php', '', $t2);

            if (is_admin()) {
                add_filter('site_transient_update_plugins', array(&$this, 'checkUpdate'));
            }

            add_filter('plugins_api', array(&$this, 'checkInfo'), 10, 3);
        }

        public function checkUpdate($transient)
        {
            try {
                if (empty($transient->checked)) {
                    return $transient;
                }

                $prefix = 'ukrsolution_upgrade_digital_barcodes_';

                if ($this->loaded === false) {
                    $remoteCache = get_transient($prefix . $this->current_version);

                    if ($remoteCache) {
                        $remoteData = $remoteCache;
                    } else {
                        $remoteData = $this->getRemote('version');

                        if ($remoteData && !is_wp_error($remoteData) && isset($remoteData->new_version)) {
                            set_transient($prefix . $this->current_version, $remoteData, 86400);
                        }
                    }

                    if ($remoteData && version_compare($this->current_version, $remoteData->new_version, '<')) {
                        $obj = new \stdClass();
                        $obj->slug = basename($this->plugin_path); 
                        $obj->plugin_name = basename($this->plugin_path);
                        $obj->name = 'Barcode Generator - create barcodes for products, orders, emails';
                        $obj->new_version = $remoteData->new_version;
                        $obj->version = $remoteData->new_version;
                        $obj->url = $remoteData->url;
                        $obj->plugin = basename($this->plugin_path); 
                        $obj->package = $remoteData->package;
                        $obj->tested = $remoteData->tested;

                        if (isset($remoteData->icons)) {
                            $obj->icons = $remoteData->icons;
                        }

                        $transient->response[$this->plugin_slug] = $obj;
                        $this->loadedData = $obj;
                    }
                    if (isset($remoteData->update_error_message) && $remoteData->update_error_message) {
                        $getParam = 'usbd-plugin-dismissed';

                        add_action('admin_notices', function () use ($remoteData, $getParam) {
                            $user_id = get_current_user_id();

                            if (get_option($user_id . '_' . basename($this->plugin_path) . '_notice_dismissed') !== "true") {
                                $class = 'notice notice-error ';
                                $page = isset($_GET["page"]) ? $_GET["page"] : '';
                                $url = admin_url("admin.php?page=" . $page . "&" . $getParam);

                                $link = '<a href="' . admin_url("admin.php?page=wpbcu-barcode-settings") . '#license">License key</a>';
                                $errorMessage = str_replace("License key", "your {$link}", $remoteData->update_error_message);

                                printf('<div style="position: relative;" class="%1$s"><p>Barcode Generator - create barcodes for products, orders, emails Plugin: %2$s</p><a href="' . $url . '"><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></a></div>', esc_attr($class), $errorMessage);
                            }
                        });
                        add_action('admin_init', function () use ($getParam) {
                            $user_id = get_current_user_id();

                            if (isset($_GET[$getParam])) {
                                update_option($user_id . '_' . basename($this->plugin_path) . '_notice_dismissed', 'true', true);
                            }
                        });
                    }

                    $this->loaded = true;
                } elseif ($this->loadedData) {
                    $transient->response[$this->plugin_slug] = $this->loadedData;
                }

                return $transient;
            } catch (\Throwable $th) {
                return $transient;
            }
        }

        public function checkInfo($obj, $action, $arg)
        {
            if (isset($arg->slug) && $arg->slug !== basename($this->plugin_path)) {
                return $obj;
            }

            if ($action == 'plugin_information') {
                return $this->getRemote('info');
            }

            return $obj;
        }

        public function getRemote($action = '')
        {
            $params = array(
                'timeout' => 10,
                'body' => array(
                    'action' => $action,
                    'session_id' => $this->session_id,
                    'license_key' => $this->license_key,
                    'domain' => get_site_url(),
                    'pn' => "demo",
                ),
            );

            $request = wp_remote_post($this->update_path, $params);

            if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
                $serverData = @unserialize($request['body']);
                $serverData->slug = basename($this->plugin_path);
                $serverData->plugin = basename($this->plugin_path);
                $serverData->plugin_name = basename($this->plugin_path);
                return $serverData;
            } else {
                $folder = basename($this->plugin_path);
                $obj = new \stdClass();
                $obj->slug = $folder;
                $obj->name = 'Barcode Generator - create barcodes for products, orders, emails';
                $obj->plugin_name = $folder;
                $obj->new_version = '';
                $obj->tested = '';
                $obj->url = 'https://www.ukrsolution.com/Joomla/A4-BarCode-Generator-For-Wordpress';
                $obj->package = '';
                $obj->requires = '5.0';
                $obj->downloaded = 0;
                $obj->last_updated = "";
                $obj->sections = array(
                    'description' => 'To upgrade plugin:<br><br>
                    1. Go to your account on <a href="https://www.ukrsolution.com/Joomla/A4-BarCode-Generator-For-Wordpress" target="_blank">www.ukrsolution.com</a><br>
                    2. Download new version.<br>
                    3. Remove old one from your wordpress.<br>
                    4. Install new version.<br>
                    5. All setting from old to new version will be transferred automatically.',
                );
                $obj->download_link = $obj->package;
                return $obj;
            }

            return false;
        }
    }
}
