<script type="text/template" id="tmpl-biteship-modal-order-biteship">
	<div class="wc-backbone-modal">
		<div class="wc-backbone-modal-content">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1><?php esc_html_e("Data Pengirim", "biteship"); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text">Close modal panel</span>
					</button>
				</header>
				<article>
					<form action="" method="post">
							<input type="hidden" name="is_bulk" value="{{{ data.is_bulk }}}" />
            				<input type="hidden" name="order_id" value="{{{ data.order_id }}}" />
						<div>
							<label for="sender_name">Pengirim</label><br>
							<input name="sender_name" value="{{{ data.shipper_name }}}" />
						</div>
						<div style="margin-top: 8px">
							<label for="sender_phone_no">No. Handphone</label><br>
							<input name="sender_phone_no" value="{{{ data.shipper_phone_no }}}" />
						</div>
					</form>
				</article>
				<footer>
					<div class="inner">
						<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e("Order Biteship", "woocommerce"); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>