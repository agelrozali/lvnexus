<?php
/**
 * Color
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title = (isset($input['title'])) ? $input['title'] : '';
$default_color = (isset($input['default_color'])) ? $input['default_color'] : '';
$description = (isset($input['description'])) ? $input['description'] : '';
$parent_class = (isset($input['parent_class'])) ? $input['parent_class'] : '';



?>
<div class="row ctc_component_color <?= $parent_class ?>">
    <?php
    if ('' !== $title) {
        ?>
        <div class="col s6">
            <p><?= $title ?></p>
        </div>
        <?php
    }
    ?>
    <div class="input-field col s6">
        <input class="ht-ctc-color" name="<?= $dbrow ?>[<?= $db_key ?>]" data-default-color="<?= $default_color ?>" id="<?= $db_key ?>" value="<?= $db_value ?>" type="text">
        <?php
        if ('' !== $description) {
            ?>
            <p class="description"><?= $description ?></p>
            <?php
        }
        ?>
    </div>
</div>