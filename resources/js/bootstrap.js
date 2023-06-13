import _ from "lodash";
window._ = _;

import IMask from "imask";
import * as bootstrap from "bootstrap";
import "bootstrap-icons/font/bootstrap-icons.css";
import selectize from "@selectize/selectize";
import "@selectize/selectize/dist/css/selectize.bootstrap5.css";
import {
    TempusDominus,
    Namespace as tdNamespace,
    DateTime,
} from "@eonasdan/tempus-dominus";
import "@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css";
import moment from "moment/moment";

window.bootstrap = bootstrap;
window.IMask = IMask;
window.selectize = selectize;
window.TempusDominus = TempusDominus;
window.tdNamespace = tdNamespace;
window.DateTime = DateTime;
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

const tDConfigsNoClear = {
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
            clear: false,
        },
    },
    allowInputToggle: false,
};

const tDConfigsWithTime = {
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
            clock: true,
            hours: true,
            minutes: true,
            seconds: true,
        },
        buttons: {
            today: true,
            clear: true,
        },
    },
    allowInputToggle: true,
};

const tDConfigsTime = {
    useCurrent: false,
    stepping: 30,
    display: {
        viewMode: "clock",
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
            calendar: false,
            date: false,
            month: false,
            year: false,
            decades: false,
            hours: true,
            minutes: true,
            seconds: false,
        },
        buttons: {
            close: true,
            clear: true,
        },
    },
    localization: {
        hourCycle: "h23",
    },
    allowInputToggle: false,
};

window.tDConfigs = tDConfigs;
window.tDConfigsNoClear = tDConfigsNoClear;
window.tDConfigsWithTime = tDConfigsWithTime;
window.tDConfigsTime = tDConfigsTime;

// Table management
const makePagination = (data) => {
    let html;
    let pages = "";
    let pageButton = "";
    const currentPage = parseInt(data.pagination.page);
    const totalCount = parseInt(data.pagination.pageCount);

    if (totalCount <= 6) {
        for (let i = 1; i <= totalCount; i++) {
            pageButton += addButton(currentPage, i);
        }
    } else {
        // Always print first page button
        pageButton += addButton(currentPage, 1);
        if (currentPage > 3) {
            pageButton += `
              <li class="page-item">
                  <span class="page-link">...</span>
              </li>
          `;
        }
        // Print "..." only if currentPage is > 3
        if (currentPage == totalCount) {
            pageButton += addButton(currentPage, currentPage - 2);
        }
        // special case where last page is selected...
        if (currentPage > 2) {
            pageButton += addButton(currentPage, currentPage - 1);
        }
        // Print current page number button as long as it not the first or last page
        if (currentPage != 1 && currentPage != totalCount) {
            pageButton += addButton(currentPage, currentPage);
        }
        //print next number button if currentPage < lastPage - 1
        if (currentPage < totalCount - 1) {
            pageButton += addButton(currentPage, currentPage + 1);
        }
        // special case where first page is selected...
        if (currentPage == 1) {
            pageButton += addButton(currentPage, currentPage + 2);
        }
        //print "..." if currentPage is < lastPage -2
        if (currentPage < totalCount - 2) {
            pageButton += `
              <li class="page-item">
                  <span class="page-link">...</span>
              </li>
          `;
        }
        //Always print last page button if there is more than 1 page
        pageButton += addButton(currentPage, totalCount);
    }

    function addButton(page, number) {
        return `
          <li ${
              page !== number ? `onclick="window.getList(${number - 1})"` : ""
          }
              class="page-item${page === number ? " active" : ""}">
              ${
                  page !== number
                      ? `<a class="page-link">${number}</a>`
                      : `<span class="page-link">${number}</span>`
              }
          </li>
      `;
    }

    html = `
  <nav aria-label="...">
      <ul class="pagination">
          <li ${
              data.pagination.page !== 1 ? `onclick="window.getList(0)"` : ""
          } class="page-item ${data.pagination.page === 1 ? "disabled" : ""}">
          ${
              data.pagination.page !== 1
                  ? `<a class="page-link"><i class="me-2 bi bi-chevron-double-left"></i>First</a>`
                  : `<span class="page-link"><i class="me-2 bi bi-chevron-double-left"></i>First</span>`
          }
          </li>
          ${pageButton}
          <li ${
              data.pagination.page !== data.pagination.pageCount
                  ? `onclick="window.getList(${data.pagination.pageCount - 1})"`
                  : ""
          } class="page-item ${
        data.pagination.page === data.pagination.pageCount ? "disabled" : ""
    }">
          ${
              data.pagination.page !== data.pagination.pageCount
                  ? `<a class="page-link"><i class="me-2 bi bi-chevron-double-right"></i>Last</a>`
                  : `<span class="page-link"><i class="me-2 bi bi-chevron-double-right"></i>Last</span>`
          }
          </li>
      </ul>
  </nav>
  `;

    return html;
};
window.makePagination = makePagination;
const showTableLoading = (col, bodyId) => {
    let html = `
      <tr>
          <td colspan="${col}">
              <div class="d-flex justify-content-center">
                  <div class="spinner-border m-5" role="status">
                      <span class="visually-hidden">Loading...</span>
                  </div>
              </div>
          </td>
      </tr>
  `;

    $(bodyId).html(html);
};
window.showTableLoading = showTableLoading;

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

import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
