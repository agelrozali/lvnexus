<?php do_action('age_gate/script_template/before'); ?>
<<?php echo $settings->renderer ?: 'template' ?> id="tmpl-age-gate" <?php echo $settings->renderer === 'script' ? 'type="text/template"' : '' ?>>
    <?php do_action('age_gate/script_content/before'); ?>
    <?php echo $this->section('content'); ?>
    <?php do_action('age_gate/script_content/after'); ?>
</<?php echo $settings->renderer ?: 'template' ?>>
<?php do_action('age_gate/script_template/after'); ?>
