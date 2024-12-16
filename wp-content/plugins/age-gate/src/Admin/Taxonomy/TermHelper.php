<?php

namespace AgeGate\Admin\Taxonomy;

class TermHelper
{
    public static function getPaginatedTerms($taxonomy, $limit = 20, $offset = 0, $search = null, $args = [])
    {
        $args = array_merge($args, [
            'taxonomy' => $taxonomy->name,
            'number' => $limit,
            'offset' => $offset,
            'hide_empty' => false,
            'suppress_filter' => true,
        ]);

        $countArgs = $args;

        if ($search) {
            $countArgs['search'] = $search;
            $args['search'] = $search;
        }

        unset(
            $countArgs['number'],
            $countArgs['offset'],
        );

        $countArgs['fields'] = 'count';

        return [
            'terms' => get_terms($args),
            'count' => (int) get_terms($countArgs),
        ];
    }
}
