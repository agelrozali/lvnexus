<?php

namespace UkrSolution\BarcodesDigital\Makers;

use UkrSolution\BarcodesDigital\Database;
use UkrSolution\BarcodesDigital\Helpers\UserFieldsMatching;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;

abstract class GeneralPostsA4BarcodesMaker extends A4BarcodesMaker
{
    protected $currency = '';
    protected $currencyPosition = 'left';
    protected $fieldNames = array(
        "standart" => array(
            "ID" => "Id",
            "post_title" => "Title",
            "post_content" => "Description",
            "post_excerpt" => "Short Description",
        ),
        "custom" => array(
        ),
        "permalink" => "Product Link",
        "permalink_admin" => "Product Link (Admin)",
        "creation_date" => "Creation date",
        "creation_time" => "Creation time",
        "creation_datetime" => "Creation date and time",
        "random-digits" => "Random digits",
        "current-date-time" => "Current date time",
    );

    public function __construct($data, string $type = '')
    {
        parent::__construct($data, $type);
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $this->currency = strip_tags(html_entity_decode(get_woocommerce_currency_symbol()));
            $this->currencyPosition = get_option('woocommerce_currency_pos');
        }
    }

    protected function getFileOptions($post, $algorithm)
    {
        $quantity = 1;

        $thumbnailUrl = get_the_post_thumbnail_url($post, get_option(Database::$optionPostImageSize, 'medium'));

        $fieldLine1 = !empty($this->data['fieldLine1']) ? $this->getField($post, $this->data['fieldLine1'], 1) : '';
        $fieldLine2 = !empty($this->data['fieldLine2']) ? $this->getField($post, $this->data['fieldLine2'], 2) : '';
        $fieldLine3 = !empty($this->data['fieldLine3']) ? $this->getField($post, $this->data['fieldLine3'], 3) : '';
        $fieldLine4 = !empty($this->data['fieldLine4']) ? $this->getField($post, $this->data['fieldLine4'], 4) : '';

        $fieldSepLine1 = $fieldSepLine2 = $fieldSepLine3 = $fieldSepLine4 = '';

        return array(
            'quantity' => $quantity,
            'post_image' => $thumbnailUrl,
            'lineBarcode' => $this->getCodeField($post), 
            'fieldLine1' => $this->twoColumnLineFormat($fieldLine1, $fieldSepLine1), 
            'fieldLine2' => $this->twoColumnLineFormat($fieldLine2, $fieldSepLine2), 
            'fieldLine3' => $this->twoColumnLineFormat($fieldLine3, $fieldSepLine3), 
            'fieldLine4' => $this->twoColumnLineFormat($fieldLine4, $fieldSepLine4), 
            'algorithm' => $algorithm, 
            'showName' => $this->showName, 
            'showLine3' => $this->showLine3, 
            'showLine4' => $this->showLine4,
            'replacements' => $this->getTemplateReplacements($post, $this->templateShortcodesArgs),
        );
    }

    protected function getCodeField($post)
    {
        if ($this->activeTemplate->code_match) {
            return $this->getCodeValue($post, $this->activeTemplate->single_product_code);
        } else {
            return isset($this->data['lineBarcode']) ? $this->getField($post, $this->data['lineBarcode']) : '';
        }
    }

    protected function getCodeValue($post, $template)
    {
        $this->extractTemplateShortcodes($template, $this->barcodeTemplateShortcodesArgs);
        $shortCodesReplacements = $this->getTemplateReplacements($post, $this->barcodeTemplateShortcodesArgs);

        $html = $template;
        foreach ($shortCodesReplacements as $shortCode => $replacement) {
            $html = str_replace($shortCode, $replacement, $html);
        }

        return $html;
    }

    protected function getField($post, &$field, $lineNumber = "")
    {
        $fieldName = isset($this->fieldNames[$field['type']]) ? $this->fieldNames[$field['type']] : '';
        $isAddFieldName = UserSettings::getoption('fieldNameL' . $lineNumber, false);

        switch ($field['type']) {
            case 'standart':
                $value = $this->getStandardPostField($post, $field['value']);
                $fieldName = isset($this->fieldNames['standart'][$field['value']]) ? $this->fieldNames['standart'][$field['value']] : '';
                break;
            case 'custom':
                if (0 === strpos($field['value'], 'parent.')) {
                    $filedBeforeReplacedValue = $field;
                    $post = !empty($post->post_parent) ? get_post($post->post_parent) : $post;
                    $field['value'] = str_replace('parent.', '', $field['value']);

                    $value = $this->getCustomFieldsValues($post, $field);
                    $fieldName = isset($this->fieldNames['custom'][$field['value']]) ? $this->fieldNames['custom'][$field['value']] : '';
                    $field = $filedBeforeReplacedValue;
                } else {
                    $value = $this->getCustomFieldsValues($post, $field);
                    $fieldName = isset($this->fieldNames['custom'][$field['value']]) ? $this->fieldNames['custom'][$field['value']] : '';
                }

                break;
            case 'multipleFields':
                $value = $this->getCodeValue($post, $field['value']);
                break;
            case 'shortcodeFields':
                try {
                    $value = \do_shortcode(str_replace("\\", "", $field['value']));
                } catch (\Throwable $th) {
                    $value = $field['value'];
                }
                break;
            case 'static':
                $value = $field['value'];
                break;
            case 'permalink':
                $value = get_post_permalink($post->ID);
                break;
            case 'permalink_admin':
                $value = 'product_variation' === $post->post_type && !empty($post->post_parent)
                    ? get_edit_post_link($post->post_parent, '')
                    : get_edit_post_link($post->ID, '');
                break;
            case 'taxonomy':
                $value = $this->getTaxonomy($post, $field);
                break;
            case 'date':
                $value = $this->getDate($field);
                break;
            case 'creation_date':
                $value = $this->getCreationDate($post);
                break;
            case 'creation_time':
                $value = $this->getCreationTime($post);
                break;
            case 'creation_datetime':
                $value = $this->getCreationDatetime($post);
                break;
            case 'random-digits':
                $value = $this->randomDigits($post, $field);
                break;
            case 'current-date-time':
                $value = $this->getCurrentDateTime($post, $field);
                break;
            default:
                $value = '';
                $value = apply_filters('barcode_generator_get_shortcode_value_hook', $value, $field['type'], $post, $field);
        }

        $value = UserFieldsMatching::prepareFieldValue($isAddFieldName, $fieldName, $value, $lineNumber);

        return (string) $value;
    }

    protected function getCurrentDateTime($post, $field)
    {
        $format = isset($field['args']['format']) ? $field['args']['format'] : get_option('date_format');
        $res = current_time($format);

        if (!empty($field['args']['plus'])) {
            $dateTime = date_create($res);
            $dateTime->modify($field['args']['plus']);
            $res = $dateTime->format($format);
        }

        return !empty($res) ? $res : '';
    }

    protected function randomDigits($post, $field)
    {
        $length = isset($field['args']['length']) ? $field['args']['length'] : 5;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }

    protected function getCustomFieldsValues($post, &$field)
    {
        $customFields = array_map('trim', explode(',', $field['value']));
        $values = array();
        foreach ($customFields as $customField) {
            if (empty($customField) && !$this->shouldAddCurrency(array_merge($field, array('value' => $customField)))) {
                continue;
            }

            $values[] = ($this->shouldAddCurrency(array_merge($field, array('value' => $customField))))
                ? $this->getValueWithCurrency($this->getProductMeta($post, $customField, true), $field)
                : $this->getProductMeta($post, $customField, true);

            $values = array_filter($values);
        }

        return implode(',', $values);
    }

    protected function shouldAddCurrency($field)
    {
        return (isset($field['args']['is_price']) && 'true' === $field['args']['is_price'])
            || in_array($field['value'], array(
            ));
    }

    protected function round($value, &$field)
    {
        if (
            is_plugin_active('woocommerce/woocommerce.php')
            && false !== strpos($value, wc_get_price_decimal_separator())
        ) {
            $tmpValue = str_replace(wc_get_price_decimal_separator(), '.', $value);
        } else {
            $tmpValue = $value;
        }

        if (isset($field['args']['round-number']) && 'true' === $field['args']['round-number'] && is_numeric($tmpValue)) {
            unset($field['args']['round-number']);
            $result = round((float)$tmpValue);
        } else {
            $result = $value;
        }

        return $result;
    }

    protected function getValueWithCurrency($value, &$field = array())
    {
        if (empty($value)) {
            $value = 0;
        }

        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            return $value;
        }

        $value = number_format($value, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator());

        if (isset($field['args']['show-only-decimal']) && 'true' === $field['args']['show-only-decimal']) {

            if (false !== strpos($value, wc_get_price_decimal_separator())) {
                $parts = explode(wc_get_price_decimal_separator(), $value);
                $result = $parts[1];
            } else {
                $result = str_repeat('0', wc_get_price_decimals());
            }
        } else {

            if (isset($field['args']['show-decimal']) && 'false' === $field['args']['show-decimal']) {
                if (false !== strpos($value, wc_get_price_decimal_separator())) {
                    $parts = explode(wc_get_price_decimal_separator(), $value);
                    $value = $parts[0];
                }
            } else {
                $value = $this->round($value, $field);
            }

            $currencySymbol = UserSettings::getOption('currencySymbol', true);

            if (
                (bool) $currencySymbol
                && (!isset($field['args']['disable-currency']) || 'true' !== $field['args']['disable-currency'])
            ) {
                switch ($this->currencyPosition) {
                    case 'left':
                        $result = $this->currency . $value;
                        break;
                    case 'left_space':
                        $result = $this->currency . ' ' . $value;
                        break;
                    case 'right':
                        $result = $value . $this->currency;
                        break;
                    case 'right_space':
                        $result = $value . ' ' . $this->currency;
                        break;
                    default:
                        $result = $this->currency . $value;
                }
            } else {
                $result = $value;
            }
        }

        return $result;
    }

    protected function getProductMeta($post, $param, $single = true)
    {
        $customKeys = get_post_custom_keys($post->ID);
        if ($customKeys && in_array($param, $customKeys)) {
            return get_post_meta($post->ID, $param, $single);
        } else {
            return '';
        }
    }

    protected function termsObjectsToString($terms, $termMeta = null)
    {
        if ($terms && !is_wp_error($terms)) {

            $terms = array_map(function ($term) use ($termMeta) {
                return empty($termMeta) ? $term->name : get_term_meta($term->term_id, $termMeta, true);
            }, $terms);

            return implode(', ', $terms);
        }

        return null;
    }

    protected function getStandardPostField($post, $field)
    {

        return isset($post->{$field}) ? $post->{$field} : '';
    }

    protected function getTemplateReplacements($post, $shortcodesArgs)
    {
        $replacements = new \ArrayObject();


        foreach ($shortcodesArgs as $shortCode => $args) {
            $replacements[$shortCode] = $this->getShortcodeFieldValue($post, $args);
        }

        return $replacements;
    }

    protected function getShortcodeFieldValue($post, $args)
    {
        $result = '';
        $args['value'] = isset($args['value']) ? $args['value'] : '';

        $argsValues = 'static' !== $args['type'] ? explode('|', $args['value']) : array($args['value']);

        foreach ($argsValues as $argsValue) {
            if (0 === strpos($argsValue, 'parent.')) {
                $post = !empty($post->post_parent) ? get_post($post->post_parent) : $post;
                $argsValue = str_replace('parent.', '', $argsValue);
            }

            $field = array('type' => $args['type'], 'value' => $argsValue, 'args' => $args);

            switch ($args['type']) {
                case 'attr':
                    $field['type'] = 'wc_taxonomy_name';
                    $field['term_meta'] = $args['term_meta'];
                    $text = $this->getField($post, $field);
                    break;
                case 'cf':
                    $field['type'] = 'custom';
                    $text = $this->getField($post, $field);
                    break;
                case 'field':
                    $field['type'] = 'standart';
                    $text = $this->getField($post, $field);
                    break;
                case 'category':
                    $field['type'] = 'wc_category';
                    $field['value'] = 'wc_category';
                    $text = $this->getField($post, $field);
                    break;
                case 'static':
                    $text = $argsValue;
                    break;
                case 'order-single-item-qty':
                    $text = $args['qty'];
                    break;
                case 'creation-date':
                    $field['type'] = 'creation_date';
                    $text = $this->getField($post, $field);
                    break;
                case 'creation-time':
                    $field['type'] = 'creation_time';
                    $text = $this->getField($post, $field);
                    break;
                case 'order_create_date':
                    $field['args']['format'] = $argsValue;
                    $text = $this->getField($post, $field);
                    break;
                default:
                    $text = $this->getField($post, $field);
                    break;
            }

            $text = $this->round($text, $field);

            if (!empty($text)) {
                $texts[] = $text;
                $result = implode(' ', $texts);

                if (!empty($args['before'])) {
                    $result = $args['before'] . $result;
                }

                if (!empty($args['after'])) {
                    $result = $result . $args['after'];
                }

                break;
            }
        }

        $result = $this->convertValueToBarcodeImageUrlIfNeed($field, $result);

        return $result;
    }

    protected function getTaxonomy($post, $field)
    {
        return $this->termsObjectsToString(get_the_terms($post, $field['value']));
    }

    protected function getDate($field)
    {
        $res = date($field['value']);

        return $res !== false ? $res : '';
    }

    protected function getCreationDate($post)
    {
        return get_the_date('', $post);
    }

    protected function getCreationTime($post)
    {
        return get_the_time('', $post);
    }

    protected function getCreationDatetime($post)
    {
        return get_the_date('', $post) . ' ' . get_the_time('', $post);
    }
}
