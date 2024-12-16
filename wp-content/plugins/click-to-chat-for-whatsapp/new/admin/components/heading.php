<?php
/**
 * number
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = (isset($input['title'])) ? $input['title'] : '';
$parent_class = (isset($input['parent_class'])) ? $input['parent_class'] : '';

?>

<div class="row ctc_component_heading <?= $parent_class ?>">
    <p class="description ht_ctc_subtitle"><?php _e( $title, 'click-to-chat-for-whatsapp' ); ?> </p>
</div>