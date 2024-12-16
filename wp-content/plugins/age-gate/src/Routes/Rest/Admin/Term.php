<?php

namespace AgeGate\Routes\Rest\Admin;

use AgeGate\Admin\Controller\ContentController;
use AgeGate\Admin\Taxonomy\TermHelper;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use AgeGate\Common\Immutable\Constants;

class Term extends WP_REST_Controller
{
    protected $namespace = 'age-gate/v3';

    protected $perPage = 20;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register']);
    }

    /**
     * Register the rest route
     *
     * @return void
     */
    public function register()
    {
        register_rest_route(
            $this->namespace,
            'admin/terms',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'response'],
                'permission_callback' => [$this, 'auth'],
                'args' => [
                    'taxonomy' => [
                        'required' => true
                    ]
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            'admin/terms/selected',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'selected'],
                'permission_callback' => [$this, 'auth'],
                'args' => [
                    'taxonomy' => [
                        'required' => true
                    ]
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            'admin/terms/select-all',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'all'],
                'permission_callback' => [$this, 'auth'],
                'args' => [
                    'taxonomy' => [
                        'required' => true
                    ]
                ]
            ]
        );
    }

    /**
     * Perform authentication
     *
     * @return bool
     */
    public function auth()
    {
        return current_user_can(Constants::CONTENT);
    }

    /**
     * Get a list of terms
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function response(WP_REST_Request $request)
    {
        $taxonomy = get_taxonomy($request->get_param('taxonomy'));

        $page = (int) $request->get_param('page');

        $offset = max($page - 1, 0) * $this->perPage;

        global $sitepress;
        remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );

        $terms = TermHelper::getPaginatedTerms($taxonomy, $this->perPage, $offset, $request->get_param('search'));
        $total = (int) $terms['count'];

        $response = [];

        foreach ($terms['terms'] ?? [] as $term) {
            foreach ($taxonomy->object_type as $type) {
                $response[] = [
                    'id' => $term->term_id . '.' . $type,
                    'key' => $term->term_id . '_' . $type,
                    'name' => $term->name . ' (' . $type . ')',
                    'lang' => $this->getLanguage($term, $request->get_param('taxonomy'))
                ];
            }
        }

        return new WP_REST_Response([
            'page' => $page ?: 1,
            'objects' => count($taxonomy->object_type),
            'pages' => ceil($total / $this->perPage),
            'total' => $terms['count'],
            'data' => $response,
        ]);
    }

    /**
     * Get a list of selected terms
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function selected(WP_REST_Request $request)
    {
        global $sitepress;
        remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );

        $ids = get_terms([
            'taxonomy' => $request->get_param('taxonomy'),
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        $option = get_option(ContentController::OPTION, []);
        $intersect = array_intersect($ids ?: [], array_keys(array_filter($option['terms'] ?? [])));

        $response = [];
        foreach ($intersect ?? [] as $id) {
            if (is_array($option['terms'][$id]) || is_object($option['terms'][$id])) {
                foreach ($option['terms'][$id] ?? [] as $postType => $value) {
                    $response[$id . '.' . $postType] = $value;
                }
            }
        }

        return new WP_REST_Response($response);
    }

    /**
     * Select all terms
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function all(WP_REST_Request $request)
    {
        global $sitepress;
        remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );

        $taxonomy = get_taxonomy($request->get_param('taxonomy'));

        $args = [
            'taxonomy' => $request->get_param('taxonomy'),
            'hide_empty' => false,
            'fields' => 'ids',
        ];

        if ($request->get_param('search')) {
            $args['search'] = $request->get_param('search');
        }

        $ids = get_terms($args);

        $response = [];

        foreach ($ids as $id) {
            foreach ($taxonomy->object_type ?? [] as $postType) {
                $response[$id . '.' . $postType] = 1;
            }
        }

        return new WP_REST_Response($response);
    }

    /**
     * Attempt to determine the term language
     *
     * @param WP_Term $term
     * @param string $type
     * @return mixed
     */
    private function getLanguage($term, $type = null)
    {
        if (function_exists('pll_get_term_language')) {
            return pll_get_term_language($term->term_id);
        }


        return apply_filters('wpml_element_language_code', null, [
            'element_id' => $term->term_id,
            'element_type' => $type,
        ]);

    }
}
