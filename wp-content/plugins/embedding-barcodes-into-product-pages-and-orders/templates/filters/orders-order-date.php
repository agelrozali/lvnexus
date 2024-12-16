<?php $max = ($valueTo) ? "max=" . esc_html($valueTo) . "" : ""; ?>
<?php $min = ($value) ? "min=" . esc_html($value) . "" : ""; ?>
<input type="date" name="order_date" title="Date from" id="scanner_filter_order_date_from" value="<?php echo esc_html($value); ?>" <?php echo esc_html($max); ?> />
<input type="date" name="order_date_to" title="Date to" id="scanner_filter_order_date_to" value="<?php echo esc_html($valueTo); ?>" <?php echo esc_html($min); ?> />
<script>
    jQuery(document).ready(function(e) {
        try {
            const dateFrom = jQuery("#scanner_filter_order_date_from");
            const dateTo = jQuery("#scanner_filter_order_date_to");

            const handlerFrom = () => {
                const value = jQuery(event.target).val();

                if (value) dateTo.attr("min", value);
                else dateTo.removeAttr("min");
            };

            const handlerTo = () => {
                const value = jQuery(event.target).val();

                if (value) dateFrom.attr("max", value);
                else dateFrom.removeAttr("max");
            };

            dateFrom.change(handlerFrom)
            dateTo.change(handlerTo)
        } catch (error) {}
    });
</script>
<style>
    #scanner_filter_order_date_from,
    #scanner_filter_order_date_to {
        width: 155px !important;
    }
</style>