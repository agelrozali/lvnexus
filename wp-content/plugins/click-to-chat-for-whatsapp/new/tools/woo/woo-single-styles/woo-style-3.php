<?php
/**
 * Style - 3
 * 
 * WhatsApp icon
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$s3_options = get_option( 'ht_ctc_s3' );
$s3_options = apply_filters( 'ht_ctc_fh_s3_options', $s3_options );
$s3_type = ( isset( $s3_options['s3_type']) ) ? esc_attr( $s3_options['s3_type'] ) : 'simple';

$s3_img_size = esc_attr( $s3_options['s3_img_size'] );
$img_size = esc_attr( $s3_options['s3_img_size'] );
if ( '' == $img_size ) {
    $img_size = "50px";
}

$rtl_css = "";
if ( function_exists('is_rtl') && is_rtl() ) {
    $rtl_css = "flex-direction:row-reverse;";
}

$s3_css = "display:inline-flex;justify-content:center;align-items:center;$rtl_css ";
$s3_cta_textcolor = (isset( $s3_options['cta_textcolor'])) ? esc_attr( $s3_options['cta_textcolor'] ) : '';
$s3_cta_bgcolor = (isset( $s3_options['cta_bgcolor'])) ? esc_attr( $s3_options['cta_bgcolor'] ) : '#ffffff';
$s3_cta_font_size = (isset( $s3_options['cta_font_size'])) ? esc_attr( $s3_options['cta_font_size'] ) : '';

$s3_cta_textcolor = ('' !== $s3_cta_textcolor) ? "color: $s3_cta_textcolor" : "";
$s3_cta_bgcolor = ('' !== $s3_cta_bgcolor) ? "background-color: $s3_cta_bgcolor" : "";
$s3_cta_font_size = ('' !== $s3_cta_font_size) ? "font-size: $s3_cta_font_size" : "";

$s3_cta_css = "padding: 0px 16px; line-height: 1.6; $s3_cta_font_size; $s3_cta_bgcolor; $s3_cta_textcolor; border-radius:10px; margin:0 10px; ";
$s3_cta_class = "ht-ctc-cta ";


$ht_ctc_svg_css = "pointer-events:none; display:block; height:$img_size; width:$img_size;";

include_once HT_CTC_PLUGIN_DIR .'new/inc/assets/img/ht-ctc-svg-images.php';

?>
<div title="<?= $call_to_action ?>" style="<?= $s3_css ?>">
    <?= ht_ctc_style_3_svg( $img_size, $type, $ht_ctc_svg_css ); ?>
</div>