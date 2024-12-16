// Click to Chat
(function ($) {

    // ready
    $(function () {

        // variables
        var v = '4.9';
        var url = window.location.href;
        var post_title = (typeof document.title !== "undefined") ? document.title : '';
        var is_mobile = 'no';

        try {
            // Where user can install app. 
            is_mobile = (typeof navigator.userAgent !== "undefined" && navigator.userAgent.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i)) ? "yes" : "no";
            console.log('User agent: is_mobile: ' + is_mobile);
        } catch (e) {}

        if ('no' == is_mobile) {
            // is_mobile yes/no,  desktop > 1025
            var is_mobile = (typeof screen.width !== "undefined" && screen.width > 1025) ? "no" : "yes";
            console.log('screen width: is_mobile: ' + is_mobile);
        }

        var no_num = '';

        var ht_ctc_storage = {};

        function getStorageData() {
            console.log('app.js - getStorageData');
            if (localStorage.getItem('ht_ctc_storage')) {
                ht_ctc_storage = localStorage.getItem('ht_ctc_storage');
                ht_ctc_storage = JSON.parse(ht_ctc_storage);
                console.log(ht_ctc_storage);
            }
        }
        getStorageData();

        // get items from ht_ctc_storage
        function ctc_getItem(item) {
            console.log('app.js - ctc_getItem');
            return (ht_ctc_storage[item]) ? ht_ctc_storage[item] : false;
        }

        // set items to ht_ctc_storage storage
        function ctc_setItem(name, value) {
            console.log(ht_ctc_storage);
            getStorageData();
            console.log(ht_ctc_storage);
            console.log('app.js - ctc_setItem: name: ' + name + ' value: ' + value);
            ht_ctc_storage[name] = value;
            console.log(ht_ctc_storage);
            var newValues = JSON.stringify(ht_ctc_storage);
            localStorage.setItem('ht_ctc_storage', newValues);
        }

        var ctc = '';
        variable_ctc();

        var ctc_values = {};
        variable_ctc_values();

        // document.dispatchEvent(
        //     new CustomEvent("ht_ctc_fn_all", { detail: { ht_ctc_storage, ctc_setItem, ctc_getItem } })
        // );
        
        chat_data();
        start();

        /**
         * get ht_ctc_chat_var and assing to ctc variable
         */
        function variable_ctc() {
            if (typeof ht_ctc_chat_var !== "undefined") {
                ctc = ht_ctc_chat_var;
            } else {
                try {
                    if (document.querySelector('.ht_ctc_chat_data')) {
                        var settings = $('.ht_ctc_chat_data').attr('data-settings');
                        ctc = JSON.parse(settings);
                        window.ht_ctc_chat_var = ctc;
                    }
                } catch (e) {
                    ctc = {};
                }
            }
        }

        /**
         * get ht_ctc_variables and assing to ctc_values variable
         */
        function variable_ctc_values() {
            console.log('variable_ctc_values');

            if (typeof ht_ctc_variables !== "undefined") {
                ctc_values = ht_ctc_variables;
            } else {
                // fallback values(dont merge).. only if ht_ctc_variables not loaded.
                // later remove params in this fallback values to reduce size.
                ctc_values = {
                    'g_an_event_name': 'click to chat',
                    'pixel_event_name': 'Click to Chat by HoliThemes',
                    'pixel_event_type': 'trackCustom',
                    'g_an_params': ['g_an_param_1', 'g_an_param_2', 'g_an_param_3'],
                    'g_an_param_1': { 'key': 'number', 'value': '{number}' },
                    'g_an_param_2': { 'key': 'title', 'value': '{title}' },
                    'g_an_param_3': { 'key': 'url', 'value': '{url}' },
                    'pixel_params': ['pixel_param_1', 'pixel_param_2', 'pixel_param_3', 'pixel_param_4'],
                    'pixel_param_1': { 'key': 'Category', 'value': 'Click to Chat for WhatsApp' },
                    'pixel_param_2': { 'key': 'return_type', 'value': 'chat' },
                    'pixel_param_3': { 'key': 'ID', 'value': '{number}' },
                    'pixel_param_4': { 'key': 'Title', 'value': '{title}' },
                }
                window.ht_ctc_variables = ctc_values;
                console.log(ht_ctc_variables);
            }
            console.log(ctc_values);
        }

        function chat_data() {

            // if no num
            var chat_data = document.querySelector('.ht_ctc_chat_data');

            if (chat_data) {
                no_num = $(".ht_ctc_chat_data").attr('data-no_number');
                // remove the element
                chat_data.remove();
            }

        }

        // start
        function start() {

            console.log(ctc);
            document.dispatchEvent(
                new CustomEvent("ht_ctc_event_settings", { detail: { ctc } })
            );

            // fixed position
            ht_ctc();

            // shortcode
            shortcode();

            // custom element
            custom_link();

        }






        // fixed position
        function ht_ctc() {
            console.log('ht_ctc');
            var ht_ctc_chat = document.querySelector('.ht-ctc-chat');
            if (ht_ctc_chat) {

                document.dispatchEvent(
                    new CustomEvent("ht_ctc_event_chat")
                );

                // display
                display_settings(ht_ctc_chat);

                // click
                ht_ctc_chat.addEventListener('click', function () {
                    // ht_ctc_chat_greetings_box (ht_ctc_chat_greetings_box_link) is not exists..

                    if (!$('.ht_ctc_chat_greetings_box').length) {
                        console.log('no greetings dialog');
                        // link
                        ht_ctc_link(ht_ctc_chat);
                    }
                });

                // greetings dialog settings..
                greetings();

                // greetings link click..
                $(document).on('click', '.ht_ctc_chat_greetings_box_link', function (e) {
                    console.log('ht_ctc_chat_greetings_box_link');
                    e.preventDefault();

                    // optin
                    if (document.querySelector('#ctc_opt')) {
                        // if (ctc_getItem('g_optin')) {
                        //     $('#ctc_opt').prop('checked', true);
                        // }
                        if ($('#ctc_opt').is(':checked') || ctc_getItem('g_optin')) {
                            console.log('optin');
                            ht_ctc_link(ht_ctc_chat);
                            // close greetings dialog
                            greetings_close_500();
                        } else {
                            console.log('animate option checkbox');
                            $('.ctc_opt_in').show(400).fadeOut('1').fadeIn('1');
                        }
                    } else {
                        ht_ctc_link(ht_ctc_chat);
                        // close greetings dialog
                        greetings_close_500();
                    }

                    document.dispatchEvent(
                        new CustomEvent("ht_ctc_event_greetings")
                    );

                });

                // optin - checkbox on change
                if (document.querySelector('#ctc_opt')) {
                    $("#ctc_opt").on("change", function (e) {
                        if ($('#ctc_opt').is(':checked')) {
                            $('.ctc_opt_in').hide(100);
                            ctc_setItem('g_optin', 'y');
                            setTimeout(() => {
                                ht_ctc_link(ht_ctc_chat);
                                greetings_close_500();
                            }, 500);
                        }
                    });
                }

            }

        }


        /**
         * greetings dialog
         */
        function greetings() {

            if ($('.ht_ctc_chat_greetings_box').length) {

                $(document).on('click', '.ht_ctc_chat_style', function (e) {
                    // ctc_greetings_opened / ctc_greetings_closed
                    if ($('.ht_ctc_chat_greetings_box').hasClass('ctc_greetings_opened')) {
                        greetings_close('user_closed');
                    } else {
                        greetings_open('user_opened');
                    }
                });

            }

            // close btn - greetings dialog
            $(document).on('click', '.ctc_greetings_close_btn', function (e) {
                greetings_close('user_closed');
            });

        }

        function greetings_display() {
            console.log('greetings_display');

            if ($('.ht_ctc_chat_greetings_box').length) {

                // Display greetings - device based
                if (ctc.g_device) {
                    if (is_mobile !== 'yes' && 'mobile' == ctc.g_device) {
                        // in desktop: mobile only
                        $('.ht_ctc_chat_greetings_box').remove();
                        return;
                    } else if (is_mobile == 'yes' && 'desktop' == ctc.g_device) {
                        // in mobile: desktop only
                        $('.ht_ctc_chat_greetings_box').remove();
                        return;
                    }
                }

                document.dispatchEvent(
                    new CustomEvent("ht_ctc_event_after_chat_displayed", { detail: { ctc, greetings_open, greetings_close } })
                );

                if (ctc.g_init && 'user_closed' !== ctc_getItem('g_user_action')) {
                    console.log('g_init');
                    // initial stage - default(preset): open on desktop, closes on mobile. open: open on all devices.
                    if ('default' == ctc.g_init) {
                        // if desktop then open
                        if (is_mobile !== 'yes') {
                            greetings_open('init');
                        }
                    } else if('open' == ctc.g_init) {
                        greetings_open('init');
                    }
                }


                $(document).on('click', '.ctc_greetings, #ctc_greetings, .ctc_greetings_now, [href="#ctc_greetings"]', function (e) {
                    console.log('greetings open triggered');
                    e.preventDefault();
                    greetings_close('element');
                    greetings_open('element');
                });

            }

        }

        /**
         * ht_ctc_chat_greetings_box_user_action - this is needed for initial close or open.. if user closed.. then no auto open initially
         * 
         */
        function greetings_open(message = 'open') {
            console.log('Greetings open: ' + message);

            stop_notification_badge();

            $('.ctc_cta_stick').remove();


            // todo:l enhance entry animations effects.. 
            if ('init' == message) {
                $('.ht_ctc_chat_greetings_box').show(70);
            } else {
                $('.ht_ctc_chat_greetings_box').show(400);
            }


            $('.ht_ctc_chat_greetings_box').addClass('ctc_greetings_opened').removeClass('ctc_greetings_closed');
            ctc_setItem('g_action', message);
            if ('user_opened' == message) {
                ctc_setItem('g_user_action', message);
            }
        }

        function greetings_close_500() {
            setTimeout(() => {
                greetings_close('chat_clicked');
            }, 500);
        }

        function greetings_close(message = 'close') {
            console.log('Greetings close: ' + message);
            
            if ('element' == message) {
                $('.ht_ctc_chat_greetings_box').hide(70);
            } else {
                $('.ht_ctc_chat_greetings_box').hide(400);
            }

            $('.ht_ctc_chat_greetings_box').addClass('ctc_greetings_closed').removeClass('ctc_greetings_opened');
            ctc_setItem('g_action', message);
            if ('user_closed' == message) {
                ctc_setItem('g_user_action', message);
            }
        }

        // display settings - Fixed position style
        function display_settings(ht_ctc_chat) {

            if ('yes' == ctc.schedule) {
                console.log('scheduled');
                document.dispatchEvent(
                    new CustomEvent("ht_ctc_event_display", { detail: { ctc, display_chat, ht_ctc_chat, online_content } })
                );
            } else {
                console.log('display directly');
                display_chat(ht_ctc_chat);
                online_content();
            }

        }

        // display based on device
        function display_chat(p) {

            if (is_mobile == 'yes') {
                if ('show' == ctc.dis_m) {

                    // remove desktop style
                    var rm = document.querySelector('.ht_ctc_desktop_chat');
                    (rm) ? rm.remove() : '';

                    p.style.cssText = ctc.pos_m + ctc.css;
                    display(p)
                }
            } else {
                if ('show' == ctc.dis_d) {

                    // remove mobile style
                    var rm = document.querySelector('.ht_ctc_mobile_chat');
                    (rm) ? rm.remove() : '';

                    p.style.cssText = ctc.pos_d + ctc.css;
                    display(p)
                }
            }


        }

        function display(p) {
            try {
                $(p).show(parseInt(ctc.se));
            } catch (e) {
                p.style.display = "block";
            }

            greetings_display();
            display_notifications();

            ht_ctc_things(p);
        }

        /**
         * online content
         * 
         * @since 3.34
         */
        function online_content() {

            console.log('online_content');

            if ($('.for_greetings_header_image_badge').length) {
                $('.for_greetings_header_image_badge').addClass('g_header_badge_online');
                // $('.for_greetings_header_image_badge').show(500);
                $('.for_greetings_header_image_badge').show();
            }


        }


        // ht_ctc_notification
        function display_notifications() {
            console.log('display_notifications');
            if (document.querySelector('.ht_ctc_notification') && 'stop' !== ctc_getItem('n_badge')) {

                if (document.querySelector('.ctc_nb')) {
                    console.log('overwrite top, right');
                    // get parent of badge and then get top, right with in that element. (to avoid conflict with other styles if added using shortcode or so...)
                    var main = $('.ht_ctc_badge').closest('.ht_ctc_style');

                    $('.ht_ctc_badge').css({
                        // overwrite top, right. if undefined or false then use default(as it can't overwrite at broswer).
                        "top": $(main).find('.ctc_nb').attr('data-nb_top'),
                        "right": $(main).find('.ctc_nb').attr('data-nb_right')
                    });
                }


                var n_time = (ctc.n_time) ? ctc.n_time * 1000 : '150'
                setTimeout(() => {
                    console.log('display_notifications: show');
                    $('.ht_ctc_notification').show(400);
                }, n_time);

            }
        }

        // after user clicks to chat or open greetings
        function stop_notification_badge() {
            console.log('stop _notification _badge');
            if (document.querySelector('.ht_ctc_notification')) {
                console.log('stop _notification _badge in if');
                ctc_setItem('n_badge', 'stop');
                $('.ht_ctc_notification').remove();
            }
        }

        // animiation, cta hover effect
        function ht_ctc_things(p) {
            console.log('animations ' + ctc.ani);
            // animations
            var an_time = ($(p).hasClass('ht_ctc_entry_animation')) ? 1200 : 120;
            setTimeout(function () {
                p.classList.add('ht_ctc_animation', ctc.ani);
            }, an_time);

            // cta hover effects
            $(".ht-ctc-chat").hover(function () {
                $('.ht-ctc-chat .ht-ctc-cta-hover').show(120);
            }, function () {
                $('.ht-ctc-chat .ht-ctc-cta-hover').hide(100);
            });
        }

        // analytics
        function ht_ctc_chat_analytics(values) {

            console.log('analytics');
            console.log(values);

            if (ctc.analytics) {

                // todo:l - maybe else need if user changed to all clicks. 
                if ('session' == ctc.analytics) {

                    if (sessionStorage.getItem('ht_ctc_analytics')) {
                        // not a unique session - return
                        console.log(sessionStorage.getItem('ht_ctc_analytics'));
                        console.log('no analytics');
                        return;
                    } else {
                        // unique session - continue..
                        console.log('no sessionStorage');
                        sessionStorage.setItem('ht_ctc_analytics', 'done');
                        console.log('added new sessionStorage');
                    }

                }

            }

            // apply variables
            function apply_variables(v) {
                console.log('apply_variables');
                var number = (ctc.chat_number && '' !== ctc.chat_number) ? ctc.chat_number : ctc.number;
                console.log(number);

                try {

                    console.log(v);
                    document.dispatchEvent(
                        new CustomEvent("ht_ctc_event_apply_variables", { detail: { v } })
                    );

                    console.log('window.apply_variables_value: ' + window.apply_variables_value);

                    // if window.apply_variables_value is set.. then use that value. can set by extension or so.
                    v = (typeof window.apply_variables_value !== "undefined") ? window.apply_variables_value : v;

                    console.log(v);

                    // v = v.replace(/\{number\}/gi, number);
                    v = v.replace('{number}', number);
                    v = v.replace('{title}', post_title);
                    v = v.replace('{url}', url);
                } catch (e) { }

                console.log(v);
                return v;
            }


            document.dispatchEvent(
                new CustomEvent("ht_ctc_event_analytics")
            );

            var id = (ctc.chat_number && '' !== ctc.chat_number) ? ctc.chat_number : ctc.number;

            // if its shortcode
            // if (values.classList.contains('ht-ctc-sc')) {
            //     // shortcode number
            //     id = values.getAttribute('data-number');
            // }

            console.log(id);

            // Google Analytics

            /**
             * if installed using GTM then gtag may not work. so user can create event using dataLayer object.
             * if google anlatyics installed using gtm (from GTM user can create event using gtm datalayer object, ...)
             * 
             * if google analytics installed directly. then gtag works. 
             * 
             * analytics - event names added to ht_ctc_chat_var (its loads most cases with out issue) and event params added to ht_ctc_variables.
             */


            var ga_parms = {};
            var ga_category = 'Click to Chat for WhatsApp';
            var ga_action = 'chat: ' + id;
            var ga_label = post_title + ', ' + url;


            // if ga_enabled
            if (ctc.ga) {
                console.log('google analytics');

                var g_event_name = (ctc.g_an_event_name && '' !== ctc.g_an_event_name) ? ctc.g_an_event_name : 'click to chat';
                console.log('Event Name: ' + g_event_name);
                g_event_name = apply_variables(g_event_name);

                // if ht_ctc_variables is not loaded to front end, then use default values.
                // since 3.31. with user defined event name, params
                console.log(ctc_values);

                if (ctc_values.g_an_params) {
                    console.log('g_an_params');
                    console.log(ctc_values.g_an_params);
                    ctc_values.g_an_params.forEach(e => {
                        console.log(e);
                        if (ctc_values[e]) {
                            var p = ctc_values[e];
                            console.log(p);
                            var k = p['key'];
                            var v = p['value'];
                            k = apply_variables(k);
                            v = apply_variables(v);
                            console.log(k);
                            console.log(v);
                            ga_parms[k] = v;
                        }
                    });
                }
                console.log('ga_parms');
                console.log(ga_parms);

                var gtag_count = 0;

                // is gtag function added by plugin
                var is_ctc_add_gtag = 'no';

                if (typeof dataLayer !== "undefined") {

                    console.log('event with gtag id..');

                    try {

                        // if gtag not defined. then create gtag function
                        if (typeof gtag == "undefined") {
                            console.log('gtag not defined');
                            window.gtag = function () {
                                dataLayer.push(arguments);
                            };
                            is_ctc_add_gtag = 'yes';
                        }

                        var tags_list = [];

                        function call_gtag(tag_id) {

                            tag_id = tag_id.toUpperCase();
                            console.log('fn: call_gtag(): ' + tag_id);

                            
                            console.log(tags_list);

                            if (tags_list.includes(tag_id)) {
                                console.log('tag_id already included');
                                return;
                            }

                            tags_list.push(tag_id);
                            console.log(tags_list);

                            // if starts with g- or gt-
                            if (tag_id.startsWith('G-') || tag_id.startsWith('GT-')) {

                                ga_parms['send_to'] = tag_id;
                                console.log(ga_parms);

                                console.log('gtag event - send_to: ' + tag_id);

                                gtag('event', g_event_name, ga_parms);

                                gtag_count++;

                            }
                        }

                        if (window.google_tag_data && window.google_tag_data.tidr && window.google_tag_data.tidr.destination) {
                            console.log('google_tag_data tidr destination');
                            console.log(window.google_tag_data.tidr.destination);

                            // for each tag_id
                            for (var tag_id in window.google_tag_data.tidr.destination) {
                                console.log('google_tag_data destination - loop: ' + tag_id);
                                call_gtag(tag_id);
                            }
                        }

                        dataLayer.forEach(function (i) {
                            console.log('datalayer - loop');
                            console.log(i);
                            if (i[0] == 'config' && i[1]) {
                                tag_id = i[1];
                                console.log('datalayer - loop - tag_id: ' + tag_id);
                                call_gtag(tag_id);
                            }
                        });

                    } catch (e) {}
                }

                // if above method sending event with tag_id is not worked. and if gtag is already defined. then call default gtag (safe side)
                if (0 == gtag_count && 'no' == is_ctc_add_gtag) {
                    if (typeof gtag !== "undefined") {
                        console.log('calling gtag - default');
                        gtag('event', g_event_name, ga_parms);
                    } else if (typeof ga !== "undefined" && typeof ga.getAll !== "undefined") {
                        console.log('ga');
                        var tracker = ga.getAll();
                        tracker[0].send("event", ga_category, ga_action, ga_label);
                        // ga('send', 'event', 'check ga_category', 'ga_action', 'ga_label');
                        // ga.getAll()[0].send("event", 'check ga_category', 'ga_action', 'ga_label');
                    } else if (typeof __gaTracker !== "undefined") {
                        console.log('__gaTracker');
                        __gaTracker('send', 'event', ga_category, ga_action, ga_label);
                    }
                }

            }

            // dataLayer (for GTM)
            if (typeof dataLayer !== "undefined") {
                console.log('dataLayer');
                dataLayer.push({
                    'event': 'Click to Chat',
                    'type': 'chat',
                    'number': id,
                    'title': post_title,
                    'url': url,
                    'event_category': ga_category,
                    'event_label': ga_label,
                    'event_action': ga_action,
                    'ref': 'dataLayer push'
                });
            }

            // google ads - call conversation code
            if (ctc.ads) {
                console.log('google ads enabled');
                if (typeof gtag_report_conversion !== "undefined") {
                    console.log('calling gtag_report_conversion');
                    gtag_report_conversion();
                }
            }

            /**
             * FB Pixel
             * https://developers.facebook.com/docs/meta-pixel/implementation/conversion-tracking
             */
            if (ctc.fb) {
                console.log('fb pixel');

                if (typeof fbq !== "undefined") {

                    // event name
                    var pixelEventName = (ctc.pixel_event_name && '' !== ctc.pixel_event_name) ? ctc.pixel_event_name : 'Click to Chat by HoliThemes';
                    console.log('Event Name: ' + pixelEventName);

                    // Event type: track/trackCustom
                    var pixelTrack = (ctc_values.pixel_event_type && '' !== ctc_values.pixel_event_type) ? ctc_values.pixel_event_type : 'trackCustom';
                    console.log('Track: ' + pixelTrack);
                    
                    var pixelParams = {};
                    console.log(typeof pixelParams);
                    
                    if (ctc_values.pixel_params) {
                        console.log(ctc_values.pixel_params);
                        console.log('pixel_params');
                        ctc_values.pixel_params.forEach(e => {
                            console.log(e);
                            if (ctc_values[e]) {
                                var p = ctc_values[e];
                                console.log(p);
                                var k = p['key'];
                                var v = p['value'];
                                k = apply_variables(k);
                                v = apply_variables(v);
                                console.log(k);
                                console.log(v);
                                pixelParams[k] = v;
                            }
                        });
                    }
                    console.log(pixelParams);

                    fbq(pixelTrack, pixelEventName, pixelParams);
                }
            }

        }

        /**
         *  link - chat
         * 
         * @used floating chat, shortcode, custom element. ht_ctc_chat_greetings_box_link click
         */
        function ht_ctc_link(values) {

            console.log('ht_ctc_link');
            console.log(values);

            console.log(ctc.number);
            document.dispatchEvent(
                new CustomEvent("ht_ctc_event_number", { detail: { ctc } })
            );
            console.log(ctc.number);

            var number = ctc.number;
            var pre_filled = ctc.pre_filled;

            if ( values.hasAttribute('data-number') && '' !== values.getAttribute('data-number') ) {
                console.log('data-number is added');
                number = values.getAttribute('data-number');
                console.log('data-number: ' + number);
            }

            if (values.hasAttribute('data-pre_filled')) {
                console.log('has pre_filled attribute');
                pre_filled = values.getAttribute('data-pre_filled');
            }

            /**
             * safari 13.. before replaceAll not supports..
             */
            try {
                pre_filled = pre_filled.replaceAll('%', '%25');

                var update_url = window.location.href;
                pre_filled = pre_filled.replace(/\[url]/gi, update_url);

                // pre_filled = encodeURIComponent(pre_filled);
                pre_filled = encodeURIComponent(decodeURI(pre_filled));
            } catch (e) { }

            if ('' == number) {
                console.log('no number');
                $(".ht-ctc-chat").html(no_num);
                return;
            }

            // navigations links..
            // 1.base_url
            var base_url = 'https://wa.me/' + number + '?text=' + pre_filled;

            // 2.url_target - _blank, _self or if popup type just add a name - here popup only
            var url_target = (ctc.url_target_d) ? ctc.url_target_d : '_blank';

            if (is_mobile == 'yes') {
                console.log('-- mobile --');
                // mobile
                if (ctc.url_structure_m) {
                    console.log('-- url struture: whatsapp:// --');
                    // whatsapp://.. is selected.
                    base_url = 'whatsapp://send?phone=' + number + '&text=' + pre_filled;
                    // for whatsapp://.. url open target is _self.
                    url_target = '_self';
                }
                // mobile: own url
                if (ctc.custom_url_m && '' !== ctc.custom_url_m) {
                    console.log('custom link');
                    base_url = ctc.custom_url_m;
                }

            } else {
                // desktop
                console.log('-- desktop --');
                if (ctc.url_structure_d) {
                    console.log('-- url struture: web whatsapp --');
                    // web whatsapp is enabled/selected.
                    base_url = 'https://web.whatsapp.com/send' + '?phone=' + number + '&text=' + pre_filled;
                }

                // desktop: own url
                if (ctc.custom_url_d && '' !== ctc.custom_url_d) {
                    console.log('custom link');
                    base_url = ctc.custom_url_d;
                }
            }

            // 3.specs - specs - if popup then add 'pop_window_features' else 'noopener'
            var pop_window_features = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=788,height=514,left=100,top=100';
            var specs = ('popup' == url_target) ? pop_window_features : 'noopener';
            console.log('-- specs: ' + specs + ' --');

            // if ('popup' == url_target) {
            //     var pop_window = window.open(base_url, url_target, specs);
            //     try {
            //         // with some extensions if popup is not opened, popup focus is true - i.e. not calling cache. 
            //         console.log('pop focus try..');
            //         console.log(pop_window);


            //         /**
            //          * if issue it throws error and runs cache. 
            //          * (with some browser blockers it works good as the popup is loaded and it calling cache, 
            //          *   but with browser extension blockers - the popup is not loaded and its not thowing cache, the code continues working.)
            //          */
            //         pop_window.focus();

            //         // for some popup blockers - .focus, .blur, .closed may not works well...  as some blockers pop_window is refering to the same window only.
            //         // if pop_window have ht_ctc_chat_var then it refer to same window. i.e. popup might be blocked. so call createlink
            //         if (pop_window.ht_ctc_chat_var) {
            //             // if true. then its not the real popup whatsapp window. some browser blockers may blocked popup
            //             console.log('ht_ctc_chat_var exists on pop_window variable');
            //             createlink();
            //         }

            //         console.log('pop window focused..');
            //     } catch (e) {
            //         console.log('pop cache');
            //         console.log(e);
            //         createlink();
            //     }
            // } else {
            //     // By adding settimeout works better with some blocker extensions.

            //     // desktop 1ms delay, mobile no settimeout
            //     if (is_mobile == 'yes') {
            //         window.open(base_url, url_target, specs);
            //     } else {
            //         setTimeout(() => {
            //             console.log('normal: window.open - with setimeout 1ms');
            //             window.open(base_url, url_target, specs);
            //         }, 1);
            //     }

            // }

            // function createlink() {
            //     console.log('createlink');
            //     var link = "<a class='ht_ctc_dynamic' style='display:none;' target='_blank' href="+ base_url +"></a>";
            //     $('body').append(link);
            //     $('.ht_ctc_dynamic')[0].click();
            //     $('.ht_ctc_dynamic').remove();
            // }

            window.open(base_url, url_target, specs);

            // number assigned to the clicked element.
            ctc.chat_number = number;

            // analytics
            ht_ctc_chat_analytics(values);

            // hook
            hook(number);

            stop_notification_badge();

        }

        // shortcode
        function shortcode() {
            // shortcode - click
            $(document).on('click', '.ht-ctc-sc-chat', function () {

                /**
                 * @since 4.3 calling ht_ctc_link function directly... 
                 * benficts using global number.. page level settings number, .. random number, .. shortcode number.
                 * url structure.. 
                 */
                // var number = this.hasAttribute('data-number') ? this.getAttribute('data-number') : '';
                // console.log(typeof number);

                // console.log('shortcode number: ' + number);

                // if ('' == number) {
                //     console.log('shortcode: adding global number');
                //     number = ctc.number;
                //     console.log('shortcode: global number: ' + number);
                // }
                
                // var pre_filled = this.getAttribute('data-pre_filled');
                // pre_filled = pre_filled.replace(/\[url]/gi, url);
                // pre_filled = encodeURIComponent(pre_filled);

                // if (ctc.url_structure_d && is_mobile !== 'yes') {
                //     // web.whatsapp - if web api is enabled and is not mobile
                //     window.open('https://web.whatsapp.com/send' + '?phone=' + number + '&text=' + pre_filled, '_blank', 'noopener');
                // } else {
                //     // wa.me
                //     window.open('https://wa.me/' + number + '?text=' + pre_filled, '_blank', 'noopener');
                // }

                // // analytics
                // ctc.chat_number = number;

                // ht_ctc_chat_analytics(this);

                // // webhook
                // hook(number);

                console.log('shortcode click');
                ht_ctc_link(this);

            });
        }

        // custom element
        function custom_link() {

            $(document).on('click', '.ctc_chat, #ctc_chat', function (e) {
                console.log('class/Id: ctc_chat');
                ht_ctc_link(this);

                if ($(this).hasClass('ctc_woo_place')) {
                    // its woo link..
                    e.preventDefault();
                }
            });

            $(document).on('click', '[href="#ctc_chat"]', function (e) {
                console.log('#ctc_chat');
                e.preventDefault();
                ht_ctc_link(this);
            });
        }


        // hook related values..
        var g_hook_v = (ctc.hook_v) ? ctc.hook_v : '';

        // webhooks
        function hook(number) {

            console.log('hook');

            if (ctc.hook_url) {

                var hook_values = {};

                // hook values
                if (ctc.hook_v) {

                    hook_values = (typeof g_hook_v !== "undefined") ? g_hook_v : ctc.hook_v;
                    // var hook_values = ctc.hook_v;

                    console.log(typeof hook_values);
                    console.log(hook_values);

                    var pair_values = {};
                    var i = 1;

                    hook_values.forEach(e => {
                        console.log(i);
                        console.log(e);
                        pair_values['value' + i] = e;
                        i++;
                    });

                    console.log(typeof pair_values);
                    console.log(pair_values);

                    ctc.hook_v = pair_values;
                }

                document.dispatchEvent(
                    new CustomEvent("ht_ctc_event_hook", { detail: { ctc, number } })
                );

                var h_url = ctc.hook_url;
                hook_values = ctc.hook_v;

                console.log(h_url);
                console.log(hook_values);

                if (ctc.webhook_format && 'json' == ctc.webhook_format) {
                    console.log('main hook: json');
                    var data = hook_values;
                } else {
                    console.log('main hook: string');
                    var data = JSON.stringify(hook_values);
                }


                console.log(data);
                console.log(typeof data);


                $.ajax({
                    url: h_url,
                    type: "POST",
                    mode: 'no-cors',
                    data: data,
                    success: function (response) {
                        console.log(response);
                    }
                });

            }


        }



    });

})(jQuery);