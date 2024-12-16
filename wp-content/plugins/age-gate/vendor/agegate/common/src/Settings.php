<?php

namespace AgeGate\Common;

use Parsedown;
use Asylum\Utility\Arr;
use Asylum\Utility\Language;
use AgeGate\Common\Immutable\Constants;
use Jawira\CaseConverter\Convert;

class Settings
{
    private $data = [];
    private $parsedown;
    public $currentLanguage;

    private static $instance = null;

    private $parseFields = [
        'content',
        'errorInvalid'
    ];

    private function __construct()
    {
        $this->parsedown = new Parsedown();
        $this->currentLanguage = $_REQUEST['age_gate']['lang'] ?? Language::getInstance()->getLanguage();
        // $this->data['language'] = $this->currentLanguage;

        // dump(Constants::AGE_GATE_OPTIONS);

        $options = [];
        $data = [
            'language' => $this->currentLanguage,
        ];

        foreach (Constants::AGE_GATE_OPTIONS as $slug => $option) {
            if ($slug === 'access') {
                continue;
            }

            $group = get_option($option, []);
            $options = array_merge($options, Arr::dot($group));
        }

        foreach (Arr::undot(array_filter($options, function ($value) {
            return ($value || is_numeric($value));
        })) as $key => $value) {

            $data[(new Convert($key))->toCamel()] = stripslashes_deep($value);
        }

        // languages
        foreach (Language::getInstance()->getLanguages() as $code => $language) {
            $data[(new Convert($code))->toCamel()] = collect($data[(new Convert($code))->toCamel()] ?? [])
                ->mapWithKeys(fn ($value, $key) => [
                    (new Convert($key))->toCamel() => stripslashes_deep($value)
                ])
                ->toArray();
        }

        // flip terms
        $data['terms'] = collect($data['terms'] ?? [])->map(function ($item, $k) {
            return is_array($item) ? array_keys($item) : [];
        })->toArray();

        $data['rememberTime'] = $data['rememberLength']['remember_time'] ?? 365;
        $data['rememberLength'] = $data['rememberLength']['remember_length'] ?? 'days';

        $data['logoId'] = apply_filters('age_gate/logo/id', $data['logo'] ?? null);

        $data['logo'] = apply_filters(
            'age_gate/logo/src',
            wp_get_attachment_url($data['logoId'])
        );

        $data['backgroundImageId'] = $data['backgroundImage'] ?? false;
        $data['backgroundImage'] = $data['backgroundImageId'] ? wp_get_attachment_url($data['backgroundImage'] ?? null) : false;


        if ($data['stepped'] ?? false) {
            $data['dateFormat'] = 'YYYY MM DD';
        }


        // if (array_key_exists('debug', $_GET)) {
        //     dump($this->data);
        //     dump(array_diff(array_keys($data), array_keys($this->data)));
        //     dd($data);
        // }



        $this->data = $data;
    }

    public function __get($prop)
    {
        // _doing_it_wrong( $prop, 'Do not call properties directly', '3.0.0' );
        $c = $this->data[(new Convert($this->currentLanguage))->toCamel()][$prop] ?? $this->data[$this->currentLanguage][$prop] ?? $this->data[$prop] ?? false;

        // if ($c && in_array($prop, $this->parseFields)) {
        //     $method = (preg_match('/\R/', $c)) ? 'text' : 'line';
        //     $c = $this->parsedown->$method($c);
        // }

        return $c;
    }

    public function set($prop, $value)
    {
        $this->data[$prop] = $value;
    }

    private function parse($content)
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCookieName()
    {
        return apply_filters('age_gate/cookie/name', $this->cookieName ?: 'age_gate');
    }
}
