<?php

namespace AgeGate\Admin;

use AgeGate\Admin\Controller\ContentController;
use Asylum\Utility\Notice;
use AgeGate\Admin\Taxonomy\TermHelper;
use AgeGate\Common\Immutable\Constants;

class Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_ag_clear_legacy_css', [$this, 'removeLegacyCss']);
        add_action('wp_ajax_age_gate_store_terms', [$this, 'storeTerms']);
    }

    public function removeLegacyCss()
    {
        $data = [];
        if (!current_user_can(Constants::ADVANCED) || !wp_verify_nonce($_POST['nonce'], 'ag_clear_css' )) {
            $data['status'] = 'Not allowed';
            $code = 401;
        } else {
            delete_option('age_gate_legacy_css');
            Notice::add(__('Legacy CSS removed'), 'success');
            $data['status'] = 'ok';
            $code = 200;
        }

        wp_send_json($data, $code);
        wp_die();
    }

    public function storeTerms()
    {
        if (!current_user_can( Constants::CONTENT)) {
            wp_send_json_error( [], 401);
        }

        $options = get_option(ContentController::OPTION, []);

        if ($_POST['idx'] == 0) {
            $options['terms'] = [];
        }

        $options['terms'] = array_merge($options['terms'] ?? [], $_POST['ag_settings'] ?? []);

        update_option(ContentController::OPTION, $options);

        wp_send_json([
            'terms' => $options['terms'],
        ]);
    }
}
