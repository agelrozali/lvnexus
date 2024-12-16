<?php

namespace AgeGate\App;

class I18n
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'registerDomain']);
    }

    public function registerDomain()
    {
        load_plugin_textdomain(
            'age-gate',
            false,
            AGE_GATE_SLUG . '/language/'
        );

    }
}
