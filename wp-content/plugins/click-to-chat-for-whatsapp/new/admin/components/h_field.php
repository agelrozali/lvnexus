<?php
/**
 * template: hidden fields
 * 
 * @since 3.28
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<input name="<?= $dbrow ?>[<?= $db_key ?>]" type="hidden" style="display:none;" value="<?= $db_value ?>"/>