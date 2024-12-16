import Cookies from "js-cookie";
import { on } from '../admin/utility/on';

window.addEventListener('DOMContentLoaded', () => {

    on('#wp-admin-bar-age-gate-toggle', 'click', '.ab-item', (e) => {
        e.preventDefault();

        const params = new URLSearchParams(e.target.href);
        const ls = params.get("ls"); // is the string "Jonathan"

        const { ag_cookie_domain, ag_cookie_name } = window;

        if (!ls) {

            if (Cookies.get(ag_cookie_name)) {
                const data = new FormData;
                data.append('action', 'ag_clear_cookie');
                Cookies.set(ag_cookie_name, 1, {
                    path: '/',
                    domain: ag_cookie_domain,
                    expires: -1,
                    secure: window.location.protocol.match(/https/) ? true : false,
                    sameSite: window.location.protocol.match(/https/) ? 'None' : false,
                });
            } else {
                Cookies.set(ag_cookie_name, '99', {
                    path: '/',
                    domain: ag_cookie_domain,
                    secure: window.location.protocol.match(/https/) ? true : false,
                    sameSite: window.location.protocol.match(/https/) ? 'None' : false,

                });
            }
        } else {
            if (localStorage.getItem(ag_cookie_name)) {
                localStorage.removeItem(ag_cookie_name);
            } else {
                const currentTime = new Date().getTime();
                const expires = new Date(currentTime + (1 * 60 * 60 * 1000)).getTime();

                const item = {
                    value: 99,
                    expires,
                };

                localStorage.setItem(ag_cookie_name, JSON.stringify(item));
            }
        }

        window.location.reload();
    });
});
