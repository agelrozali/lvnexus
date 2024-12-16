<script>
 function hideError(){
    var x = document.getElementById("setting-error-biteship");
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
 }
</script>


<div id="setting-error-biteship" style="<?= isset($display_node) ? $display_node : "display:none" ?>" class="error">
    <p><strong><span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">Biteship for WooCommerce</strong></p>
    <p>Biteship for WooCommerce plugin has been installed! But we detected there's no WooCommerce plugin installed. Make sure you have installed <em><a href="//<?= $_SERVER[
        "HTTP_HOST"
    ] ?>/wp-admin/plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=600&height=550" class="thickbox">WooCommerce plugin</a></em> to use Biteship.</p>
    <button type="button" class="notice-dismiss" onclick="hideError()"><span class="screen-reader-text">Dismiss this notice.</span></button>
</div>