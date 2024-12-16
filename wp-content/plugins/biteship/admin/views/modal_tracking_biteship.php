<script type="text/template" id="tmpl-biteship-modal-tracking-biteship">
	<div class="wc-backbone-modal">
		<div class="wc-backbone-modal-content">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1><?php esc_html_e("Add tracking - order - #{{{ data.order_id }}}", "biteship"); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text">Close modal panel</span>
					</button>
				</header>
				<article>
                    <div class="">
                        <table class="">
                            <thead>
                                <tr>
                                <th style="width: 500px;text-align: left;">Items</th>
                                <th style="">Qty.</th></tr>
                            </thead>
                            <tbody>
                                {{{ data.items }}}
                            </tbody>
                        </table>
                        <hr style="margin: 20px 0px;">
                        <form action="" method="get">
                            <div class="">
                                <div style="margin-top: 8px">
                                <label for="sender_phone_no">Tracking number:</label>
                                <input name="sender_phone_no" value="{{{ data.waybill_id }}}" disabled/>
                            </div>
                            </div>
                        </form>
                    </div>
				</article>
				<footer>
					<div class="inner">
                        <button onclick="redirectPageTracking('{{{ data.waybill_id }}}', '{{{ data.waybill_url }}}')" class="button button-primary button-large"><?php esc_html_e(
                            "Cek Order",
                            "woocommerce"
                        ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>
<script>
    function redirectPageTracking(waybill_id, waybill_url){
        window.open( waybill_url+"&waybill_id="+waybill_id, '_blank');
    }
</script>
