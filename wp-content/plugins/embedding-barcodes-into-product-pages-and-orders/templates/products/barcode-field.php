<p class="form-field <?php echo $this->fieldName; ?>_field ">
    <label for="<?php echo $this->fieldName; ?>"><?php echo $this->fieldLabel; ?></label>
    <input type="text" class="short" style="" name="<?php echo $this->fieldName; ?>" id="<?php echo $this->fieldName; ?>" value="<?php echo esc_html($value); ?>">
</p>