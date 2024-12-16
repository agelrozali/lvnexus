<?php

use AgeGate\App\I18n;
use AgeGate\Admin\Ajax;
use AgeGate\Admin\Admin;
use AgeGate\App\AgeGate;
use AgeGate\Admin\Update;
use AgeGate\Enqueue\Enqueue;
use AgeGate\Legacy\Deprecated;
use AgeGate\Legacy\Check as LegacyCheck;
use AgeGate\Routes\Rest\Check;
use AgeGate\Shortcode\Shortcode;
use AgeGate\Presentation\Template;
use AgeGate\Routes\Rest\Developer;
use AgeGate\Routes\Rest\Admin\Term;

add_action('wp', function() {
    if (apply_filters('age_gate/include_deprecated_hooks', false) === true) {
        new Deprecated;
    }
}, 0);

new Admin;
new Ajax;
new Update;

new AgeGate;
new I18n;

new Template;

new Check;
new LegacyCheck;
// new Routes\Rest\Media();
new Developer;
new Term;

new Shortcode;

new Enqueue;

