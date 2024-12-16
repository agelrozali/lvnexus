<?php

namespace AgeGate\App;

use AgeGate\Common\Content;
use AgeGate\Common\Settings;
use AgeGate\Integration\Discover;
use AgeGate\Controller\JsController;
use AgeGate\Controller\StandardController;

class AgeGate
{
    protected static $content;

    public function __construct()
    {
        add_action('wp', [$this, 'init'], PHP_INT_MAX);
    }

    public function init()
    {
        if (is_admin()) {
            return;
        }

        new Discover();

        $settings = Settings::getInstance();

        do_action('age_gate/settings', $settings);

        if ($settings->disableAgeGate) {
            return;
        }

        $content = new Content();
        self::$content = apply_filters('age_gate/init/content', $content);

        if (!is_a(self::$content, 'AgeGate\\Common\\Content')) {
            _doing_it_wrong( 'age_gate/init/content', 'Must return instance of AgeGate\\Common\\Content', '3.4.0');
            self::$content = $content;
        }

        if ($settings->method === 'js') {
            new JsController(self::$content);
        } else {
            new StandardController(self::$content);
        }
    }

    public static function getContent()
    {
        return self::$content;
    }
}
