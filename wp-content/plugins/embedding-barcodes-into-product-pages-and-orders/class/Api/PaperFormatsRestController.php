<?php

namespace UkrSolution\BarcodesDigital\Api;

use UkrSolution\BarcodesDigital\Database;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class PaperFormatsRestController extends BaseRestController
{
    protected $base = 'papers';

    public function register_routes()
    {
        $this->requestQueryArgs = array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'minimum' => 0,
            ),
        );
        $this->requestBodyArgs = array(
            'name' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'width' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 210,
            ),
            'height' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 297,
            ),
            'landscape' => array(
                'required' => true,
                'type' => 'boolean',
                'default' => 0,
            ),
        );

        parent::register_routes();
    }

    public function index($request)
    {
        global $wpdb;

        $tablePaperFormats = Database::$tablePaperFormats;

        $result = $this->queryRows("
            SELECT *
            FROM {$wpdb->prefix}{$tablePaperFormats}
            WHERE `name` <> 'future-reserved-paper-formats'
        ");

        return rest_ensure_response($result);
    }
}
