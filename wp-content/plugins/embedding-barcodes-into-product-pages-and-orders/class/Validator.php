<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Helpers\SupportedPostTypes;

class Validator
{
    private static $instance;

    private $data = array();
    private $validated = array();
    private $rules = array();
    private $errors = array();
    private $errorMessages = array();
    private $sendErrorResponse = false;
    private $shouldBail = false;

    public static function create($data, $validationRules, $sendErrorResponse = false)
    {
        $config = require __DIR__ . '/../config/config.php';
        self::$instance = new self($data, $validationRules, $config['listMessagesValidationRules'], $sendErrorResponse);

        return self::$instance;
    }

    public function __construct($data, $validationRules, $errorMessages, $sendErrorResponse = false)
    {
        $this->data = $data;
        $this->rules = $validationRules;
        $this->sendErrorResponse = $sendErrorResponse;
        $this->errorMessages = $errorMessages;
    }

    public function validate()
    {
        foreach ($this->rules as $fieldName => $rules) {
            $rulesArray = ('' !== $rules) ? explode('|', $rules) : array();
            $rulesArrayFiltered = array();
            foreach ($rulesArray as $key => $itemRule) {
                $rule = $this->getRule($itemRule);
                $attributes = $this->getAttributes($itemRule);
                $rulesArrayFiltered[$key] = array(
                    'rule' => $rule,
                );
                $rulesArrayFiltered[$key]['attributes'] = !empty($attributes) ? $attributes : array();
            }

            $this->validateField($fieldName, $rulesArrayFiltered);

            if ($this->shouldBail && !empty($this->errors)) {
                break;
            }
        }

        if ($this->isValid()) {
            return $this->validated;
        } else {
            if ($this->sendErrorResponse) {
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    uswbg_a4bJsonResponse(array('error' => Validator::getErrors()));
                } else {
                    set_transient('wpbcu_old_post', $this->data, 10);
                    uswbg_a4bRedirectBackWithErrorNotices(Validator::getErrors());
                }

                return false;
            } else {
                return false;
            }
        }
    }

    public function getRule($rule)
    {
        $tpmArr = explode(':', $rule);

        return $tpmArr[0];
    }

    public function getAttributes($rule)
    {
        $tpmArr = explode(':', $rule);
        $attributes = array();
        if (isset($tpmArr[1])) {
            $attributes = explode(',', $tpmArr[1]);
        }

        return $attributes;
    }

    public function isValid()
    {
        return (count($this->errors) > 0) ? false : true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function validateField($fieldName, $rulesArray)
    {
        if (empty($rulesArray) && isset($this->data[$fieldName])) {
            $this->validated[$fieldName] = $this->data[$fieldName];
            return;
        }

        foreach ($rulesArray as $itemRuleData) {
            $methodName = "rule{$itemRuleData['rule']}";
            if (method_exists($this, $methodName)) {
                $fieldValid = call_user_func_array(array($this, $methodName), array($fieldName, $itemRuleData));
            } else {
                echo '<pre>';
                print_r($methodName);
                die();
                throw new \Exception("wp_barcodes: Validation rule doesn't exists.");
            }

            if ($fieldValid) {
                if (isset($this->data[$fieldName])) {
                    $this->validated[$fieldName] = $this->data[$fieldName];
                }
            }
        }
    }

    private function ruleRequired($fieldName, $ruleData)
    {
        $attributes = $ruleData['attributes'];

        if (isset($attributes[0]) && 'if_empty' === $attributes[0] && isset($attributes[1])) {
            if (
                empty($this->data[$attributes[1]]) 
                && (!isset($this->data[$fieldName]) 
                    || '' === $this->data[$fieldName])
            ) {
                $this->_error($this->errorMessages['required'], $fieldName);

                return false;
            }
        } elseif (!isset($this->data[$fieldName]) || ('' === $this->data[$fieldName] && $fieldName !== 'base_padding_uol')) {
            $this->_error($this->errorMessages['required'], $fieldName);

            return false;
        }

        return true;
    }

    private function ruleRequiredItem($fieldName, $ruleData)
    {
        $attributes = $ruleData['attributes'];

        if (isset($attributes[0]) && 'if_empty' === $attributes[0] && isset($attributes[1])) {
            if (
                empty($this->data[$attributes[1]]) 
                && (!isset($this->data[$fieldName]) 
                    || '' === $this->data[$fieldName])
            ) {
                $this->_error($this->errorMessages['required_item'], $fieldName);

                return false;
            }
        } elseif (
            !isset($this->data[$fieldName])
            || '' === $this->data[$fieldName]
        ) {
            $this->_error($this->errorMessages['required_item'], $fieldName);

            return false;
        }

        return true;
    }

    private function ruleBail($fieldName, $ruleData)
    {
        $this->shouldBail = true;

        return false;
    }

    private function ruleNumeric($fieldName, $ruleData)
    {
        if (isset($this->data[$fieldName])) {
            if (false == is_numeric($this->data[$fieldName])) {
                $this->_error($this->errorMessages['numeric'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function ruleStrtobool($fieldName, $ruleData)
    {
        if (isset($this->data[$fieldName])) {
            if ('true' === $this->data[$fieldName]) {
                $this->data[$fieldName] = true;
            }
            if ('false' === $this->data[$fieldName]) {
                $this->data[$fieldName] = false;
            }
        }

        return true;
    }

    private function ruleBoolean($fieldName, $ruleData)
    {
        if (true == isset($this->data[$fieldName])) {
            if (false == is_bool($this->data[$fieldName])) {
                $this->_error($this->errorMessages['boolean'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function ruleString($fieldName, $ruleData)
    {
        if (true == isset($this->data[$fieldName])) {
            $this->data[$fieldName] = sanitize_text_field($this->data[$fieldName]);

            if (false == is_string($this->data[$fieldName])) {
                $this->_error($this->errorMessages['string'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function rulehtml($fieldName, $ruleData)
    {
        if (true == isset($this->data[$fieldName])) {
            $this->data[$fieldName] = $this->data[$fieldName];

            if (false == is_string($this->data[$fieldName])) {
                $this->_error($this->errorMessages['string'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function rulejs($fieldName, $ruleData)
    {
        if (true == isset($this->data[$fieldName])) {
            $this->data[$fieldName] = $this->data[$fieldName];

            if (false == is_string($this->data[$fieldName])) {
                $this->_error($this->errorMessages['string'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function ruleComplexCodeValue($fieldName, $ruleData)
    {
        if (true == isset($this->data[$fieldName])) {
            $this->data[$fieldName] = stripslashes($this->data[$fieldName]);
            $this->data[$fieldName] = sanitize_textarea_field($this->data[$fieldName]);

            $this->data[$fieldName] = preg_replace('/^(?:[\t ]*(?:\r?\n|\r))+/m', '', $this->data[$fieldName]);
            $this->data[$fieldName] = rtrim($this->data[$fieldName]);



        }

        return true;
    }

    private function ruleArray($fieldName, $ruleData)
    {
        if (true == isset($this->data[$fieldName])) {
            if (false == is_array($this->data[$fieldName])) {
                $this->_error($this->errorMessages['array'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function ruleIn($fieldName, $ruleData)
    {
        if (isset($this->data[$fieldName]) && isset($ruleData['attributes'])) {
            if (false == in_array($this->data[$fieldName], $ruleData['attributes'])) {
                $this->_error($this->errorMessages['in'], $fieldName);

                return false;
            }
        }

        return true;
    }

    private function ruleXml($fieldName, $ruleData)
    {
        if (isset($this->data[$fieldName])) {
            $this->data[$fieldName] = stripslashes($this->data[$fieldName]);

            $useErrors = libxml_use_internal_errors(true);

            $doc = new \DOMDocument();
            $success = $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?><root>' . $this->data[$fieldName] . '</root>');
            $xmlErrors = libxml_get_errors();

            if (!$success && !empty($xmlErrors)) {
                $this->_error($this->errorMessages['xml'] . ' ' . $xmlErrors[0]->message, $fieldName);
            } elseif (!$success) {
                $this->_error($this->errorMessages['xml'], $fieldName);
            }

            libxml_use_internal_errors($useErrors);

            if (!empty($this->errors)) {
                return false;
            }
        }

        return true;
    }

    private function _error($errorMsg, $fieldName)
    {
        $this->errors[] = sprintf($errorMsg, $fieldName);
    }
}
