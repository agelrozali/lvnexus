jQuery(document).ready(function ($) {
    $(document).on({
        ajaxStart: function () {
            $('body').addClass('omnisend-ajax-loading');
        },
        ajaxStop: function () {
            $('body').removeClass('omnisend-ajax-loading');
        }
    });

    handleClick('#ajax__checkout_opt_in_status', function (e) {
        post(
            {
                action: 'omnisend_update_plugin_setting',
                setting_name: 'checkout_opt_in_status',
                setting_value: e.target.checked ? 'enabled' : 'disabled'
            },
            e.target
        );
    });

    handleClick('#ajax__checkout_opt_in_text_submit', function (e) {
        post(
            {
                action: 'omnisend_update_plugin_setting',
                setting_name: 'checkout_opt_in_text',
                setting_value: document.querySelector('#ajax__checkout_opt_in_text').value
            },
            e.target
        );
    });

    handleClick('#ajax__checkout_opt_in_preselected_status', function (e) {
        post(
            {
                action: 'omnisend_update_plugin_setting',
                setting_name: 'checkout_opt_in_preselected_status',
                setting_value: e.target.checked ? 'enabled' : 'disabled'
            },
            e.target
        );
    });

    handleClick('#ajax__contact_tag_status', function (e) {
        post(
            {
                action: 'omnisend_update_plugin_setting',
                setting_name: 'contact_tag_status',
                setting_value: e.target.checked ? 'enabled' : 'disabled'
            },
            e.target
        );
    });

    handleClick('#ajax__contact_tag_submit', function (e) {
        post(
            {
                action: 'omnisend_update_plugin_setting',
                setting_name: 'contact_tag',
                setting_value: document.querySelector('#ajax__contact_tag').value
            },
            e.target
        );
    });

    function post(data, element) {
        element?.setAttribute('disabled', 'disabled');

        return $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: { ...data, _wpnonce: omnisend_settings_script_var.nonce }
        }).always(() => {
            element?.removeAttribute('disabled');
        });
    }

    function handleClick(selector, handler) {
        const element = document.querySelector(selector);

        if (!element) {
            return;
        }

        element.addEventListener('click', handler);
    }
});
