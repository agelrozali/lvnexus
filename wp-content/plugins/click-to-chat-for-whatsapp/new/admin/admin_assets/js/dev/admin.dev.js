// Click to Chat
document.addEventListener('DOMContentLoaded', function () {
    // md
    try {
        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems, {});
        var elems = document.querySelectorAll('.collapsible');
        M.Collapsible.init(elems, {});
        var elems = document.querySelectorAll('.modal');
        M.Modal.init(elems, {});
        var elems = document.querySelectorAll('.tooltipped');
        M.Tooltip.init(elems, {});
    } catch (e) {
        console.log(e);
    }
});

(function ($) {

    // ready
    $(function () {

        var admin_ctc = {};
        try {
            document.dispatchEvent(
                new CustomEvent("ht_ctc_fn_all", { detail: { admin_ctc, ctc_getItem, ctc_setItem, intl_init, intl_onchange } })
            );
        } catch (e) {
            console.log(e);
            console.log('cache: ht_ctc_fn_all custom event');
        }

        // local storage - admin
        var ht_ctc_admin = {};

        var ht_ctc_admin_var = (window.ht_ctc_admin_var) ? window.ht_ctc_admin_var : {};
        console.log(ht_ctc_admin_var);

        if (localStorage.getItem('ht_ctc_admin')) {
            ht_ctc_admin = localStorage.getItem('ht_ctc_admin');
            ht_ctc_admin = JSON.parse(ht_ctc_admin);
        }

        // get items from ht_ctc_admin
        function ctc_getItem(item) {
            if (ht_ctc_admin[item]) {
                return ht_ctc_admin[item];
            } else {
                return false;
            }
        }

        // set items to ht_ctc_admin storage
        function ctc_setItem(name, value) {
            ht_ctc_admin[name] = value;
            var newValues = JSON.stringify(ht_ctc_admin);
            localStorage.setItem('ht_ctc_admin', newValues);
        }


        /**
         * ht_ctc_storage - public
         * to update public side - localStorage for admins to see the changes.
         */
        var ht_ctc_storage = {};

        if (localStorage.getItem('ht_ctc_storage')) {
            ht_ctc_storage = localStorage.getItem('ht_ctc_storage');
            ht_ctc_storage = JSON.parse(ht_ctc_storage);
        }

        // get items from ht_ctc_storage
        function ctc_front_getItem(item) {
            if (ht_ctc_storage[item]) {
                return ht_ctc_storage[item];
            } else {
                return false;
            }
        }

        // set items to ht_ctc_storage storage
        function ctc_front_setItem(name, value) {
            ht_ctc_storage[name] = value;
            var newValues = JSON.stringify(ht_ctc_storage);
            localStorage.setItem('ht_ctc_storage', newValues);
        }

        // md
        try {
            $('select').formSelect();
            $('.collapsible').collapsible();
            $('.modal').modal();
            $('.tooltipped').tooltip();
        } catch (e) {
            console.log(e);
        }

        // md tabs
        try {

            $(document).on('click', '.open_tab', function () {
                var tab = $(this).attr('data-tab');
                $('.tabs').tabs('select', tab);
                ctc_setItem('woo_tab', '#' + tab);
            });

            $(document).on('click', '.md_tab_li', function () {
                var href = $(this).children('a').attr('href');
                window.location.hash = href;
                ctc_setItem('woo_tab', href);
            });

            $(".tabs").tabs();

            // only on woo page.. 
            if (document.querySelector('.ctc-admin-woo-page') && ctc_getItem('woo_tab')) {

                var woo_tab = ctc_getItem('woo_tab');

                // setTimeout(() => {
                //     $(".tabs").tabs('select', woo_tab);
                // }, 2500);

                woo_tab = woo_tab.replace('#', '');
                setTimeout(() => {
                    $("[data-tab=" + woo_tab + "]").trigger('click');
                }, 1200);
            }

        } catch (e) {
            console.log(e);
            console.log('cache: md tabs');
        }

        // intl
        try {
            // @parm: class name
            intl_input('intl_number');
            $('.intl_error').remove();
        } catch (e) {
            console.log(e);
            console.log('cache: intl_input');
            $('.greetings_links').hide();
            $('.intl_error').show();
        }


        // wpColorPicker
        // http://automattic.github.io/Iris/#change
        var color_picker = {
            palettes: [
                '#000000',
                '#FFFFFF',
                '#075e54',
                '#128C7E',
                '#25d366',
                '#DCF8C6',
                '#34B7F1',
                '#ECE5DD',
                '#00a884',
            ],
            change: function (event, ui) {
                try {
                    var element = event.target;
                    console.log(element);

                    var color = ui.color.toString();
                    console.log(color);

                    // check if element have data-update attribute
                    var update_type = $(element).attr('data-update-type'); // color, background-color, border-color, ..
                    console.log(update_type);

                    var update_class = $(element).attr('data-update-selector'); // the other filed to update
                    console.log(update_class);

                    if (update_type && update_class) {
                        console.log('update');
                        $(update_class).css(update_type, color);

                        // if data-update-2-type and data-update-2-selector exists
                        if ($(element).attr('data-update-2-type') && $(element).attr('data-update-2-selector')) {
                            console.log('update-2-type');
                            $($(element).attr('data-update-2-selector')).css($(element).attr('data-update-2-type'), color);
                        }

                    }
                } catch (e) {
                    console.log(e);
                    console.log('cache: wpColorPicker on change');
                }
            }
        }
        try {
            $('.ht-ctc-color').wpColorPicker(color_picker);
            console.log('wpColorPicker passed args');
        } catch (e) {
            $('.ht-ctc-color').wpColorPicker();
            console.log('wpColorPicker default');
        }

        // functions
        show_hide_options();
        styles();
        call_to_action();
        ht_ctc_admin_animations();
        desktop_mobile();
        notification_badge();
        wn();
        hook();
        ss();
        other();

        try {
            woo_page();
            collapsible();
            update_fronend_storage();
            analytics();
        } catch (e) {
            console.log(e);
            console.log('cache: woo_page(), collapsible(), update_fronend_storage()');
        }

        // jquery ui
        try {
            $(".ctc_sortable").sortable({
                cursor: "move",
                handle: '.handle'
            });
        } catch (e) {
            console.log(e);
            console.log('cache: jquery ui - sortable');
        }


        // show/hide settings
        function show_hide_options() {

            // default display
            var val = $('.global_display:checked').val();

            if (val == 'show') {
                $('.global_show_or_hide_icon').addClass('dashicons dashicons-visibility');
                $(".hide_settings").show();
                $(".show_hide_types .show_btn").attr('disabled', 'disabled');
                $(".show_hide_types .show_box").hide();
            } else if (val == 'hide') {
                $('.global_show_or_hide_icon').addClass('dashicons dashicons-hidden');
                $(".show_settings").show();
                $(".show_hide_types .hide_btn").attr('disabled', 'disabled');
                $(".show_hide_types .hide_box").hide();
            }
            $('.global_show_or_hide_label').html('(' + val + ')');

            // on change
            $(".global_display").on("change", function (e) {

                var change_val = e.target.value;
                var add_class = '';
                var remove_class = '';

                $(".hide_settings").hide();
                $(".show_settings").hide();
                $(".show_hide_types .show_btn").removeAttr('disabled');
                $(".show_hide_types .hide_btn").removeAttr('disabled');
                $(".show_hide_types .show_box").hide();
                $(".show_hide_types .hide_box").hide();

                if (change_val == 'show') {
                    add_class = 'dashicons dashicons-visibility';
                    remove_class = 'dashicons-hidden';
                    $(".hide_settings").show(500);
                    $(".show_hide_types .show_btn").attr('disabled', 'disabled');
                    $(".show_hide_types .hide_box").show();
                } else if (change_val == 'hide') {
                    add_class = 'dashicons dashicons-hidden';
                    remove_class = 'dashicons-visibility';
                    $(".show_settings").show(500);
                    $(".show_hide_types .hide_btn").attr('disabled', 'disabled');
                    $(".show_hide_types .show_box").show();
                }
                $('.global_show_or_hide_label').html('(' + change_val + ')');
                $('.global_show_or_hide_icon').removeClass(remove_class);
                $('.global_show_or_hide_icon').addClass(add_class);

            });

        }


        // styles
        function styles() {


            // get data-style attribute from select_style_container and add class to select_style_item as selected
            var style = $('.select_style_container').attr('data-style');
            console.log(style);
            if (style) {
                $('.select_style_item[data-style="' + style + '"]').addClass('select_style_selected');
            }

            // on click select style item
            $(".select_style_item").on("click", function (e) {

                // select effects
                $(".select_style_item").removeClass('select_style_selected');
                $(this).addClass('select_style_selected');

                // update chat_select_style value
                var style = $(this).attr('data-style');
                console.log(style);
                $(".select_style_desktop").val(style);

                $(".customize_styles_link").fadeOut(100).fadeIn(100);

            });


            // get data-style attribute from select_style_container and add class to select_style_item as selected
            var style = $('.m_select_style_container').attr('data-style');
            console.log(style);
            if (style) {
                $('.m_select_style_item[data-style="' + style + '"]').addClass('select_style_selected');
            }

            // on click select style item
            $(".m_select_style_item").on("click", function (e) {

                // select effects
                $(".m_select_style_item").removeClass('select_style_selected');
                $(this).addClass('select_style_selected');

                // update chat_select_style value
                var style = $(this).attr('data-style');
                console.log(style);
                $(".select_style_mobile").val(style);
            });

            // If Styles for desktop, mobile not selected as expected
            if ($('#select_styles_issue').is(':checked') && !$('.same_settings').is(':checked') ) {
                $(".select_styles_issue_checkbox").show();
            }
            $('.select_styles_issue_description').on('click', function (e) {
                $('.select_styles_issue_checkbox').toggle(500);
            });


            // customize styles page: 
            
            // dispaly all style - ask to save changes on change
            $("#display_allstyles").on("change", function (e) {
                $(".display_allstyles_description").show(200);
            });

            // style-1 - add icon
            if ($('.s1_add_icon').is(':checked')) {
                $(".s1_icon_settings").show();
            } else {
                $(".s1_icon_settings").hide();
            }

            $(".s1_add_icon").on("change", function (e) {
                if ($('.s1_add_icon').is(':checked')) {
                    $(".s1_icon_settings").show(200);
                } else {
                    $(".s1_icon_settings").hide(200);
                }
            });
            

        }


        // call to actions
        function call_to_action() {
            var cta_styles = ['.ht_ctc_s2', '.ht_ctc_s3', '.ht_ctc_s3_1', '.ht_ctc_s7'];
            cta_styles.forEach(ht_ctc_admin_cta);

            function ht_ctc_admin_cta(style) {
                // default display
                var val = $(style + ' .select_cta_type').find(":selected").val();
                if (val == 'hide') {
                    $(style + " .cta_stick").hide();
                }

                // on change
                $(style + " .select_cta_type").on("change", function (e) {
                    var change_val = e.target.value;
                    if (change_val == 'hide') {
                        $(style + " .cta_stick").hide(100);
                    } else {
                        $(style + " .cta_stick").show(200);
                    }
                });
            }

        }



        function ht_ctc_admin_animations() {
            // default display
            var val = $('.select_an_type').find(":selected").val();
            if (val == 'no-animation') {
                $(".an_delay").hide();
                $(".an_itr").hide();
            }

            // on change
            $(".select_an_type").on("change", function (e) {

                var change_val = e.target.value;

                if (change_val == 'no-animation') {
                    $(".an_delay").hide();
                    $(".an_itr").hide();
                } else {
                    $(".an_delay").show(500);
                    $(".an_itr").show(500);
                }
            });
        }


        // Deskop, Mobile - same settings
        function desktop_mobile() {

            // same setting
            if ($('.same_settings').is(':checked')) {
                $(".not_samesettings").hide();
            } else {
                $(".not_samesettings").show();
            }

            $(".same_settings").on("change", function (e) {

                if ($('.same_settings').is(':checked')) {
                    $(".not_samesettings").hide(900);
                    $(".select_styles_issue_checkbox").hide();
                } else {
                    $(".not_samesettings").show(900);
                }

            });

        }

        function notification_badge() {
            // same setting
            if ($('#notification_badge').is(':checked')) {
                $(".notification_settings ").show();
            } else {
                $(".notification_settings ").hide();
            }

            $("#notification_badge").on("change", function (e) {

                if ($('#notification_badge').is(':checked')) {
                    $(".notification_settings ").show(400);
                } else {
                    $(".notification_settings ").hide(400);
                }

            });
        }


        // WhatsApp number  
        function wn() {

            var cc = $("#whatsapp_cc").val();
            var num = $("#whatsapp_number").val();

            $("#whatsapp_cc").on("change paste keyup", function (e) {
                cc = $("#whatsapp_cc").val();
                call();
            });

            $("#whatsapp_number").on("change paste keyup", function (e) {
                num = $("#whatsapp_number").val();
                call();

                if (num && 0 == num.charAt(0)) {
                    $('.ctc_wn_initial_zero').show(500);
                } else {
                    $('.ctc_wn_initial_zero').hide(500);
                }
            });

            function call() {
                $(".ht_ctc_wn").html(cc + '' + num);
                $("#ctc_whatsapp_number").val(cc + '' + num);
            }

        }


        // woo page..
        function woo_page() {

            //  Woo single product page - woo position
            var position_val = $('.woo_single_position_select').find(":selected").val();
            // woo add to cart layout
            var style_val = $('.woo_single_style_select').find(":selected").val();

            if (position_val && '' !== position_val && 'select' !== position_val) {
                $('.woo_single_position_settings').show();
            }
            if (position_val && 'select' == position_val) {
                hide_cart_layout();
            } else if (style_val && style_val == '1' || style_val == '8') {
                // if position_val is not 'select'
                show_cart_layout();
            }

            // on change - select position
            $('.woo_single_position_select').on("change", function (e) {
                var position_change_val = e.target.value;
                var style_val = $('.woo_single_style_select').find(":selected").val();

                if (position_change_val == 'select') {
                    $('.woo_single_position_settings').hide(200);
                    hide_cart_layout();
                } else {
                    $('.woo_single_position_settings').show(200);
                    if (style_val == '1' || style_val == '8') {
                        show_cart_layout();
                    }
                }
            });

            // on change - style - for cart layout
            $('.woo_single_style_select').on("change", function (e) {
                var style_change_val = e.target.value;

                if (style_change_val == '1' || style_change_val == '8') {
                    show_cart_layout();
                } else {
                    hide_cart_layout();
                }
            });

            // position center is checked
            if ($('#woo_single_position_center').is(':checked')) {
                $(".woo_single_position_center_checked_content").show();
            }

            $("#woo_single_position_center").on("change", function (e) {
                if ($('#woo_single_position_center').is(':checked')) {
                    $(".woo_single_position_center_checked_content").show(200);
                } else {
                    $(".woo_single_position_center_checked_content").hide(100);
                }
            });



            // woo shop page .. 
            if ($('#woo_shop_add_whatsapp').is(':checked')) {
                $(".woo_shop_add_whatsapp_settings").show();

                var shop_style_val = $('.woo_shop_style').find(":selected").val();
                if (shop_style_val == '1' || shop_style_val == '8') {
                    shop_show_cart_layout();
                }
            }




            $("#woo_shop_add_whatsapp").on("change", function (e) {
                if ($('#woo_shop_add_whatsapp').is(':checked')) {
                    $(".woo_shop_add_whatsapp_settings").show(200);

                    var shop_style_val = $('.woo_shop_style').find(":selected").val();

                    if (shop_style_val == '1' || shop_style_val == '8') {
                        shop_show_cart_layout();
                    }

                } else {
                    $(".woo_shop_add_whatsapp_settings").hide(100);
                    shop_hide_cart_layout(100);
                }
            });


            // on change - style - for cart layout
            $('.woo_shop_style').on("change", function (e) {
                var shop_style_change_val = e.target.value;

                if (shop_style_change_val == '1' || shop_style_change_val == '8') {
                    shop_show_cart_layout();
                } else {
                    shop_hide_cart_layout();
                }
            });


            function show_cart_layout() {
                $(".woo_single_position_settings_cart_layout").show(200);
            }
            function hide_cart_layout() {
                $(".woo_single_position_settings_cart_layout").hide(200);
            }

            function shop_show_cart_layout() {
                $(".woo_shop_cart_layout").show(200);
            }
            function shop_hide_cart_layout() {
                $(".woo_shop_cart_layout").hide(200);
            }

        }


        // webhook
        function hook() {

            // webhook value - html 
            var hook_value_html = $('.add_hook_value').attr('data-html');

            // add value
            $(document).on('click', '.add_hook_value', function () {

                $('.ctc_hook_value').append(hook_value_html);
            });

            // Remove value
            $('.ctc_hook_value').on('click', '.hook_remove_value', function (e) {
                e.preventDefault();
                $(this).closest('.additional-value').remove();
            });

        }


        // things based on screen size
        function ss() {

            var is_mobile = (typeof screen.width !== "undefined" && screen.width > 1024) ? "no" : "yes";

            if ('yes' == is_mobile) {

                // WhatsApp number tooltip position for mobile
                // $("#whatsapp_cc").data('position', 'bottom');
                $("#whatsapp_cc").attr('data-position', 'bottom');
                $("#whatsapp_number").attr('data-position', 'bottom');
            }
        }


        function other() {

            // google ads - checkbox
            $('.ga_ads_display').on('click', function (e) {
                $('.ga_ads_checkbox').toggle(500);
            });

            // // display - call gtag_report_conversion by default if checked.
            // if ($('#ga_ads').is(':checked')) {
            //     $(".ga_ads_checkbox").show();
            // }

            // hover text on save_changes button
            var text = $('#ctc_save_changes_hover_text').text();
            $("#submit").attr('title', text);

            
            // s3e - shadow on hover
            if (!$('#s3_box_shadow').is(':checked')) {
                $(".s3_box_shadow_hover").show();
            }

            $('#s3_box_shadow').on('change', function (e) {
                if ($('#s3_box_shadow').is(':checked')) {
                    $(".s3_box_shadow_hover").hide(400);
                } else {
                    $(".s3_box_shadow_hover").show(500);
                }
            });

        }

        // collapsible..
        function collapsible() {



            /**
             * ht_ctc_sidebar_contat, .. - not added, as it may cause view distraction..
             */
            var collapsible_list = [
                'ht_ctc_s1',
                'ht_ctc_s2',
                'ht_ctc_s3',
                'ht_ctc_s3_1',
                'ht_ctc_s4',
                'ht_ctc_s5',
                'ht_ctc_s6',
                'ht_ctc_s7',
                'ht_ctc_s7_1',
                'ht_ctc_s8',
                'ht_ctc_s99',
                'ht_ctc_webhooks',
                // 'ht_ctc_analytics',
                'ht_ctc_animations',
                'ht_ctc_notification',
                'ht_ctc_other_settings',
                'ht_ctc_enable_share_group',
                'ht_ctc_debug',
                'ht_ctc_device_settings',
                'ht_ctc_show_hide_settings',
                'ht_ctc_woo_1',
                'ht_ctc_woo_shop',
                'ctc_g_opt_in',
                'g_content_collapsible',
                'url_structure',
                'ht_ctc_custom_css'
            ];

            // dynamically add to collapsible_list
            if (document.querySelector('.coll_active')) {
                $('.coll_active').each(function () {
                    collapsible_list.push($(this).attr('data-coll_active'));
                });
            }


            var default_active = [
                'ht_ctc_device_settings',
                'ht_ctc_show_hide_settings',
                'ht_ctc_woo_1',
                'ht_ctc_webhooks',
                // 'ht_ctc_analytics',
                'ht_ctc_animations',
                'ht_ctc_notification',
                'g_content_collapsible',
                'url_structure',
            ];


            collapsible_list.forEach(e => {

                // one known issue.. is already active its not working as expected. 
                var is_col = (ctc_getItem('col_' + e)) ? ctc_getItem('col_' + e) : '';
                if ('open' == is_col) {
                    $('.' + e + ' li').addClass('active');
                } else if ('close' == is_col) {
                    $('.' + e + ' li').removeClass('active');
                } else if (default_active.includes(e)) {
                    // if not changed then for default_active list add active..
                    $('.' + e + ' li').addClass('active');
                }


                $('.' + e).collapsible({
                    onOpenEnd() {
                        console.log(e + ' open');
                        ctc_setItem('col_' + e, 'open');
                    },
                    onCloseEnd() {
                        console.log(e + ' close');
                        ctc_setItem('col_' + e, 'close');
                    }
                });

            });

        }

        /**
         * intl tel input 
         * intlTelInput - from intl js.. 
         * 
         * class name - intl_number, multi agent class names
         */
        function intl_input(className) {

            console.log('intl_input() className: ' + className);

            if (document.querySelector("." + className)) {
                console.log(className + ' class name exists');

                if (typeof intlTelInput !== 'undefined') {
                    
                    $('.' + className).each(function () {
                        console.log('each: calling intl_init()..' + this);
                        var i = intl_init(this);
                    });

                    console.log('calling intl_onchange() from intl_input()');
                    intl_onchange();
                } else {
                    // throw error..
                    console.log('intlTelInput not loaded..');
                    throw new Error('intlTelInput not loaded..');
                }
                
            }

        }

        // intl: - init
        function intl_init(v) {

            console.log('intl_init()');

            var hidden_input = $(v).attr("data-name") ? $(v).attr("data-name") : 'ht_ctc_chat_options[number]';
            console.log(hidden_input);

            $(v).removeAttr('name');
            var pre_countries = [];
            var country_code_date = new Date().toDateString();
            var country_code = (ctc_getItem('country_code_date') == country_code_date) ? ctc_getItem('country_code') : '';
            console.log('country_code: ' + country_code);

            if ('' == country_code) {
                console.log('getting country code..');
                // fall back..
                country_code = 'us';
                
                $.get("https://ipinfo.io", function () { }, "jsonp").always(function (resp) {
                    country_code = (resp && resp.country) ? resp.country : "us";
                    ctc_setItem('country_code', country_code);
                    ctc_setItem('country_code_date', country_code_date);
                    add_prefer_countrys(country_code);
                    call_intl();
                });
            } else {
                call_intl();
            }

            var intl = '';
            function call_intl() {
                pre_countries = (ctc_getItem('pre_countries')) ? ctc_getItem('pre_countries') : [];
                console.log(pre_countries);

                var values = {
                    autoHideDialCode: false,
                    initialCountry: "auto",
                    geoIpLookup: function (success, failure) {
                        success(country_code);
                    },
                    dropdownContainer: document.body,
                    hiddenInput: function () {
                        return { phone: hidden_input, country: 'ht_ctc_chat_options[intl_country]' };
                    },
                    nationalMode: false,
                    // autoPlaceholder: "polite",
                    countryOrder: pre_countries,
                    separateDialCode: true,
                    containerClass: 'intl_tel_input_container',

                    utilsScript: ht_ctc_admin_var.utils
                };

                intl = intlTelInput(v, values );
            }

            return intl;
        }
        

        // intl: on change
        function intl_onchange() {

            console.log('intl_onchange()');

            $('.intl_number').on("input countrychange", function (e) {
                // if blank also it may triggers.. as if countrycode changes.
                console.log('on change - intl_number - input, countrychange');
                console.log(this);
                console.log(intlTelInput);

                // var changed = intlTelInputGlobals.getInstance(this);
                // var changed = window.intlTelInput.getInstance(this);
                // var changed = intlTelInput(this);
                var changed = intlTelInput.getInstance(this);

                console.log(changed);
                console.log(changed.getNumber());

                // add value to next sibbling hidden input field.
                $(this).next('input[type="hidden"]').val(changed.getNumber());


                if (window.ht_ctc_admin_demo_var) {
                    console.log('for demo: update number');
                    window.ht_ctc_admin_demo_var.number = changed.getNumber();
                    console.log(window.ht_ctc_admin_demo_var);
                }

                if (changed.isValidNumber()) {
                    // to display in format
                    console.log('valid number');

                    // issue here.. setNumber ~ uses for for formating..
                    // console.log(changed.getNumber());

                    var d = {
                        number: changed.getNumber()
                    };

                    // @used at admin demo
                    document.dispatchEvent(
                        new CustomEvent("ht_ctc_admin_event_valid_number", { detail: { d } })
                    );

                }
            });

            // intl: only countrycode changes.
            $('.intl_number').on("countrychange", function (e) {

                console.log('on change - intl_number - countrychange');

                // var changed = intlTelInputGlobals.getInstance(this);
                // var changed = window.intlTelInput.getInstance(this);
                // var changed = window.intlTelInput(this);
                var changed = intlTelInput.getInstance(this);

                console.log(changed);

                console.log(changed.getSelectedCountryData().iso2);
                console.log('calling add_prefer_countrys()');
                add_prefer_countrys(changed.getSelectedCountryData().iso2);
            });

        }

        function add_prefer_countrys(country_code) {

            console.log('add_prefer_countrys(): ' + country_code);

            country_code = (country_code && '' !== country_code) ? country_code.toUpperCase() : 'US';

            var pre_countries = (ctc_getItem('pre_countries')) ? ctc_getItem('pre_countries') : [];
            console.log(pre_countries);

            if (!pre_countries.includes(country_code)) {
                console.log(country_code + ' not included. so pushing country code to pre countries');
                
                // push to index 0..
                pre_countries.unshift(country_code);
                // pre_countries.push(country_code);

                ctc_setItem('pre_countries', pre_countries);
            }
            console.log('#END add_prefer_countrys()');
        }


        /**
         * on save changes clear stuff - local storage: front. 
         *  for better user interface - while testing, admin side.. 
         *      for notification badge
         * as now for colors not added on change..
         */
        function update_fronend_storage() {

            $('.notification_field').on("change", function (e) {
                console.log('notifications updated..');
                ctc_front_setItem('n_badge', 'admin_start');
            });


        }



        /**
         * Analytics.. 
         */
        function analytics() {

            console.log('analytics()');

            // google analytics

            // if #google_analytics is checked then display .ctc_ga_values
            if ($('#google_analytics').is(':checked')) {
                $(".ctc_ga_values").show();
            }

            // event name, params - display only if ga is enabled.
            $("#google_analytics").on("change", function (e) {
                if ($('#google_analytics').is(':checked')) {
                    $(".ctc_ga_values").show(400);
                } else {
                    $(".ctc_ga_values").hide(200);
                }
            });


            var g_an_param_snippet = $('.ctc_g_an_param_snippets .ht_ctc_g_an_add_param');
            console.log(g_an_param_snippet);

            // add value
            $(document).on('click', '.ctc_add_g_an_param_button', function () {

                console.log('on click: add g an param button');
                console.log(g_an_param_snippet);

                var g_an_param_order = $('.g_an_param_order').val();
                g_an_param_order = parseInt(g_an_param_order);


                var g_an_param_clone = g_an_param_snippet.clone();
                console.log(g_an_param_clone);

                // filed number for reference
                $(g_an_param_clone).find('.g_an_param_order_ref_number').attr('name', `ht_ctc_othersettings[g_an_params][]`);
                $(g_an_param_clone).find('.g_an_param_order_ref_number').val('g_an_param_' + g_an_param_order);

                $(g_an_param_clone).find('.ht_ctc_g_an_add_param_key').attr('name', `ht_ctc_othersettings[g_an_param_${g_an_param_order}][key]`);
                $(g_an_param_clone).find('.ht_ctc_g_an_add_param_value').attr('name', `ht_ctc_othersettings[g_an_param_${g_an_param_order}][value]`);


                console.log($('.ctc_new_g_an_param'));

                $('.ctc_new_g_an_param').append(g_an_param_clone);


                g_an_param_order++;
                $('.g_an_param_order').val(g_an_param_order);
            });

            



            // fb pixel

            // if #fb_pixel is checked then display .ctc_pixel_values
            if ($('#fb_pixel').is(':checked')) {
                $(".ctc_pixel_values").show();
            }

            // event name, params - display only if fb pixel is enabled.
            $("#fb_pixel").on("change", function (e) {
                if ($('#fb_pixel').is(':checked')) {
                    $(".ctc_pixel_values").show(400);
                } else {
                    $(".ctc_pixel_values").hide(200);
                }
            });

            // if pixel_event_type is 'custom' then display .ctc_pixel_custom_event_name
            var pixel_event_type = $('.pixel_event_type').find(":selected").val();
            if (pixel_event_type == 'trackCustom') {
                $(".pixel_custom_event").show(100);
            } else if (pixel_event_type == 'track') {
                $(".pixel_standard_event").show(100);
            }

            // on change - pixel_event_type
            $(".pixel_event_type").on("change", function (e) {
                var pixel_event_type_change_val = e.target.value;
                console.log(pixel_event_type_change_val);
                if (pixel_event_type_change_val == 'trackCustom') {
                    $(".pixel_custom_event").show(200);
                    $(".pixel_standard_event").hide(100);
                } else if (pixel_event_type_change_val == 'track') {
                    $(".pixel_standard_event").show(200);
                    $(".pixel_custom_event").hide(100);
                }
            });

            var pixel_param_snippet = $('.ctc_pixel_param_snippets .ht_ctc_pixel_add_param');
            console.log(pixel_param_snippet);

            // add value
            $(document).on('click', '.ctc_add_pixel_param_button', function () {

                console.log('on click: add g an param button');
                console.log(pixel_param_snippet);

                var pixel_param_order = $('.pixel_param_order').val();
                pixel_param_order = parseInt(pixel_param_order);


                var pixel_param_clone = pixel_param_snippet.clone();
                console.log(pixel_param_clone);

                // filed number for reference
                $(pixel_param_clone).find('.pixel_param_order_ref_number').attr('name', `ht_ctc_othersettings[pixel_params][]`);
                $(pixel_param_clone).find('.pixel_param_order_ref_number').val('pixel_param_' + pixel_param_order);

                $(pixel_param_clone).find('.ht_ctc_pixel_add_param_key').attr('name', `ht_ctc_othersettings[pixel_param_${pixel_param_order}][key]`);
                $(pixel_param_clone).find('.ht_ctc_pixel_add_param_value').attr('name', `ht_ctc_othersettings[pixel_param_${pixel_param_order}][value]`);


                console.log($('.ctc_new_pixel_param'));

                $('.ctc_new_pixel_param').append(pixel_param_clone);


                pixel_param_order++;
                $('.pixel_param_order').val(pixel_param_order);
            });




            // Remove params
            $('.ctc_an_params').on('click', '.an_param_remove', function (e) {
                console.log('on click: an_param_remove');
                e.preventDefault();
                $(this).closest('.ctc_an_param').remove();
            });

            // analytics count
            $(".analytics_count_message").on("click", function (e) {
                // $(".analytics_count_message span").hide();
                $('.analytics_count_select').toggle(200);
            });

            // on change - analytics count value 
            $(".select_analytics").on("change", function (e) {
                var change_val = e.target.value;
                // $(".analytics_count_message span").show();
                // $('.analytics_count_select').hide(200);
                $(".analytics_count_message span").html(change_val);
            });

        }












    });
})(jQuery);