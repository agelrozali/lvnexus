<?php
/**
 * count field
 * useful to update the settings each time when save changes.. (even if settings or not changed) - to clear cache, ..
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$db_value = intval($db_value);
$db_value = ++$db_value;
?>
<div class="ctc_count">
    <input name="<?= $dbrow; ?>[count]" value="<?= $db_value; ?>" type="hidden" class="hide">
</div>