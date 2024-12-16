<?php




use UkrSolution\BarcodesDigital\Helpers\Variables;

if (!function_exists('uswbg_a4bJsonResponse')) {
    function uswbg_a4bJsonResponse($data)
    {
        @header('Content-type: application/json; charset=utf-8');
        echo json_encode($data);
        wp_die();
    }
}

if (!function_exists('uswbg_a4bGetPostsByCategories')) {
    function uswbg_a4bGetPostsByCategories($categoriesIds = array(), $args = array())
    {
        $defaultArgs = array(
            'post_type'      => 'any',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'terms'    => $categoriesIds,
                    'field'    => 'term_id',
                    'operator' => 'IN',
                ),
            ),
        );

        $args = array_merge($defaultArgs, $args);
        $query = new \WP_Query($args);

        return $query->posts;
    }
}

if (!function_exists('uswbg_a4bGetPosts')) {
    function uswbg_a4bGetPosts($args)
    {
        $defaultArgs = array(
            'post_type'      => 'any',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'suppress_filters' => true,
            'lang' => 'all',
        );

        $args = array_merge($defaultArgs, $args);
        $query = new \WP_Query($args);

        return $query->posts;
    }
}

if (!function_exists('uswbg_a4bExcludePostsByIds')) {
    function uswbg_a4bExcludePostsByIds($posts, $excludeIds)
    {
        return array_filter(
            $posts,
            function ($post) use ($excludeIds) {
                return !in_array($post->ID, $excludeIds);
            }
        );
    }
}

if (!function_exists('uswbg_a4bObjectsFieldToArray')) {
    function uswbg_a4bObjectsFieldToArray($objects, $field)
    {
        $fieldValues = [];
        foreach ($objects as $object) {
            $fieldValues[] = $object->$field;
        }

        return $fieldValues;
    }
}

if (!function_exists('uswbg_a4bFlashMessage')) {
    function uswbg_a4bFlashMessage($message, $type)
    {
        $flashMessages = get_transient('wpbcu_validation_errors') ?: array();
        $flashMessages[] = compact('message', 'type');
        set_transient('wpbcu_validation_errors', $flashMessages, 10);
    }
}

if (!function_exists('uswbg_a4bShowNotices')) {
    function uswbg_a4bShowNotices()
    {
        $flashMessages = get_transient('wpbcu_validation_errors') ?: array();
        delete_transient('wpbcu_validation_errors');

        foreach ($flashMessages as $flash) {
            printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr($flash['type']), esc_html($flash['message']));
        }
    }
}

if (!function_exists('uswbg_a4bRedirectBackWithErrorNotices')) {
    function uswbg_a4bRedirectBackWithErrorNotices($errors)
    {
        foreach ($errors as $error) {
            uswbg_a4bFlashMessage($error, 'error');
        }

        wp_redirect(wp_get_referer());
        exit();
    }
}

if (!function_exists('uswbg_a4bOldPostInitialization')) {
    function uswbg_a4bOldPostInitialization()
    {
        global $wpbcu_old_post;
        $wpbcu_old_post = get_transient('wpbcu_old_post') ?: array();
        if (!empty($wpbcu_old_post)) {
            delete_transient('wpbcu_old_post');
        }
    }
}

if (!function_exists('uswbg_a4bOld')) {
    function uswbg_a4bOld($field)
    {
        global $wpbcu_old_post;

        return isset($wpbcu_old_post[$field]) ? $wpbcu_old_post[$field] : null;
    }
}

if (!function_exists('USWBG_a4bRecursiveSanitizeTextField')) {
    function USWBG_a4bRecursiveSanitizeTextField($array, $isStripslashes = false)
    {
        foreach ($array as $key => &$value) {
            if($isStripslashes === true) {
                $value = is_array($value) ? USWBG_a4bRecursiveSanitizeTextField($value, $isStripslashes) : sanitize_text_field(stripslashes($value));
            } else {
                $value = is_array($value) ? USWBG_a4bRecursiveSanitizeTextField($value) : sanitize_text_field($value);
            }
        }

        return $array;
    }
}

if (!function_exists('USWBG_a4bRecursiveSanitizeTextareaField')) {
    function USWBG_a4bRecursiveSanitizeTextareaField($array, $isStripslashes = false)
    {
        foreach ($array as $key => &$value) {
            if ($isStripslashes === true) {
                $value = is_array($value) ? USWBG_a4bRecursiveSanitizeTextField($value, $isStripslashes) : sanitize_textarea_field(stripslashes($value));
            } else {
                $value = is_array($value) ? USWBG_a4bRecursiveSanitizeTextField($value) : sanitize_textarea_field($value);
            }
        }

        return $array;
    }
}

if (!function_exists('uswbg_add_custom_shortcode')) {
    function uswbg_add_custom_shortcode($shortcodeTag, $shortcodeHandler)
    {
        add_filter('barcode_generator_register_shortcodes_hook', function ($shortcodes) use ($shortcodeTag) {
            $shortcodes[] = $shortcodeTag;

            return $shortcodes;
        }, 10, 1);
        add_filter('barcode_generator_get_shortcode_value_hook', function ($value, $shortcode, $item, $field) use ($shortcodeTag, $shortcodeHandler) {
            try {
                switch ($shortcode) {
                    case $shortcodeTag:
                        $value = $shortcodeHandler($value, $shortcode, $item, $field);
                        break;
                }
            } catch (\Exception $e) {
            }

            return $value;
        }, 10, 4);
    }
}

if (!function_exists('USWBG_digital_lStylePath')) {
    function USWBG_digital_lStylePath($path)
    {
        $link = "link";

        $data = array("<" . $link);

        $data[] = ' ';
        $data[] = 'rel="stylesheet"';
        $data[] = ' ';
        $data[] = 'type="text/css"';
        $data[] = ' ';



        $data[] = '>';

        return $data;
    }
}

if (!function_exists('USWBG_digital_lScriptPath')) {
    function USWBG_digital_lScriptPath($path)
    {
        $script = "script";

        $data = array("<" . $script);

        $data[] = ' ';

        $data[] = '><';
        $data[] = '/';
        $data[] = $script;
        $data[] = '>';

        return $data;
    }
}

