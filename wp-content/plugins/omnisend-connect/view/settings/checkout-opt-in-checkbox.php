<?php
/**
 * Omnisend Checkout Opt In Checkbox Settings View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_checkout_opt_in_checkbox_settings() {
	$checkout_opt_in_enabled     = Omnisend_Settings::get_checkout_opt_in_status() === Omnisend_Settings::STATUS_ENABLED;
	$checkout_opt_in_text        = Omnisend_Settings::get_checkout_opt_in_text();
	$checkout_opt_in_preselected = Omnisend_Settings::get_checkout_opt_in_preselected_status() === Omnisend_Settings::STATUS_ENABLED;

	?>
	<div class="settings-section">
		<h3 class="omnisend-content-lead strong setting-title">Collect email subscribers at checkout</h3>
		<div class="setting-control-container">
			<input id="ajax__checkout_opt_in_status" type="checkbox" class="omnisend-checkbox setting-checkbox" <?php echo $checkout_opt_in_enabled ? 'checked' : ''; ?> />
			<div>
				<label for="ajax__checkout_opt_in_status">
					<span class="omnisend-content-body strong">Add an opt-in checkbox to the checkout page</span>
				</label>
				<div class="omnisend-content-body">
					Customers who consent will be imported to your Omnisend account as email subscribers.
				</div>
				<div id="checkout_opt_in_text_input_container" class="setting-input-container">
					<div class="omnisend-content-body strong">Opt-in checkbox consent text</div>
					<div class="setting-input-wrapper">
						<input id="ajax__checkout_opt_in_text" class="omnisend-input" type="text" maxlength="250" value="<?php echo esc_attr( $checkout_opt_in_text ); ?>" />
						<button id="ajax__checkout_opt_in_text_submit" type="submit" class="omnisend-primary-button">
							Save consent text
						</button>
					</div>
					<div class="setting-control-container inner-control">
						<input id="ajax__checkout_opt_in_preselected_status" type="checkbox" class="omnisend-checkbox setting-checkbox" <?php echo $checkout_opt_in_preselected ? 'checked' : ''; ?> />
						<div>
							<label for="ajax__checkout_opt_in_preselected_status">
								<span class="omnisend-content-body strong">Preselect opt-in checkbox in the checkout page</span>
							</label>
							<div class="omnisend-content-body">
								Customers can deselect if they don’t want email marketing.
							</div>
						</div>
					</div>
				</div>
				<!-- TODO: add kb link -->
				<a target="_blank" href="https://support.omnisend.com/en/articles/1636174-omnisend-for-woocommerce-wordpress#h_62f5d74f6f" class="omnisend-kb-link setting-kb-link">
					Learn more about opt-in settings
					<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/kb-icon.svg'; ?>">
				</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		(function() {
			var checkoutOptInStatusCheckbox = document.getElementById("ajax__checkout_opt_in_status");
			var checkoutOptInTextInputContainer = document.getElementById("checkout_opt_in_text_input_container");

			checkoutOptInTextInputContainer.style.display = checkoutOptInStatusCheckbox.checked ? "block" : "none";

			checkoutOptInStatusCheckbox.addEventListener("change", function(event) {
				checkoutOptInTextInputContainer.style.display = event.target.checked ? "block" : "none";
			});

			var checkoutOptInTextInput = document.getElementById("ajax__checkout_opt_in_text");
			var checkoutOptInTextSubmitBtn = document.getElementById("ajax__checkout_opt_in_text_submit");

			checkoutOptInTextSubmitBtn.disabled = checkoutOptInTextInput.value.trim() === "";

			checkoutOptInTextInput.addEventListener("input", function(event) {
				checkoutOptInTextSubmitBtn.disabled = event.target.value.trim() === "";
			});
		})()
	</script>
	<?php
}
