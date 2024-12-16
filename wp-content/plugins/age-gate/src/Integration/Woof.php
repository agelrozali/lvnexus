<?php

namespace AgeGate\Integration;

use AgeGate\Common\Integration;

class Woof extends Integration
{
    public function exists()
    {
        return class_exists('WOOF');
    }

    public function init()
    {
        if ($this->exists()) {

            add_filter('age_gate/init/content', function($content) {
                $woof = new \WOOF;

                if (method_exists($woof, 'get_really_current_term') && $term = $woof->get_really_current_term()) {
                    // dd($term);
                    $content = new \AgeGate\Common\Content($term, 'term');
                } elseif ($woof->is_isset_in_request_data($woof->get_swoof_search_slug())) {
                    global $wp_query;

                    if (($wp_query->query_vars['taxonomy'] ?? false) && ($wp_query->query_vars['term'] ?? false)) {

                        if ($term = get_term_by('slug', $wp_query->query_vars['term'], $wp_query->query_vars['taxonomy'])) {
                            $content = new \AgeGate\Common\Content($term, 'term');
                        }
                    }
                }

                return $content;
            });
        }
    }
}
