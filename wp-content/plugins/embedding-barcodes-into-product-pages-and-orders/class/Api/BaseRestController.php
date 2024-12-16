<?php

namespace UkrSolution\BarcodesDigital\Api;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

abstract class BaseRestController extends WP_REST_Controller
{
    protected $namespace = 'a4b/v1';
    protected $base;
    protected $table;
    protected $primaryKey = 'id';
    protected $defaultKey = 'default';
    protected $requestQueryArgs = array();
    protected $requestBodyArgs = array();

    public function register_routes()
    {
        register_rest_route($this->namespace, '/'.$this->base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'index'),
                'permission_callback' => array($this, 'permissionsCheck'),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'store'),
                'permission_callback' => array($this, 'permissionsCheck'),
                'args' => $this->requestBodyArgs,
            ),
        ));
        register_rest_route($this->namespace, '/'.$this->base.'/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'show'),
                'permission_callback' => array($this, 'permissionsCheck'),
                'args' => $this->requestQueryArgs,
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update'),
                'permission_callback' => array($this, 'permissionsCheck'),
                'args' => array_merge($this->requestQueryArgs, $this->requestBodyArgs),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'remove'),
                'permission_callback' => array($this, 'permissionsCheck'),
                'args' => $this->requestQueryArgs,
            ),
        ));
    }

    public function index($request)
    {
        global $wpdb;

        $result = $this->queryRows("
            SELECT * 
            FROM {$wpdb->prefix}{$this->table}
        ");

        return rest_ensure_response($result);
    }

    public function show($request)
    {
        global $wpdb;
        $params = $request->get_params();

        $result = $this->queryRow($wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}{$this->table}
            WHERE {$this->primaryKey} = %d
        ", $params[$this->primaryKey]));

        return $this->prepareShowResults(rest_ensure_response($result));
    }

    public function store($request)
    {
        global $wpdb;
        $params = $request->get_params();

        if (false !== $wpdb->insert("{$wpdb->prefix}{$this->table}", $params)) {
            $result = $this->queryRow("
                SELECT * 
                FROM {$wpdb->prefix}{$this->table}
                WHERE {$this->primaryKey} = {$wpdb->insert_id}
            ");
        } else {
            $result = new WP_Error('500', $wpdb->last_error, array('status' => 500));
        }

        return rest_ensure_response($result);
    }

    public function update($request)
    {
        global $wpdb;
        $params = $request->get_params();

        if (false !== $wpdb->update("{$wpdb->prefix}{$this->table}", $params, array($this->primaryKey => $params[$this->primaryKey]))) {
            $result = $this->queryRow("
                SELECT *
                FROM {$wpdb->prefix}{$this->table}
                WHERE {$this->primaryKey} = {$params[$this->primaryKey]}
                AND `{$this->defaultKey}` = 0
            ");
        } else {
            $result = new WP_Error('500', $wpdb->last_error, array('status' => 500));
        }

        return rest_ensure_response($result);
    }

    public function remove($request)
    {
        global $wpdb;
        $params = $request->get_params();

        $affectedRows = $wpdb->delete("{$wpdb->prefix}{$this->table}", array($this->primaryKey => $params[$this->primaryKey], $this->defaultKey => 0));

        if (1 === $affectedRows) {
            $result = new WP_REST_Response(null, 204);
        } elseif (0 === $affectedRows) {
            $result = new WP_Error('404', __('Not found'), array('status' => 404));
        } else {
            $result = new WP_Error('500', $wpdb->last_error, array('status' => 500));
        }

        return rest_ensure_response($result);
    }

    protected function prepareShowResults($result)
    {
        return $result;
    }

    public function permissionsCheck($request)
    {
        return current_user_can('export');
    }

    protected function queryRow($sql)
    {
        global $wpdb;
        $row = $wpdb->get_row($sql, ARRAY_A);

        if (!empty($row)) {
            return $row;
        } else {
            return $wpdb->last_error
                ? new WP_Error('500', $wpdb->last_error, array('status' => 500))
                : new WP_Error('404', __('Not found', 'wpbcu-barcode-generator', array('status' => 404)));
        }
    }

    protected function queryRows($sql)
    {
        global $wpdb;

        $rows = $wpdb->get_results($sql, ARRAY_A);

        return $wpdb->last_error ? new WP_Error('500', $wpdb->last_error, array('status' => 500)) : $rows;
    }

    public function prepareLinkUrl($parts = array())
    {
        array_unshift($parts, $this->namespace);

        return rest_url(implode('/', $parts));
    }
}
