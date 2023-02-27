<script type="module">
    const liveToast = document.getElementById("liveToast");

    let fromDTPicker;
    let toDTPicker;

    const getList = async (page) => {
        showLoading();
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const param = {
            limit: 10,
            page: page ? page : 0
        };
        if ($("#patientName").val()) {
            param['name'] = $("#patientName").val();
        }
        if ($("#reasonFilter").val() !== 'all') {
            param['reason'] = $("#reasonFilter").val();
        }
        if ($("#statusFilter").val() !== 'all') {
            param['status'] = $("#statusFilter").val();
        }
        if (fromDTPicker.dates.lastPicked && toDTPicker.dates.lastPicked) {
            param['startDate'] = moment.parseZone(fromDTPicker.dates.lastPicked).utc().format();
            param['endDate'] = moment.parseZone(toDTPicker.dates.lastPicked).utc().format();
        }
        const formData = new FormData();
        for (var key in param) {
            formData.append(key, param[key]);
        }
        const req = await fetch("/api/v1/appointment/get-complete-list", {
            headers: {
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: formData
        });
        const response = await req.json();
        if (response) {
            const toast = new bootstrap.Toast(liveToast);
            if (response.status) {
                $("#appointmentRows").empty();
                if (response.data.length > 0) {
                    response.data.map((row, i) => iteratePaginationData(row, i));
                    $("#allCount").html(`${response.count}`);
                    $("#pagination").html(makePagination(response));
                } else {
                    let html = `
                        <tr>
                            <td colspan="6">No Data.</td>
                        </tr>
                    `;

                    $("#appointmentRows").append(html);
                }
            } else {
                document
                    .getElementById("errorToastHeader")
                    .classList.add("d-block");
                document
                    .getElementById("successToastHeader")
                    .classList.add("d-none");
                if (typeof response.message === "string") {
                    document.getElementById("toastBody").innerHTML =
                        response.message;
                    toast.show();
                }
            }
        }
    }

    const showLoading = () => {
        let html = `
            <tr>
                <td colspan="6">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border m-5" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </td>
            </tr>
        `;

        $("#appointmentRows").html(html);
    }

    const makePagination = (data) => {
        let html;
        let pages = '';

        for (let i = 1; i <= data.pagination.pageCount; i++) {
            pages += `
                <li ${data.pagination.page !== i ? `onclick="window.getList(${i - 1})"` : ""}
                    class="page-item${data.pagination.page === i ? " active" : ""}">
                    ${data.pagination.page !== i ? `<a class="page-link">${i}</a>` : `<span class="page-link">${i}</span>`}
                </li>
            `;
        }

        html = `
        <nav aria-label="...">
            <ul class="pagination">
                <li ${data.pagination.page !== 1 ? `onclick="window.getList(0)"` : ""} class="page-item ${data.pagination.page === 1 ? "disabled" : ""}">
                ${data.pagination.page !== 1 ? `<a class="page-link"><i class="me-2 bi bi-chevron-double-left"></i>First</a>` : `<span class="page-link"><i class="me-2 bi bi-chevron-double-left"></i>First</span>`}
                </li>
                ${pages}
                <li ${data.pagination.page !== data.pagination.pageCount ? `onclick="window.getList(${data.pagination.pageCount - 1})"` : ""} class="page-item ${data.pagination.page === data.pagination.pageCount ? "disabled" : ""}">
                ${data.pagination.page !== data.pagination.pageCount ? `<a class="page-link"><i class="me-2 bi bi-chevron-double-right"></i>Last</a>` : `<span class="page-link"><i class="me-2 bi bi-chevron-double-right"></i>Last</span>`}
                </li>
            </ul>
        </nav>
        `;

        return html;
    }

    const iteratePaginationData = (row, i) => {
        let html;
        let visitReason;
        if (row.visit_reason === "pharmacy") {
            visitReason = `<p class="fs-5 mb-0"><span class="badge bg-secondary">Pharmacy</span></p>`;
        } else {
            visitReason = `<p class="fs-5 mb-0"><span class="badge bg-secondary">Doctor</span></p>`;
        }
        let status;
        if (row.status === "waiting") {
            status = `<p class="fs-5 mb-0"><span class="badge bg-info">Waiting</span></p>`;
        }
        html += `
            <tr>
                <td scope="row">${ i + 1 }</td>
                <td>${ row.patient.name }</td>
                <td>${ row.visit_time }</td>
                <td>${ visitReason }</td>
                <td>${ status }</td>
                <td style="text-align: center;">
                    <a href="/appointments/detail/${ row.uuid }" class="btn btn-sm btn-primary">See Details</a>
                </td>
            </tr>
        `;

        $("#appointmentRows").append(html);
    }

    window.getList = getList;

    $(document).ready(function() {
        fromDTPicker = new TempusDominus(document.getElementById("fromDate"), tDConfigsWithTime);
        toDTPicker = new TempusDominus(document.getElementById("toDate"), tDConfigsWithTime);
        toDTPicker.disable();
        toDTPicker.updateOptions({
            useCurrent: false
        })
        $("#fromDate").on("change.td", function(e) {
            toDTPicker.enable();
            toDTPicker.updateOptions({
                restrictions: {
                    minDate: e.detail.date
                }
            })
        });

        getList();

        $("#tableForm").submit(function(e) {
            e.preventDefault();
            getList();
        });
        $("#resetFilterBtn").click(function(e) {
            $("#tableForm").trigger("reset");
            fromDTPicker.dates.setValue(null);
            toDTPicker.dates.setValue(null);
            toDTPicker.disable();
            getList();
        });
    });
</script>