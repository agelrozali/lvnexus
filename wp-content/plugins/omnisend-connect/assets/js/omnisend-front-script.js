/*
omnisend_woo_data - object is created by WP, check following properties bellow
ajax_url
 */
var omnisend_email_submitted          = '';
var omnisend_email_submit_in_progress = false;

window.addEventListener(
	'load',
	function () {
		var emailInput = document.querySelector( '.wc-block-components-text-input > #email' );
		if (emailInput) {
			emailInput.addEventListener(
				'focus',
				function () {
					omnisend_handle_email_change( '.wc-block-components-text-input > #email' )
				}
			)
			emailInput.addEventListener(
				'change',
				function () {
					omnisend_handle_email_change( '.wc-block-components-text-input > #email' )
				}
			)
		}
	}
);

jQuery( document ).ready(
	function () {
		['#billing_email', '#username', '#reg_email'].forEach(
			function (selector) {
				var emailInput = document.querySelector( selector );
				if (emailInput) {
					emailInput.addEventListener(
						'focus',
						function () {
							omnisend_handle_email_change( selector )
						}
					)
					emailInput.addEventListener(
						'change',
						function () {
							omnisend_handle_email_change( selector )
						}
					)
				}
			}
		)
	}
);

function omnisend_handle_email_change(selector) {
	if (omnisend_email_submit_in_progress) {
		return;
	}

	omnisend_email_submit_in_progress = true;

	var email = document.querySelector( selector ).value;

	var validEmail = function (email) {
		return /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(email)
	}

	if ( ! validEmail( email )) {
		omnisend_email_submit_in_progress = false;
		return;
	}

	if (omnisend_email_submitted === email) {
		omnisend_email_submit_in_progress = false;
		return;
	}

	var urlParams = new URLSearchParams({
		action: 'omnisend_identify',
		email: email,
		_wpnonce: omnisend_woo_data.nonce
	})

	if (omnisend_woo_data.contact_id) {
		urlParams.set('contact_id', omnisend_woo_data.contact_id)
	}

	var url = omnisend_woo_data.ajax_url + "?" + urlParams.toString();

	fetch(url, {
		method: 'POST',
		headers: {
			Accept: 'application/json'
		}
	})
		.then(function (response) {
			var successful = response.status >= 200 && response.status < 400;
			if (successful) {
				omnisend_email_submitted = email;
			}
			omnisend_email_submit_in_progress = false;
		})
		.catch(function () {
			omnisend_email_submit_in_progress = false;
		})
}
