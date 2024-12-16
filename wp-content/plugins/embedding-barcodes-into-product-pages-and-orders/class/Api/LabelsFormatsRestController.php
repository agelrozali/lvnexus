<?php

namespace UkrSolution\BarcodesDigital\Api;

use UkrSolution\BarcodesDigital\Database;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class LabelsFormatsRestController extends BaseRestController
{
    protected $base = 'labels';

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
            'userId' => array(
                'required' => true,
                'type' => 'integer',
                'minimum' => 0,
                'default' => get_current_user_id(),
            ),
            'width' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 70,
            ),
            'height' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 67.7,
            ),
            'around' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 0,
            ),
            'across' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 0,
            ),
            'marginLeft' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 0,
            ),
            'marginRight' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 0,
            ),
            'marginTop' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 13,
            ),
            'marginBottom' => array(
                'required' => true,
                'type' => 'number',
                'minimum' => 0,
                'default' => 13,
            ),
            'aroundCount' => array(
                'required' => true,
                'type' => 'integer',
                'minimum' => 1,
                'default' => 4,
            ),
            'acrossCount' => array(
                'required' => true,
                'type' => 'integer',
                'minimum' => 1,
                'default' => 3,
            ),
            'paperId' => array(
                'required' => true,
                'type' => 'integer',
                'minimum' => 1,
            ),
        );

        parent::register_routes();
    }

    public function show($request)
    {
        global $wpdb;

        $params = $request->get_params();
        $tableLabelSheets = Database::$tableLabelSheets;
        $tablePaperFormats = Database::$tablePaperFormats;

        $result = $this->queryRow($wpdb->prepare("
            SELECT cf.*, pf.uol
            FROM {$wpdb->prefix}{$tableLabelSheets} AS cf
            LEFT JOIN {$wpdb->prefix}{$tablePaperFormats} AS pf
                ON pf.id = cf.paperId
            WHERE cf.{$this->primaryKey} = %d
        ", $params[$this->primaryKey]));

        return $this->prepareShowResults(rest_ensure_response($result));
    }

    protected function prepareShowResults($result)
    {
        if (is_a($result, 'WP_REST_Response')) {
            $result->add_link(
                'paper',
                $this->prepareLinkUrl(array('papers', $result->data['paperId'])),
                array('embeddable' => true)
            );
        }

        return $result;
    }
}
