<?php

namespace AgeGate\Admin\Controller;

use Asylum\Utility\Arr;
use AgeGate\Common\Settings;
use AgeGate\Admin\Settings\Content;
use AgeGate\Common\Content as ContentData;
use AgeGate\Common\Admin\AbstractController;
use AgeGate\Common\Immutable\Constants as Immutable;

class ContentController extends AbstractController
{
    use Content;
    public const PERMISSION = Immutable::CONTENT;
    public const OPTION = Immutable::OPTION_CONTENT;

    // protected $template = 'content';

    public function register(): void
    {
        $this->view->addData([
            'noOptions' => __('No terms', 'age-gate')
        ]);
        $this->menu(__('Content', 'age-gate'), self::PERMISSION);
    }

    protected function required(): bool
    {
        return current_user_can(self::PERMISSION);
    }

    protected function data(): array
    {
        $data = get_option(self::OPTION, []) ?: [];

        unset($data['terms']);
        return $data;
    }

    protected function fields(): array
    {
        return $this->getContentFields();
    }

    /**
     * Special prep for data storage
     *
     * @return void
     */
    protected function store()
    {
        $option = get_option(self::OPTION, []);


        $_POST['ag_settings']['terms'] = collect($option['terms'] ?? [])
            ->map(fn($value) => $value ? 1 : false)
            ->undot()
            ->toArray();

        parent::store();
    }

    public function enqueue(): void
    {
        global $hook_suffix;

        if ('age-gate_page_age-gate-content' === $hook_suffix) {
            wp_enqueue_script('age-gate-admin-content', AGE_GATE_URL . 'dist/admin-content.js', ['age-gate-admin'], AGE_GATE_VERSION, true);

            wp_localize_script( 'age-gate-admin-content', 'ag_content_params', [
                'save_error' => esc_html__('Error: an error occured while saving. Please try again', 'age-gate'),
                'load_error' => esc_html__('Error: an error occured while loading. Refreshing the page recommended', 'age-gate'),
            ]);
        }
    }

    protected function optionStored($data): void
    {
        $data = Arr::undot($data);

        foreach ($data['user'] ?? [] as $id => $options) {
            $content = (new ContentData($id, 'user'));
            $settings = Settings::getInstance();
            // mutli ages?
            if ($settings->multiAge && current_user_can(Immutable::SET_CUSTOM_AGE)) {
                $default = $settings->{$content->getLanguage()}['defaultAge'] ?? $settings->defaultAge;

                if ($options['age'] ?? false) {
                    $age = (int) $options['age'];

                    if ($age === $default) {
                        // remove the meta as we don't need it
                        delete_user_meta($id, Immutable::META_AGE);
                    } else {
                        // add new meta key
                        update_user_meta($id, Immutable::META_AGE, $age);
                    }
                }
            }

            // bypass ?
            if ($settings->type === 'all' && current_user_can(Immutable::SET_CONTENT)) {
                if ($options['bypass'] ?? false) {
                    // add new meta key
                    update_user_meta($id, Immutable::META_BYPASS, 1);
                } else {
                    // remove the meta as we don't need it
                    delete_user_meta($id, Immutable::META_BYPASS);
                }
            }

            // restrict
            if ($settings->type === 'selected' && current_user_can(Immutable::SET_CONTENT)) {
                if ($options['restrict'] ?? false) {
                    // add new meta key
                    update_user_meta($id, Immutable::META_RESTRICT, 1);
                } else {
                    // remove the meta as we don't need it
                    delete_user_meta($id, Immutable::META_RESTRICT);
                }
            }
        }
    }

    protected function rules() : array
    {
        $rules = [];

        foreach ($this->getContentFields() as $set) {
            foreach ($set['fields'] as $name => $field) {
                if ($field['type'] === 'group') {
                    foreach ($field['fields'] as $n => $f) {

                        if ($f['type'] !== 'hidden') {
                            $rules[$name . '.' . $n] = $f['type'] === 'number' ? 'numeric' : 'boolean';
                        }
                    }
                } else {
                    if ($field['type'] !== 'hidden') {
                        $rules[$name] = $field['type'] === 'checkbox' ? 'boolean' : 'numeric';
                    }
                }
            }
        }

        return $rules;
    }
}
