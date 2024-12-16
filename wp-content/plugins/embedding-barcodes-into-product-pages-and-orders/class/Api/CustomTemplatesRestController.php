<?php

namespace UkrSolution\BarcodesDigital\Api;

use DOMDocument;
use UkrSolution\BarcodesDigital\Database;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CustomTemplatesRestController extends BaseRestController
{
    protected $base = 'templates';
    protected $defaultKey = 'is_default';

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
            'template' => array(
                'required' => true,
                'type' => 'string',
                'validate_callback' => array($this, 'validateXml'),
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
        );

        parent::register_routes();

        register_rest_route($this->namespace, '/' . $this->base . '/active', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'active'),
                'permission_callback' => array($this, 'permissionsCheck'),
            ),
        ));
    }

    public function validateXml($param, $request, $key)
    {
        $useErrors = libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $success = $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?><root>' . $param . '</root>');

        libxml_use_internal_errors($useErrors);

        return $success;
    }

    public function active($request)
    {
        global $wpdb;

        $tableTemplates = Database::$tableTemplates;

        $result = $this->queryRow("
            SELECT *
            FROM {$wpdb->prefix}{$tableTemplates}
            WHERE is_active = 1
        ");

        return rest_ensure_response($result);
    }
}
