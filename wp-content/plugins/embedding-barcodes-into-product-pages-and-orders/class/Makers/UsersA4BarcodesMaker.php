<?php

namespace UkrSolution\BarcodesDigital\Makers;

use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Helpers\UserFieldsMatching;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use WP_User;

class UsersA4BarcodesMaker extends GeneralPostsA4BarcodesMaker
{
    protected $fieldNames = array(
        "standart" => array(
            "ID" => "Coupon Id",
        ),
        'user-id' => 'User id',
        'user-name' => 'User name',
        'user-email' => 'User email',
        'user-username' => 'User username',
        'user-register-date' => 'User register date',
        'user-role' => 'User role',
        'autologin-links' => 'User login url',
    );
    public $shortcodesToUserMetaMap = array(
        'user-shipping-first-name' => 'shipping_first_name',
        'user-shipping-last-name' => 'shipping_last_name',
        'user-shipping-city' => 'shipping_city',
        'user-shipping-address-1' => 'shipping_address_1',
        'user-shipping-address-2' => 'shipping_address_2',
        'user-shipping-postcode' => 'shipping_postcode',
        'user-shipping-phone' => 'shipping_phone',
        'user-shipping-country' => 'shipping_country',
        'user-shipping-country-full-name' => 'shipping_country_full_name',
        'user-shipping-state' => 'shipping_state',
        'user-shipping-state-full-name' => 'shipping_state_full_name',
        'user-shipping-company' => 'shipping_company',

        'user-billing-first-name' => 'billing_first_name',
        'user-billing-last-name' => 'billing_last_name',
        'user-billing-city' => 'billing_city',
        'user-billing-address-1' => 'billing_address_1',
        'user-billing-address-2' => 'billing_address_2',
        'user-billing-postcode' => 'billing_postcode',
        'user-billing-phone' => 'billing_phone',
        'user-billing-email' => 'billing_email',
        'user-billing-country' => 'billing_country',
        'user-billing-country-full-name' => 'billing_country_full_name',
        'user-billing-state' => 'billing_state',
        'user-billing-state-full-name' => 'billing_state_full_name',
        'user-billing-company' => 'billing_company',
    );

    protected function getItems()
    {
        $usersIds = isset($this->data['usersIds']) ? $this->data['usersIds'] : null;

        $args = array(
            'include' => $usersIds
        );
        $query = new \WP_User_Query($args);

        $this->items = $query->get_results();

    }

    protected function getFileOptions($post, $algorithm)
    {
        return parent::getFileOptions($post, $algorithm);
    }

    public function getField($user, &$field, $lineNumber = "")
    {
        $value = parent::getField($user, $field, $lineNumber);

        if (!empty($value)) {
            return $value;
        }

        $fieldName = isset($this->fieldNames[$field['type']]) ? $this->fieldNames[$field['type']] : '';
        $isAddFieldName = UserSettings::getoption('fieldNameL' . $lineNumber, false);

        if (isset($this->shortcodesToUserMetaMap[$field['type']])) {
            $field['value'] = $this->shortcodesToUserMetaMap[$field['type']];
            $field['type'] = 'user-meta';
        }

        switch ($field['type']) {
            case 'user-id':
                $value = $user->ID;
                break;
            case 'user-name':
                $value = get_user_meta($user->ID, 'first_name', true) . ' ' . get_user_meta($user->ID, 'last_name', true);
                break;
            case 'user-display-name':
                $value = $user->get('display_name');
                break;
            case 'user-email':
                $value = $user->get('user_email');
                break;
            case 'user-username':
                $value = $user->get('user_login');
                break;
            case 'user-register-date':
                $format = $field['args']['format'] ?? get_option('date_format');
                $value = wp_date($format, strtotime($user->get('user_registered')));
                break;
            case 'user-role':
                $value = $this->getUserRoles($user, $field);
                break;
            case 'autologin-links':
                $value = esc_url($this->getLoginAsUrl($user, $field));
                break;
            case 'user-meta':
                $value = $this->getUserMetaValue($user, $field);
                break;
            case 'user-shipping-full-name':
                $value = sprintf(
                    '%1$s %2$s',
                    $this->getUserMetaValue($user, array_merge($field, array('value' => 'shipping_first_name'))),
                    $this->getUserMetaValue($user, array_merge($field, array('value' => 'shipping_last_name')))
                );
                break;
            case 'user-billing-full-name':
                $value = sprintf(
                    '%1$s %2$s',
                    $this->getUserMetaValue($user, array_merge($field, array('value' => 'billing_first_name'))),
                    $this->getUserMetaValue($user, array_merge($field, array('value' => 'billing_last_name')))
                );
                break;
            default:
                $value = '';
        }

        $value = UserFieldsMatching::prepareFieldValue($isAddFieldName, $fieldName, $value, $lineNumber);

        return (string) $value;
    }

    protected function getUserMetaValue($user, $field)
    {
        if (in_array($field['value'], array('billing_country_full_name', 'shipping_country_full_name'))) {
            $countryCode = get_user_meta($user->ID, str_replace('_full_name', '', $field['value']), true);
            $countries = is_plugin_active('woocommerce/woocommerce.php') ? WC()->countries->get_countries() : array();
            $value = (is_array($countries) && isset($countries[$countryCode])) ? preg_replace("/\s\(.*\)/", '', $countries[$countryCode]) : $countryCode;
        } elseif (in_array($field['value'], array('billing_state_full_name', 'shipping_state_full_name'))) {
            $countryCode = get_user_meta($user->ID, str_replace('state_full_name', 'country', $field['value']), true);
            $states = is_plugin_active('woocommerce/woocommerce.php') ? WC()->countries->get_states($countryCode) : array();
            $stateCode = get_user_meta($user->ID, str_replace('_full_name', '', $field['value']), true);
            $value = (is_array($states) && isset($states[$stateCode])) ? $states[$stateCode] : $stateCode;
        } elseif (metadata_exists('user', $user->ID, $field['value'])) {
            $value = get_user_meta($user->ID, $field['value'], true);
        } else {
            $value = '';
        }

        return $value;
    }

    protected function getUserRoles($user, $field)
    {
        global $wp_roles;
        $userRolesNiceNames = array();

        foreach($user->roles as $userRoleSlug){
            $userRolesNiceNames[] = translate_user_role($wp_roles->roles[$userRoleSlug]['name']);
        }

        return implode(', ', $userRolesNiceNames);
    }

    protected function getLoginAsUrl($user, $field)
    {


        $code = \get_user_meta($user->ID, 'pkg_autologin_code', true);

        return \site_url("?autologin_code={$code}");
    }
}
