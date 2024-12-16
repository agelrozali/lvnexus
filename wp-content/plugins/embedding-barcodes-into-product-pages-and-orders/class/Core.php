<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeTemplatesController;
use UkrSolution\BarcodesDigital\BarcodeTemplates\BarcodeView;
use UkrSolution\BarcodesDigital\Cart\BarcodeCart;
use UkrSolution\BarcodesDigital\Filters\Items;
use UkrSolution\BarcodesDigital\Generators\BarcodeImage;
use UkrSolution\BarcodesDigital\Helpers\UserFieldsMatching;
use UkrSolution\BarcodesDigital\Helpers\UserSettings;
use UkrSolution\BarcodesDigital\Helpers\Variables;
use UkrSolution\BarcodesDigital\Makers\ManualA4BarcodesMaker;
use UkrSolution\BarcodesDigital\Makers\TestA4BarcodesMaker;
use UkrSolution\BarcodesDigital\POS\POS_Orders;
use UkrSolution\BarcodesDigital\Updater\Updater;

class Core
{
    protected $config;
    protected $customTemplatesController;
    protected $dimensions;
    protected $updater;

    public function __construct($config)
    {
        $this->config = $config;
        $this->customTemplatesController = new BarcodeTemplatesController();
        $this->dimensions = new Dimensions();


        $ajaxPrefix = "_d";

        add_action('admin_menu', array($this, 'addMenuPages'), 9);
        add_action('admin_menu', array($this, 'adminEnqueueScripts'), 9);
        add_action('admin_enqueue_scripts', array($this, 'adminAllEnqueueScripts'), 9);
        add_filter('plugin_row_meta', array($this, 'pluginRowMeta'), 10, 2);

        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_barcodes_by_values', array($this, 'getBarcodesByValues'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_barcodes_test', array($this, 'getBarcodesTest'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_latest_version', array($this, 'getLatestVersion'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_all_algorithms', array($this, 'getAllAlgorithms'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_active_template', array($this, 'getActiveTemplate'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_all_templates', array($this, 'getAllTemplates'));

        $woocommerceModel = new WooCommerce();
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_barcodes', array($woocommerceModel, 'getBarcodes'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_categories', array($woocommerceModel, 'getCategories'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_attributes', array($woocommerceModel, 'getAttributes'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_local_attributes', array($woocommerceModel, 'getLocalAttributes'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_check_custom_field', array($woocommerceModel, 'countProductsByCustomField'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_barcodes_by_orders', array($woocommerceModel, 'getBarcodesByOrders'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_barcodes_by_order_products', array($woocommerceModel, 'getBarcodesByOrderProducts'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_barcodes_by_order_items', array($woocommerceModel, 'getBarcodesByOrderProducts'));

        $preview = new Preview();
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_preview_barcode', array($preview, 'getBarcode'));


        $formatsModel = new Formats();
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_delete_format', array($formatsModel, 'deleteFormat'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_save_format', array($formatsModel, 'saveFormat'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_all_formats', array($formatsModel, 'getAllFormats'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_formats_by_paper', array($formatsModel, 'getFormatsByPaper'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_format', array($formatsModel, 'getFormat'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_get_all_paper_formats', array($formatsModel, 'getAllPaperFormats'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_save_paper_format', array($formatsModel, 'savePaperFormat'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_delete_paper_format', array($formatsModel, 'deletePaperFormat'));

        $productsModel = new Products();
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_products_search', array($productsModel, 'search'));


        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_save_digital_template_changes', array($this->customTemplatesController, 'updateDigital'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_save_template_changes', array($this->customTemplatesController, 'update'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_delete_template', array($this->customTemplatesController, 'delete'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_copy_template', array($this->customTemplatesController, 'copy'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_create_template', array($this->customTemplatesController, 'createNewTemplate'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_set_active_template', array($this->customTemplatesController, 'setactive'));

        $settings = new Settings();
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_change_uol', array($settings, 'changeUol'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_update_user_settings', array($settings, 'updateUserSettings'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_update_user_field_matching', array($settings, 'updateUserFieldMatching'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_update_template_field_matching', array($settings, 'updateTemplateFieldMatching'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_clear_template_field_matching', array($settings, 'clearTemplateFieldMatching'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_save_import_settings', array($settings, 'saveImportSettings'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_save_session', array($settings, 'saveSession'));

        $shortcodes = new Shortcodes();
        add_shortcode('barcode', array($shortcodes, 'get'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_shortcodes_get', array($shortcodes, 'shortcodesGet'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_shortcode_get_by_id', array($shortcodes, 'shortcodeGetById'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_shortcode_remove', array($shortcodes, 'shortcodeRemove'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_shortcode_set_default', array($shortcodes, 'shortcodeSetDefault'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_shortcode_create', array($shortcodes, 'shortcodeCreate'));

        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_settings_save_orders', array($shortcodes, 'saveOrder'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_settings_save_products', array($shortcodes, 'saveProduct'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_settings_save_custom', array($shortcodes, 'saveCustom'));

        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_generate_products_barcodes', array($shortcodes, 'generateProductsBarcodes'));
        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_generate_barcode_product_image', array($shortcodes, 'generateBarcodeProductImage'));

        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_import_user_codes', array($productsModel, 'importCodes'));

        add_action("woocommerce_settings_save_general", array($shortcodes, 'wcGeneralUpdated'), 10, 0);
        add_action('admin_head', array($productsModel, 'product_column_barcode_style'));
        add_action('manage_product_posts_custom_column', array($productsModel, 'product_column_barcode'), 2);


        $productField = UserSettings::getoption('productField', false);

        if ($productField) {
            add_action('woocommerce_product_options_sku', array($productsModel, 'woocommerce_product_options_sku'), 15);
            add_action('woocommerce_process_product_meta', array($productsModel, 'woocommerce_process_product_meta'));

            add_action('woocommerce_variation_options', array($productsModel, 'woocommerce_variation_options'), 15, 3);
            add_action('woocommerce_save_product_variation', array($productsModel, 'woocommerce_save_product_variation'), 10, 2);
            add_filter('woocommerce_available_variation', array($productsModel, 'woocommerce_available_variation'));
        }


        $metaBoxes = new MetaBoxes();
        $adminProductPageParams = UserSettings::getJsonSectionOption('adminProductPageParams', 'product', 1);
        $adminOrderPageParams = UserSettings::getJsonSectionOption('adminOrderPageParams', 'order', 1);
        $adminOrderPreviewParams = UserSettings::getJsonSectionOption('adminOrderPreviewParams', 'order', 1);
        $adminOrderItemPageParams = UserSettings::getJsonSectionOption('adminOrderItemPageParams', 'product', 1);

        if (isset($adminProductPageParams["status"]) && $adminProductPageParams["status"]) {
            add_action('add_meta_boxes', array($metaBoxes, 'productPage'));
            add_action('save_post_product', array($metaBoxes, "saveProductPage"));
            add_action('woocommerce_variation_header', array($productsModel, 'variationBarcode'));
            add_action('woocommerce_product_after_variable_attributes', array($productsModel, 'variationInnerBarcode'), 10, 3);
        }

        if (isset($adminOrderPageParams["status"]) && $adminOrderPageParams["status"]) {
            add_action('add_meta_boxes', array($metaBoxes, 'orderPage'));
        }

        if (isset($adminOrderPreviewParams["status"]) && $adminOrderPreviewParams["status"]) {
            $orders = new Orders();
            add_action('woocommerce_admin_order_preview_get_order_details', array($orders, "woocommerce_admin_order_preview_get_order_details"));
        }

        if (isset($adminOrderItemPageParams["status"]) && $adminOrderItemPageParams["status"]) {
            $orders = new Orders();
            add_action('woocommerce_after_order_itemmeta', array($orders, "addOrderItemsBarcode"), 10, 2);
        }


        add_action('wp_ajax_a4barcode' . $ajaxPrefix . '_dimensionsGet_get', array($this->dimensions, 'get'));

        $POS_Orders = new POS_Orders();

        add_filter('woocommerce_payment_complete', array($POS_Orders, "woocommerce_payment_complete"), 100, 1);

        add_action('init', array($this, "parseDigitalRequest"));
        add_action('init', function () {
            $emails = new Emails();
            $productsModel = new Products();

            $Integration = new Integration();
            $Integration->init();

            $orderBarcodeEmailParams = UserSettings::getJsonSectionOption('orderBarcodeEmailParams', 'order');
            $productBarcodeEmailParams = UserSettings::getJsonSectionOption('productBarcodeEmailParams', 'product');

            if (isset($orderBarcodeEmailParams["status"]) && $orderBarcodeEmailParams["status"]) {
                add_filter('woocommerce_email_customer_details', array($emails, 'woocommerce_email_customer_details'), 10, 3);
            }

            if (isset($productBarcodeEmailParams["status"]) && $productBarcodeEmailParams["status"]) {
                add_filter('woocommerce_order_item_meta_end', array($emails, 'woocommerce_order_item_meta_end'), 10, 3);
            }

            $barcodesOnProductPageParams = UserSettings::getJsonSectionOption('barcodesOnProductPageParams', 'product');

            if (isset($barcodesOnProductPageParams["status"]) && $barcodesOnProductPageParams["status"]) {
                $productsModel->shortcodeOnProductPage();
            }
        });

        $productsModel = new Products();
        add_action('transition_post_status', array($productsModel, 'transition_post_status'), 10, 3);
    }

    public function addMenuPages()
    {
        $icons = str_replace("class/", "", \plugin_dir_url(__FILE__)) . "assets/icons/";



        $icon = $icons . 'barcode-generator-menu-logo.svg';
        add_menu_page(
            __('Barcode Genera...', 'wpbcu-barcode-generator'),
            __('Barcode Genera...', 'wpbcu-barcode-generator'),
            'read',
            'wpbcu-digital-settings',
            array($this, 'settingsPage'),
            $icon
        );

        add_submenu_page('wpbcu-digital-settings', __('Settings', 'wpbcu-barcode-generator'), __('Settings', 'wpbcu-barcode-generator'), 'export', 'wpbcu-digital-settings', array($this, 'settingsPage'));

        add_submenu_page(
            'wpbcu-digital-settings',
            __('Support', 'wpbcu-barcode-generator'),
            '<span class="a4barcodeDigital_support">' . __('Support', 'wpbcu-barcode-generator') . '</span>',
            'read',
            'wpbcu-digital-generator-support',
            array($this, 'emptyPage')
        );
        add_submenu_page(null, __('Barcode-Generator Page', 'wpbcu-barcode-generator'), __('Barcode-Generator Page', 'wpbcu-barcode-generator'), 'read', 'wpbcu-digital-generator-print', array($this, 'emptyPage'));
    }

    public function shortcodesPage()
    {

        echo '<div><a href="#" id="barcode_d-shortcodes-section"></a></div>';
    }

    public function settingsPage()
    {
        wp_enqueue_style('codemirror', Variables::$A4B_PLUGIN_BASE_URL . 'assets/chosen/css/chosen.min.css', array(), false);
        wp_enqueue_script('codemirror', Variables::$A4B_PLUGIN_BASE_URL . 'assets/chosen/js/chosen.jquery.min.js', array(), false, true);

        echo '<div><a href="#" id="barcode_d-settings-section"></a></div>';
    }

    public function settingsInit()
    {
        add_submenu_page(
            'wpbcu-barcode-generator',
            __('Settings', 'wpbcu-barcode-generator'),
            __('Settings', 'wpbcu-barcode-generator'),
            'export',
            'wpbcu-barcode-settings',
            array($this, 'settingsPage')
        );
    }

    public function getBarcodesByValues()
    {
        Request::ajaxRequestAccess();

        $post = array();

        foreach (array('format', 'requestTime', 'isUseApi') as $key) {
            if (isset($_POST[$key])) {
                $post[$key] = sanitize_text_field($_POST[$key]);
            }
        }
        if (isset($_POST['fields'])) {
            $post['fields'] = USWBG_a4bRecursiveSanitizeTextField($_POST['fields']);
        }

        $validationRules = array(
            'format' => 'required',
            'requestTime' => 'string',
            'fields' => 'required|array',
        );

        $data = Validator::create($post, $validationRules, true)->validate();

        $barcodesMaker = new ManualA4BarcodesMaker($data);
        $requestTime = (isset($data['requestTime'])) ? $data['requestTime'] : '';
        $result = $barcodesMaker->make();


        uswbg_a4bJsonResponse(array_merge($result, ["requestTime" => $requestTime]));
    }

    public function getBarcodesTest()
    {
        Request::ajaxRequestAccess();
        $barcodesMaker = new TestA4BarcodesMaker();
        $result = $barcodesMaker->make();
        uswbg_a4bJsonResponse($result);
    }

    public function pluginRowMeta($links, $file)
    {
        if (Variables::$A4B_PLUGIN_BASE_NAME == $file) {
            $rowMeta = ucfirst(strtolower(Variables::$A4B_PLUGIN_PLAN));
            array_splice($links, 1, 0, $rowMeta);
        }

        return (array) $links;
    }

    public function disablePluginUpdates($plugins)
    {
        $pluginCurrentPathFile = plugin_basename(__FILE__);
        $startCutPosition = strpos($pluginCurrentPathFile, '/');
        $pluginDirName = substr($pluginCurrentPathFile, 0, $startCutPosition);
        if ($plugins && isset($plugins->response) && isset($plugins->response[$pluginDirName . '/barcode_generator.php'])) {
            unset($plugins->response[$pluginDirName . '/barcode_generator.php']);
        }

        return $plugins;
    }

    public function adminAllEnqueueScripts()
    {
        if (!is_admin()) {
            return;
        }

        wp_register_style('import_categories_button_demo', Variables::$A4B_PLUGIN_BASE_URL . 'templates/actions-assets/style.css', false, '2.0.2');
        wp_enqueue_style('import_categories_button_demo');

        wp_enqueue_script('import_buttons_actions_assets', Variables::$A4B_PLUGIN_BASE_URL . 'templates/actions-assets/script.js');
    }

    public function adminEnqueueScripts($isFront = false)
    {
        Request::ajaxRequestAccess();
        global $wp_version;
        global $current_user;

        wp_enqueue_script("barcode_loader_digital", Variables::$A4B_PLUGIN_BASE_URL."assets/js/index-2.0.2-00123546.js", array("jquery"), null, true);
wp_enqueue_script("barcode_api_digital", Variables::$A4B_PLUGIN_BASE_URL."assets/js/api-2.0.2-00123546.js", array("jquery"), null, true);
wp_enqueue_style("barcode_core_css_digital", Variables::$A4B_PLUGIN_BASE_URL."public/dist/css/app_demo_2.0.2-00123546.css", null, null);$appJsPath = Variables::$A4B_PLUGIN_BASE_URL."public/dist/js/app_demo_2.0.2-00123546.js";
$vendorJsPath = Variables::$A4B_PLUGIN_BASE_URL."public/dist/js/chunk-vendors_demo_2.0.2-00123546.js";
$jszip = Variables::$A4B_PLUGIN_BASE_URL."assets/js/jszip.min-2.0.2-00123546.js";


        $active_template = $this->customTemplatesController->getActiveTemplate();
        $activeDimension = $this->dimensions->getActive();
        $allPaperFormats = array();
        $barcodeSizes = array();
        $importCodes = array();

        $barcodeImage = new BarcodeImage();
        $barcodeSizes = $barcodeImage->getBarcodeSizes();

        $shortcodesModel = new Shortcodes();
        $shortcodesOrder = $shortcodesModel->shortcodesGetByType('order');
        $shortcodesProduct = $shortcodesModel->shortcodesGetByType('product');
        $shortcodesCustom = $shortcodesModel->shortcodesGetByType('custom');

        $productsModel = new Products();
        $importCodes = $productsModel->getImportFilesData();

        $productsModel = new Products();
        $importCodes = $productsModel->getImportFilesData();

        $jsWindowKey = 'a4bjsDigital';
        $barcodeLoaderScriptSlug = 'barcode_loader_digital';


        $woocommerceModel = new WooCommerce();
        $wcAttributes = $woocommerceModel->getAttributes(false);

        $isBoosterForWC = is_plugin_active("woocommerce-jetpack/woocommerce-jetpack.php");
        if (!$isBoosterForWC) {
            $isBoosterForWC = is_plugin_active("booster-plus-for-woocommerce/booster-plus-for-woocommerce.php");
        }

        $previewProduct = null;

        if ($current_user) {
            try {
                $_pidData = \get_user_meta($current_user->ID, 'usplp_product_preview', true);
                $_pidData = explode(":", $_pidData);

                $_pid = $_pidData[0];
                $type = count($_pidData) == 2 ? $_pidData[1] : "";
                $_post = null;

                if($type == "user") {
                    $_post = \get_user_by('id', $_pid);

                    if ($_post) {
                        $_post = (array)$_post->data;
                        $_post['post_type'] = 'user';
                        $previewProduct = $_post;
                    }
                } else if ($_pid) {
                    $_post = \get_post($_pid);

                    if ($_post) {
                        $previewProduct = array('ID' => $_post->ID, 'post_title' => $_post->post_title, 'post_type' => $_post->post_type);
                    }
                }

                if (!$_pid || !$_post) {
                    $args = array('numberposts' => 'n', 'post_type' => 'product');
                    $recentProducts = \wp_get_recent_posts($args, OBJECT);

                    if ($recentProducts && count($recentProducts)) {
                        $_post = $recentProducts[0];
                        $previewProduct = array('ID' => $_post->ID, 'post_title' => $_post->post_title, 'post_type' => $_post->post_type);
                    }
                }
            } catch (\Throwable $th) {
            }
        }

        wp_localize_script($barcodeLoaderScriptSlug, $jsWindowKey, array(
            'pluginUrl' => Variables::$A4B_PLUGIN_BASE_URL,
            'pluginType' => Variables::$A4B_PLUGIN_TYPE,
            'websiteUrl' => get_bloginfo("url"),
            'adminUrl' => get_admin_url(),
            'pluginVersion' => '2.0.2',
            'isWoocommerceActive' => is_plugin_active('woocommerce/woocommerce.php'),
            'isCF7Active' => is_plugin_active('contact-form-7/wp-contact-form-7.php'),
            'isTieredPriceActive' => (is_plugin_active('tier-pricing-table/tier-pricing-table.php') || is_plugin_active('tier-pricing-table-premium/tier-pricing-table.php')),
            'isAtumPoActive' => is_plugin_active('atum-stock-manager-for-woocommerce/atum-stock-manager-for-woocommerce.php'),
            'isPbetActive' => is_plugin_active('product-batch-expiry-tracking-for-woocommerce/product-batch-expiry-tracking-for-woocommerce.php'),
            'isWcShippingLocalPickupPlusActive' => is_plugin_active('woocommerce-shipping-local-pickup-plus/woocommerce-shipping-local-pickup-plus.php'),
            'wt_seq_ordnum' => defined('WT_SEQUENCIAL_ORDNUMBER_VERSION') || is_plugin_active("woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers-pro.php"),
            'autologin_links' => is_plugin_active('autologin-links/autologin-links.php'),
            'isBoosterForWC' => $isBoosterForWC,
            'isWcPdfInvoicesPackingSlipsActive' => is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php'),
            'isProductField' => UserSettings::getoption('productField', false),
            'appJsPath' => $appJsPath,
            'vendorJsPath' => $vendorJsPath,
            'jszip' => $jszip,
            'activeTemplateData' => $active_template ? $active_template : null,
            'allPaperFormats' => $allPaperFormats,
            'allTemplates' => $this->getAllTemplates(false),
            'shortcodesOrder' => $shortcodesOrder,
            'shortcodesProduct' => $shortcodesProduct,
            'shortcodesCustom' => $shortcodesCustom,
            'rest_root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'activeDimension' => $activeDimension,
            'dimensions' => $this->dimensions->get(false),
            'barcodeTypes' => $this->config['listAlgorithm'],
            'barcodeSizes' => $barcodeSizes,
            'uid' => get_current_user_id(),
            'wp_version' => $wp_version,
            'wc_version' => defined("WC_VERSION") ? WC_VERSION : 0,
            'currentPage' => Variables::getCurrentPage(),
            'ajaxUrl' => get_admin_url() . 'admin-ajax.php',
            'isFront' => $isFront ? 1 : 0,
            'plugins' => $this->checkExternalPlugins(),
            'isCustomSortEnabled' => function_exists('barcodes_products_sort_items_hook'),
            'tab' => isset($_GET["tab"]) ? sanitize_text_field($_GET["tab"]) : "",
            'previewProduct' => $previewProduct
        ));

        $generalSettings = UserSettings::getGeneral();

        $userSettings = UserSettings::get();

        $userFieldsMatching = UserFieldsMatching::get();

        $jsL10n = require_once Variables::$A4B_PLUGIN_BASE_PATH . 'config/jsL10n.php';


        wp_localize_script($barcodeLoaderScriptSlug, 'a4barcodesL10nDigital', $jsL10n);
        wp_localize_script($barcodeLoaderScriptSlug, 'a4barcodesUSDigital', $userSettings);
        wp_localize_script($barcodeLoaderScriptSlug, 'a4barcodesGSDigital', $generalSettings);
        wp_localize_script($barcodeLoaderScriptSlug, 'a4barcodesFMDigital', $userFieldsMatching);
        wp_localize_script($barcodeLoaderScriptSlug, 'a4barcodesCodesDigital', $importCodes);
        wp_localize_script($barcodeLoaderScriptSlug, 'a4barcodesATTRDigital', $wcAttributes);
    }

    public function emptyPage()
    {
    }

    public function pageBarcodeTemplates()
    {
        wp_enqueue_media();
        $this->enqueueTemplatesAssets();
        $this->customTemplatesController->edit();
    }

    public function getAllAlgorithms()
    {
        Request::ajaxRequestAccess();
        uswbg_a4bJsonResponse(array(
            'list' => $this->config['listAlgorithm'],
            'success' => array(),
            'error' => array(),
        ));
    }

    public function getLatestVersion()
    {
        Request::ajaxRequestAccess();
        global $wp_version;
        $lastReleaseDataFallback = array('url' => '', 'version' => '');

        $lastReleaseDataResponse = wp_remote_get('https://www.ukrsolution.com/CheckUpdates/PrintBarcodeGeneratorForWordpressV3.json');
        $lastReleaseData = is_wp_error($lastReleaseDataResponse)
            ? $lastReleaseDataFallback
            : (json_decode(wp_remote_retrieve_body($lastReleaseDataResponse), true) ?: $lastReleaseDataFallback);

        $barcodes = [
            'isLatest' => (int) version_compare('2.0.2', $lastReleaseData['version'], '>='),
            'latest' => $lastReleaseData['version'], 
            'version' => '2.0.2',
            'downloadUrl' => $lastReleaseData['url'],
            'pluginUrl' => Variables::$A4B_PLUGIN_BASE_URL,
            'type' => strtolower(Variables::$A4B_PLUGIN_PLAN),
            'wp_version' => $wp_version,
            'isWoocommerceActive' => is_plugin_active('woocommerce/woocommerce.php'),
            'active_template' => $this->customTemplatesController->getActiveTemplate(),
        ];

        uswbg_a4bJsonResponse($barcodes);
    }

    public function getActiveTemplate()
    {
        uswbg_a4bJsonResponse($this->customTemplatesController->getActiveTemplate());
    }

    public function getAllTemplates($isAjax = true)
    {
        Request::ajaxRequestAccess();
        $templates = $this->customTemplatesController->getAllTemplates();
        $embeddingSettings = get_option(Database::$optionSettingsProductsKey, array());

        if (isset($embeddingSettings["template"])) {
            foreach ($templates as &$template) {
                if ($template->id === $embeddingSettings["template"]) {
                    $template->embed = true;
                    break;
                }
            }
        }

        $dimensions = new Dimensions();
        $activeDimension = $dimensions->getActive();

        foreach ($templates as &$template) {
            if ($template->is_base && $template->is_default && $activeDimension) {
                $template->uol_id = $activeDimension;
            }
        }

        if ($isAjax === false) {
            return $templates;
        } else {
            uswbg_a4bJsonResponse($templates);
        }
    }

    public function parseDigitalRequest()
    {
        if (preg_match('/\/d-barcodes\/(.*?).png(.*?)?$/', $_SERVER["REQUEST_URI"], $m)) {
            $barcodeImage = new BarcodeImage();
            $barcodeImage->parseHash($m[1]);

            echo "";
            exit;
        }
    }

    public function parsePrintRequest()
    {
    }

    protected function enqueueTemplatesAssets()
    {
        if ('BASIC' !== Variables::$A4B_PLUGIN_PLAN) {
            wp_enqueue_script('barcode_template_preview', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/barcode_template_preview-2.0.2-00123546.js', array('jquery'), null, true);
            wp_localize_script('barcode_templates', 'a4bBarcodeTemplates', array('pluginUrl' => Variables::$A4B_PLUGIN_BASE_URL));

            wp_enqueue_style('codemirror', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/codemirror/codemirror.css', array(), false);
            wp_enqueue_script('codemirror', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/codemirror/codemirror.js', array(), false, true);
            wp_enqueue_script('codemirror_xml', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/codemirror/mode/xml/xml.js', array('codemirror'), false, true);
            wp_enqueue_script('codemirror_js', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/codemirror/mode/javascript/javascript.js', array('codemirror'), false, true);
            wp_enqueue_script('codemirror_css', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/codemirror/mode/css/css.js', array('codemirror'), false, true);
            wp_enqueue_script('codemirror_html', Variables::$A4B_PLUGIN_BASE_URL . 'assets/js/codemirror/mode/htmlmixed/htmlmixed.js', array('codemirror'), false, true);
        }
    }

    private function checkExternalPlugins()
    {
        $alg_wc_ean_title = get_option('alg_wc_ean_title', __('EAN', 'ean-for-woocommerce'));

        $wpm_pgw_label = get_option('wpm_pgw_label', __('EAN', 'product-gtin-ean-upc-isbn-for-woocommerce'));
        $wpm_pgw_label = sprintf(__('%s Code:', 'product-gtin-ean-upc-isbn-for-woocommerce'), $wpm_pgw_label);

        $hwp_gtin_text = get_option('hwp_gtin_text');
        $hwp_gtin_text = (!empty($hwp_gtin_text) ? $hwp_gtin_text : 'GTIN');

        $plugins = array(
            "wc_appointments" => array('status' => is_plugin_active('woocommerce-appointments/woocommerce-appointments.php'), 'label' => 'WooCommerce Appointments'),
            "openpos" => array('status' => is_plugin_active('woocommerce-openpos/woocommerce-openpos.php'), 'label' => 'Product Id'),
            "atum" => array('status' => is_plugin_active('atum-stock-manager-for-woocommerce/atum-stock-manager-for-woocommerce.php'), 'label' => ''),
            "license_manager" => array('status' => is_plugin_active('license-manager-for-woocommerce/license-manager-for-woocommerce.php'), 'label' => ''),
            array('key' => '_alg_ean', 'status' => function_exists('alg_wc_ean'), 'label' => 'EAN for WooCommerce', 'fieldLabel' => $alg_wc_ean_title . ' <sup>(EAN for WooCommerce)</sup>'),
            array('key' => '_wpm_gtin_code', 'status' => function_exists('wpm_product_gtin_wc'), 'label' => 'Product GTIN (EAN, UPC, ISBN) for WooCommerce', 'fieldLabel' => $wpm_pgw_label . ' <sup style="border: none; font-weight: normal;" title="Product GTIN (EAN, UPC, ISBN) for WooCommerce">(Product GTIN (EAN, UPC, ISBN))</sup>'),
            array('key' => 'hwp_product_gtin', 'status' => class_exists('Woo_GTIN'), 'label' => 'WooCommerce UPC, EAN, and ISBN', 'fieldLabel' => $hwp_gtin_text . ' <sup>(WooCommerce UPC, EAN, and ISBN)</sup>'),
            array('key' => '_wepos_barcode', 'status' => is_plugin_active('wepos/wepos.php'), 'label' => 'WePOS', 'fieldLabel' => 'Barcode <sup style="border: none; font-weight: normal;">(WePOS)</sup>'),
            array('key' => '_ts_gtin', 'status' => is_plugin_active('woocommerce-germanized/woocommerce-germanized.php'), 'label' => 'GTIN - Germanized for WooCommerce', 'fieldLabel' => 'GTIN <sup>(Germanized for WooCommerce)</sup>'),
            array('key' => '_ts_mpn', 'status' => is_plugin_active('woocommerce-germanized/woocommerce-germanized.php'), 'label' => 'MPN - Germanized for WooCommerce', 'fieldLabel' => 'MPN <sup>(Germanized for WooCommerce)</sup>'),
        );

        foreach ($plugins as &$plugin) {
            if (isset($plugin["fieldLabel"])) {
                $plugin["fieldLabel"] = str_replace('<sup>', '<sup style="border: none; font-weight: normal;">', $plugin["fieldLabel"]);
            }
        }

        return $plugins;
    }
}
