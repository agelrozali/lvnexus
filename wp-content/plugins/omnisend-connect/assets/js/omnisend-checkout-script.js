(function () {
	var lastSentEmail = ''

	window.addEventListener(
		'load',
		function () {
			var inputElement = document.querySelector( 'input[name="billing_email"], #billing_email, .wc-block-components-text-input > #email');
			var email = inputElement && extractEmailValue( inputElement )
			if (email || omnisend_checkout_vars.contact_id) {
				trackEvent(email);
			}

			if (inputElement) {
				inputElement.addEventListener( 'change', inputListener );
			}
		}
	);

	function getUrl(email = '') {
		const params = new URLSearchParams({
			action: 'omnisend_track_started_checkout_event',
			email: email,
			_wpnonce: omnisend_checkout_vars.nonce
		})

		return omnisend_checkout_vars.ajax_url + '?' + params.toString()
	}

	function trackEvent(email) {
		if (lastSentEmail && lastSentEmail === email) {
			return;
		}

		lastSentEmail = email;

		return fetch( getUrl( email ) );
	}

	function extractEmailValue(inputElement) {
		if (inputElement.checkValidity && inputElement.checkValidity()) {
			return inputElement.value.trim();
		} else {
			return '';
		}
	}

	function inputListener(event) {
		var email = extractEmailValue( event.target );
		if (email) {
			trackEvent( email )
		}
	}
})();
