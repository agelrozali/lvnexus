<select name="order_product_category">
    <option value=""><?php echo __('All product categories', 'wpbcu-barcode-generator'); ?></option>

    <?php
    $current = isset($_GET['order_product_category']) ? $_GET['order_product_category'] : '';

    foreach ($values as $key => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            $key,
            $key == $current ? ' selected="selected"' : '',
            $label
        );
    }
    ?>
</select>