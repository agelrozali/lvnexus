<?php

namespace AgeGate\Admin\Settings;

trait Tools
{
    protected function getToolsFields()
    {
        return [
            [
                'title' => 'Disable Age Gate',
                'subtitle' => '',
                'fields' => [
                    'disable_age_gate' => [
                        'type' => 'checkbox',
                        'label' => __('Disable Age Gate', 'age-gate'),
                        'default' => false,
                        'subtext' => __('Disable all Age Gates to users', 'age-gate'),
                    ],
                ]
            ],
            [
                'title' => 'Developer options',
                'subtitle' => '',
                'fields' => [
                    'dev_warning' => [
                        'type' => 'checkbox',
                        'label' => __('Development warning', 'age-gate'),
                        'default' => true,
                        'subtext' => __('Show warnings if using development version', 'age-gate'),
                    ],
                    'dev_endpoint' => [
                        'type' => 'checkbox',
                        'label' => __('Developer endpoint', 'age-gate'),
                        'default' => false,
                        'subtext' => __('Enable a developer API endpoint containing Age Gate information', 'age-gate'),
                    ],
                    'feedback' => [
                        'type' => 'checkbox',
                        'label' => __('Send feedback', 'age-gate'),
                        'default' => false,
                        'subtext' => __('Occasionally send settings information to the developers', 'age-gate'),
                    ],
                ]
            ],
        ];
    }
}
