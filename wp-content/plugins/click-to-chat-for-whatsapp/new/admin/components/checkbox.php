<?php
/**
 * checkbox
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = (isset($input['title'])) ? esc_attr($input['title']) : '';
$parent_class = (isset($input['parent_class'])) ? $input['parent_class'] : '';
$label = (isset($input['label'])) ? $input['label'] : '';
$description = (isset($input['description'])) ? $input['description'] : '';



?>
<div class="row ctc_component_checkbox <?= $parent_class ?>">
    <div class="input-field col s12">
        <p>
            <label>
                <input name="<?= $dbrow ?>[<?= $db_key ?>]" type="checkbox" class="<?= $db_key ?>" value="1" <?php checked( $db_value, 1 ); ?> />
                <span><?= $title ?></span>
            </label>
        </p>
        <?php
        if ('' !== $description) {
            ?>
            <p class="description"><?= $description ?></p>
            <?php
        }
        ?>
    </div>
</div>