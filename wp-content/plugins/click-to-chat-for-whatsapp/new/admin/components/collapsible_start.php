<?php
/**
 * collapsible - start code
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = (isset($input['title'])) ? esc_attr($input['title']) : '';

$description = (isset($input['description'])) ? $input['description'] : '';

$active = 'active';
$collapsible = (isset($input['collapsible'])) ? $input['collapsible'] : '';
if ('no' == $collapsible) {
    $active = '';
}

$ul_class = (isset($input['ul_class'])) ? $input['ul_class'] : '';

?>

<ul class="collapsible <?= $ul_class ?>">
<li class="<?= $active ?>">
<div class="collapsible-header" id="showhide_settings"><?= $title ?>
    <span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
</div>
<div class="collapsible-body">

<?php
if ('' !== $description) {
    ?>
    <p class="description"><?= $description; ?></p>
    <br>
    <?php
}