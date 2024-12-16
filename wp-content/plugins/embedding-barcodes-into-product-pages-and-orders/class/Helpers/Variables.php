<?php

namespace UkrSolution\BarcodesDigital\Helpers;

class Variables
{

    public static $A4B_PLUGIN_BASE_NAME = USWBG_PLUGIN_BASE_NAME;
    public static $A4B_PLUGIN_BASE_URL = USWBG_PLUGIN_BASE_URL;
    public static $A4B_PLUGIN_BASE_PATH = USWBG_PLUGIN_BASE_PATH;
    public static $A4B_SITE_BASE_URL = USWBG_SITE_BASE_URL;
    public static $A4B_PLUGIN_PLAN = USWBG_PLUGIN_PLAN;
    public static $A4B_PLUGIN_TYPE = USWBG_PLUGIN_TYPE;

    public static $codePref = "WyIuIiwibiIsIm8iLCJpIiwicyJd";
    public static $code = "WyJyIiwiZSIsInYiLCIgIiwibyIsIm0iLCJlIiwiZCJd";
    public static $codeSuf = "WyIgIiwicyIsImkiLCIgIiwicyIsImkiLCJoIiwiVCJd";
    public static $infoClass = "bm90aWNlIG5vdGljZS1lcnJvcg";
    public static $infoId = "bm90aWNlLWJhcmNvZGUtdmVyc2lvbg";
    public static $infoView = "ZGlzcGxheTpub25l";
    public static $infoMessage = "TmV3IHZlcnNpb24gb2YgIkJhcmNvZ" . "GUgRGlnaXRhbCIgaXMgYXZhbGlhYmxlLCB5b3UgY2FuIGRvd25sb2FkIGl0IGhlcmU";
    public static $info = "aHR0cHM6Ly93d3cudWtyc29sdXRpb24uY29tL1dvcmRwcmVzcy9EaWdpdG" . "FsLUJhcmNvZGUtR2VuZXJhdG9yLWZvci1FbWJlZGRpbmctaW50by1QYWdlcy1hbmQtUG9zdHM";

    public static function getString($str)
    {
        return base64_decode($str);
    }

    public static function initCodes(&$inst, $args)
    {
        $inst->barcodeCPref = $args[0];
        $inst->barcodeCode = $args[1];
        $inst->barcodeCSuf = $args[2];
    }

    public static function convertArr2String($arr)
    {
        return implode("", $arr);
    }

    public static function getCurrentPage()
    {
        $postType = (isset($_GET["post_type"])) ? sanitize_text_field($_GET["post_type"]) : null;
        $postId = (isset($_GET["post"])) ? sanitize_text_field($_GET["post"]) : null;
        $page = (isset($_GET["page"])) ? sanitize_text_field($_GET["page"]) : null;

        if (!$postType && $postId) {
            $post = get_post($postId);
            if ($post && isset($post->post_type)) $postType = $post->post_type;
        }

        if (in_array($postType, array("product", "product_variation", "shop_order"))) {
            return $postType;
        } else if (in_array($page, array("wpcf7", "flamingo", "wpbcu-barcode-settings", "wpbcu-barcode-templates-edit"))) {
            return $page;
        }

        return "";
    }
}
