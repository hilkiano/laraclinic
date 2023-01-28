import _ from "lodash";
window._ = _;

import IMask from "imask";
import * as bootstrap from "bootstrap";
import "bootstrap-icons/font/bootstrap-icons.css";
import selectize from "@selectize/selectize";
import "@selectize/selectize/dist/css/selectize.bootstrap5.css";
import { TempusDominus } from "@eonasdan/tempus-dominus";
import "@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css";
import moment from "moment/moment";

window.bootstrap = bootstrap;
window.IMask = IMask;
window.selectize = selectize;
window.TempusDominus = TempusDominus;
window.moment = moment;

const tDConfigs = {
    display: {
        icons: {
            type: "icons",
            time: "bi bi-alarm-fill",
            date: "bi bi-calendar",
            up: "bi bi-arrow-up",
            down: "bi bi-arrow-down",
            previous: "bi bi-chevron-left",
            next: "bi bi-chevron-right",
            today: "bi bi-calendar-fill",
            clear: "bi bi-trash",
            close: "bi bi-x-lg",
        },
        theme: "light",
        components: {
            calendar: true,
            date: true,
            month: true,
            year: true,
            decades: true,
            clock: false,
            hours: false,
            minutes: false,
            seconds: false,
        },
        buttons: {
            clear: true,
        },
    },
    allowInputToggle: true,
};

window.tDConfigs = tDConfigs;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
