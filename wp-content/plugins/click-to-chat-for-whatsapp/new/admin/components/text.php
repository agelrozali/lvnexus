<?php
/**
 * text
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = (isset($input['title'])) ? $input['title'] : '';
$description = (isset($input['description'])) ? $input['description'] : '';
$label = (isset($input['label'])) ? $input['label'] : '';
$placeholder = (isset($input['placeholder'])) ? $input['placeholder'] : '';
$parent_class = (isset($input['parent_class'])) ? $input['parent_class'] : '';

?>
<div class="row ctc_component_text <?= $parent_class ?>">
    <div class="input-field col s12">
        <input name="<?= $dbrow ?>[<?= $db_key ?>]" type="text" value="<?= $db_value ?>" placeholder="<?= $placeholder ?>"/>
        <label for="pre_filled"><?= $label ?></label>
        <p class="description"><?= $description ?></p>
    </div>
</div>