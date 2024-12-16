<?php

define('USWBG_PLUGIN_PLAN', 'DEMO');




define('USWBG_PLUGIN_TYPE', 'DIGITAL');

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    $_SERVER['HTTPS'] = 'on';
}

define('USWBG_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));

define('USWBG_PLUGIN_BASE_PATH', plugin_dir_path(__FILE__));

define('USWBG_SITE_BASE_URL', get_site_url());
