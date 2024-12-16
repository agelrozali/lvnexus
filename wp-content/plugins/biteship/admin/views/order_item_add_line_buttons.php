<button type="button" class="button add-biteship"><?php esc_html_e("Add Biteship", "biteship"); ?></button>

<script type="text/template" id="tmpl-biteship-modal-add-shipping">
	<div class="wc-backbone-modal">
		<div class="wc-backbone-modal-content">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1><?php esc_html_e("Add Biteship Shipping", "biteship"); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text">Close modal panel</span>
					</button>
				</header>
				<article>
					<p id="shippingListLoading"></p>
					<form action="" method="post">
					  <ul id="shippingList">
						</ul>
					</form>
				</article>
				<footer>
					<div class="inner">
						<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e("Add", "woocommerce"); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>