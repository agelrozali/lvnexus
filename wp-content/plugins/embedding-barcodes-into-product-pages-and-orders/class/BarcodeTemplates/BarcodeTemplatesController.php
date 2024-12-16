<?php

namespace UkrSolution\BarcodesDigital\BarcodeTemplates;

use UkrSolution\BarcodesDigital\Database;
use UkrSolution\BarcodesDigital\Dimensions;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use UkrSolution\BarcodesDigital\Helpers\Variables;
use UkrSolution\BarcodesDigital\Request;
use UkrSolution\BarcodesDigital\Settings;
use UkrSolution\BarcodesDigital\Validator;

class BarcodeTemplatesController
{
    protected $wpdb;
    protected $tbl;
    protected $tblTemplateToUser;
    protected $digitalTemplateValidationRules = array(
        'id' => 'numeric',
        'height' => 'required|numeric',
        'width' => 'required|numeric',
        'base_padding_uol' => 'numeric',
        'barcode_type' => 'string',
    );
    protected $templateValidationRules = array(
        'id' => 'numeric',
        'name' => 'required',
        'type' => 'required|string',
        'logo' => 'string',
        'senderAddress' => 'html',
        'template' => 'xml',
        'height' => 'required|numeric',
        'width' => 'required|numeric',
        'label_margin_top' => 'required',
        'label_margin_right' => 'required',
        'label_margin_bottom' => 'required',
        'label_margin_left' => 'required',
        'barcode_type' => 'string',
        'code_match' => 'numeric',
        'fontStatus' => 'numeric',
        'fontTagLink' => 'html',
        'fontCssRules' => 'string',
        'customCss' => 'html',
        'jsAfterRender' => 'js',
    );
    protected $defaultTemplateValidationRules = array(
        'id' => 'numeric',
        'name' => 'string',
        'barcode_type' => 'string',
        'code_match' => 'numeric',
        'fontStatus' => 'numeric',
        'fontTagLink' => 'html',
        'fontCssRules' => 'string',
        'customCss' => 'html',
        'jsAfterRender' => 'js',
    );

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->tbl = $wpdb->prefix . Database::$tableTemplates;
        $this->tblTemplateToUser = $wpdb->prefix . Database::$tableTemplateToUser;
    }

    public function create()
    {
        $templates = $this->wpdb->get_results("SELECT * FROM `{$this->tbl}`");

        include_once Variables::$A4B_PLUGIN_BASE_PATH . 'templates/barcode-templates/create.php';
    }

    public function createNewTemplate()
    {
        Request::ajaxRequestAccess();

    }

    public function edit()
    {
        $pluginUrl = plugin_dir_url(dirname(dirname(__FILE__)));
        echo '<script>window.barcodePluginUrl = "' . $pluginUrl . '"</script>';

        $prefix = '';
        $prefix = '_d';

        echo '<div><a href="#" id="barcode' . $prefix . '-custom-templates"></a></div>';
    }

    public function update()
    {
        Request::ajaxRequestAccess();
    }

    public function updateDigital()
    {
        Request::ajaxRequestAccess();
        $id = isset($_POST['id']) ? intval(sanitize_key($_POST['id'])) : null;
        $chosenTemplateRow = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM `{$this->tbl}` WHERE `id` = %s", $id), ARRAY_A);

        $post = array();
        foreach (array('id', 'height', 'width', 'padding', 'barcode_type') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }

        if (isset($post['padding'])) {
            $post['base_padding_uol'] = $post['padding'];
            unset($post['padding']);
        }

        $data = Validator::create($post, $this->digitalTemplateValidationRules, true)->validate();
        $res = $this->wpdb->update($this->tbl, $data, array('id' => $data['id']));

        if ($res !== false) {
            uswbg_a4bJsonResponse(array(
                "message" => __('Template updated successfully!', 'wpbcu-barcode-generator'),
            ));
        } else {
            uswbg_a4bJsonResponse(array(
                "error" => $this->wpdb->last_error,
            ));
        }

        uswbg_a4bJsonResponse(array(
            "error" => __('Unknown error', 'wpbcu-barcode-generator'),
        ));
    }

    public function delete()
    {
        Request::ajaxRequestAccess();
    }

    public function copy()
    {
        Request::ajaxRequestAccess();
    }

    public function setactive()
    {
        Request::ajaxRequestAccess();
    }

    public function setActiveTemplate($id, $resetMatching = true, $isAjax = true)
    {
        $this->wpdb->delete(
            $this->tblTemplateToUser,
            array('userId' => get_current_user_id()),
            array('%d')
        );

        $this->wpdb->insert(
            $this->tblTemplateToUser,
            array(
                'userId' => get_current_user_id(),
                'templateId' => $id,
            ),
            array('%d', '%d')
        );

    }

    protected function redirectToEditPage($id = null)
    {
        wp_redirect(admin_url('/admin.php?page=wpbcu-barcode-templates-edit&id=' . $id));
        exit;
    }

    protected function redirectToCreatePage()
    {
        wp_redirect(admin_url('/admin.php?page=wpbcu-barcode-templates-create'));
        exit;
    }

    public function getActiveTemplate()
    {
        $userId = get_current_user_id();

        $userTemplate = $this->wpdb->get_row("SELECT * FROM `{$this->tblTemplateToUser}` WHERE `userId` = {$userId}");

        $chosenTemplateRow = null;

        if ($userTemplate) {
            $chosenTemplateRow = $this->wpdb->get_row("SELECT * FROM `{$this->tbl}` WHERE `id` = {$userTemplate->templateId}");
        }

        if (!$chosenTemplateRow) {
            $chosenTemplateRow = $this->wpdb->get_row("SELECT * FROM `{$this->tbl}` WHERE `slug` = 'default-1'");
        }

        try {
            if ($chosenTemplateRow && $chosenTemplateRow->matching) {
                $chosenTemplateRow->matching = @json_decode($chosenTemplateRow->matching);
            }
        } catch (\Throwable $th) {
            return $chosenTemplateRow;
        }

        $dimensions = new Dimensions();
        $activeDimension = $dimensions->getActive();

        if ($chosenTemplateRow && $chosenTemplateRow->is_base && $chosenTemplateRow->is_default && $activeDimension) {
            $chosenTemplateRow->uol_id = $activeDimension;
        }

        return $chosenTemplateRow;
    }

    public function getTemplateById($id)
    {
        $chosenTemplateRow = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM `{$this->tbl}` WHERE `id` = %s", $id),
            OBJECT
        );

        return $chosenTemplateRow;
    }

    public function getAllTemplates()
    {
        $userId = get_current_user_id();

        $dimensions = new Dimensions();

        $templates = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT TU.id AS 'is_active', T.* "
                    . " FROM `{$this->tbl}` AS T "
                    . " LEFT JOIN `{$this->tblTemplateToUser}` AS TU ON T.id = TU.templateId AND TU.userId = %s "
                    . " WHERE T.uol_id = '%d' OR T.is_base = 1 "
                    . " ORDER BY T.id ",
                $userId,
                $dimensions->getActive()
            )
        );

        $activeIndex = null;

        foreach ($templates as $key => $template) {
            if ($template->is_active) {
                $activeIndex = $key;
            }


            if ($template->matching) {
                $template->matching = @json_decode($template->matching);
            }
        }

        if ($activeIndex === null && $templates) {
            $templates[0]->is_active = $userId;
        }

        return $templates;
    }

    public function getConstantAttributes($template, $constant)
    {
        $attributes = array(
            "width" => $template->width,
            "height" => $template->height,
        );

        preg_match("/\[$constant\s(.*)\]/i", $template->template, $m);

        if (!isset($m[1])) {
            return $attributes;
        }

        $arr = explode(" ", $m[1]);

        if (!$arr) {
            return $attributes;
        }

        foreach ($arr as $value) {
            $attr = explode("=", $value);

            if ($attr && count($attr) === 2) {
                $attributes[$attr[0]] = $attr[1];
            }
        }

        return $attributes;
    }

    public function getNodes($template, $tag)
    {
        $list = array();

        $p = xml_parser_create();
        xml_parse_into_struct($p, $template, $vals, $index);
        xml_parser_free($p);

        if (!$vals) {
            return $list;
        }

        foreach ($vals as $node) {
            if (strtolower($node["tag"]) === $tag) {
                $attrs = array(
                    'dominant-baseline' => '',
                    'text-anchor' => '',
                );

                foreach ($node["attributes"] as $key => $value) {
                    $attrs[strtolower($key)] = strtolower($value);
                }
                $list[] = $attrs;
            }
        }

        return $list;
    }

    public function updatePaperSize($uolId, $width, $height)
    {
        $table = $this->wpdb->prefix . Database::$tablePaperFormats;
        $this->wpdb->update($table, array('width' => $width, 'height' => $height), array('is_roll' => 1, 'uol_id' => $uolId));
    }
}
