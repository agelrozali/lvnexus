<p class="form-row form-field form-row-full">
    <label for="<?php echo "v_{$this->fieldName}[{$variation->ID}]"; ?>"><?php echo $this->fieldLabel; ?></label> &nbsp;
    <span style="display: block;">
        <input type="text" class="input-text" style="max-width: 48%;" name="<?php echo "v_{$this->fieldName}[{$variation->ID}]"; ?>" id="<?php echo "v_{$this->fieldName}[{$variation->ID}]"; ?>" value="<?php echo esc_html($value); ?>">
    </span>
</p>