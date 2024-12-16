<?php
/**
 * Omnisend Tag Settings View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_tag_settings() {
	$contact_tag         = Omnisend_Settings::get_contact_tag();
	$contact_tag_enabled = Omnisend_Settings::get_contact_tag_status() === Omnisend_Settings::STATUS_ENABLED;

	?>
	<div class="settings-section">
		<h3 class="omnisend-content-lead strong setting-title">Tag imported contacts</h3>
		<div class="setting-control-container">
			<input id="ajax__contact_tag_status" type="checkbox" class="omnisend-checkbox setting-checkbox" <?php echo $contact_tag_enabled ? 'checked' : ''; ?> />
			<div>
				<label for="ajax__contact_tag_status">
					<span class="omnisend-content-body strong">Assign a tag to contacts that you import to Omnisend</span><br/>
				</label>
				<div class="omnisend-content-body">
					Tags can be used to structure your contracts and store essential pieces of data
				</div>
				<div id="contact_tag_input_container" class="setting-input-container">
					<div class="omnisend-content-body strong">Tag name</div>
					<div class="setting-input-wrapper">
						<input id="ajax__contact_tag" class="omnisend-input" type="text" maxlength="250" value="<?php echo esc_attr( $contact_tag ); ?>">
						<button id="ajax__contact_tag_submit" type="submit" class="omnisend-primary-button">Save tag</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		(function() {
			var tagStatusCheckbox = document.getElementById("ajax__contact_tag_status");
			var tagInputContainer = document.getElementById("contact_tag_input_container");

			tagInputContainer.style.display = tagStatusCheckbox.checked ? "block" : "none";

			tagStatusCheckbox.addEventListener("change", function(event) {
				tagInputContainer.style.display = event.target.checked ? "block" : "none";
			});

			var tagTextInput = document.getElementById("ajax__contact_tag");
			var tagTextSubmitBtn = document.getElementById("ajax__contact_tag_submit");

			tagTextSubmitBtn.disabled = tagTextInput.value.trim() === "";

			tagTextInput.addEventListener("input", function(event) {
				tagTextSubmitBtn.disabled = event.target.value.trim() === "";
			});
		})()
	</script>
	<?php
}
