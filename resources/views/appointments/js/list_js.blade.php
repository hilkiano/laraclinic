<script type="module">
    const filterField = document.getElementById("filterField");
    const filterBtn = document.getElementById("filterBtn");
    const clearFilterBtn = document.getElementById("clearFilterBtn");
    const statusFilter = document.getElementById("statusFilter");
    const reasonFilter = document.getElementById("reasonFilter");
    const resetFilterBtn = document.getElementById("resetFilterBtn");
    const appointmentModal = document.getElementById("appointmentsModal");
    const appointmentForm = document.getElementById("appointmentForm");
    const liveToast = document.getElementById("liveToast");
    const url = new URL(window.location.href);
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    let patientSelector;
    let visitTimePicker;
    let assignmentSelector;
    let appointmentModalEl;

    const handleFilter = () => {
        if (filterField.value !== "") {
            if (url.searchParams.has("page")) {
                url.searchParams.delete("page");
            }
            url.searchParams.set("filter", filterField.value);
            window.location = url.href;
        }
    };
    filterBtn.addEventListener("click", handleFilter);
    filterField.addEventListener("keyup", function(evt) {
        if (evt.key === "Enter") {
            filterBtn.click();
        }
    });
    if (url.searchParams.has("filter")) {
        filterField.value = url.searchParams.get("filter");
    }

    const clearFilter = () => {
        if (url.searchParams.has("filter")) {
            url.searchParams.delete("filter");
            window.location = url.href;
        } else {
            console.error("Filter value is not exist in URL.");
        }
    };
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener("click", clearFilter);
    }

    const handleStatus = (evt) => {
        if (url.searchParams.has("status")) {
            if (url.searchParams.get("status") === evt.target.value) {
                return false;
            }
            logicHandleStatus(evt);
        } else {
            logicHandleStatus(evt);
        }
    }
    const logicHandleStatus = (evt) => {
        if (evt.target.value !== "all") {
            if (url.searchParams.has("page")) {
                url.searchParams.delete("page");
            }
            url.searchParams.set("status", evt.target.value);
            window.location = url.href;
        } else {
            url.searchParams.delete("status");
            window.location = url.href;
        }
    }
    statusFilter.addEventListener("change", handleStatus);
    if (url.searchParams.has("status")) {
        statusFilter.value = url.searchParams.get("status");
    }

    const handleReason = (evt) => {
        if (url.searchParams.has("reason")) {
            if (url.searchParams.get("reason") === evt.target.value) {
                return false;
            }
            logicHandleReason(evt);
        } else {
            logicHandleReason(evt);
        }
    }
    const logicHandleReason = (evt) => {
        if (evt.target.value !== "all") {
            if (url.searchParams.has("page")) {
                url.searchParams.delete("page");
            }
            url.searchParams.set("reason", evt.target.value);
            window.location = url.href;
        } else {
            url.searchParams.delete("reason");
            window.location = url.href;
        }
    }
    reasonFilter.addEventListener("change", handleReason);
    if (url.searchParams.has("reason")) {
        reasonFilter.value = url.searchParams.get("reason");
    }

    const handleReset = () => {
        url.searchParams.delete("page");
        url.searchParams.delete("filter");
        url.searchParams.delete("status");
        url.searchParams.delete("reason");

        window.location = url.href;
    }
    resetFilterBtn.addEventListener("click", handleReset);

    const openModal = () => {
        appointmentForm.reset();
        const $patientSelector = $('#patient_id').selectize({
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            options: [],
            onChange: function() {
                $("#patient_id").removeClass("is-invalid");
            },
            load: function(query, callback) {
                if (!query.length) return callback([]);
                $.ajax({
                    type: 'get',
                    url: `/api/v1/appointment/patient-list/${query}`,
                    headers: {
                        Accept: "application/json, text-plain, */*",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    dataType: 'json',
                    error: function() {
                        callback([]);
                    },
                    success: function(res) {
                        callback(res.data);
                    }
                })
            }
        });
        patientSelector = $patientSelector[0].selectize;
    }

    appointmentModal.addEventListener("show.bs.modal", openModal);

    const handleSubmit = async (e) => {
        const btn = e.target;
        btn.classList.add("disabled");
        btn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const requestBody = new FormData(appointmentForm);
        const req = await fetch("/api/v1/appointment/make", {
            headers: {
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: requestBody
        });
        const response = await req.json();
        if (response) {
            const toast = new bootstrap.Toast(liveToast);
            btn.classList.remove("disabled");
            document.getElementById("submitLoading").remove();
            if (response.status) {
                const url = new URL(window.location.origin);
                url.pathname = 'appointments/list';

                window.location = url.href;
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
                } else if (response.message instanceof Object) {
                    handleError(response.message);
                }
            }
        }
    }

    const handleError = (errors) => {
        for (let error in errors) {
            document.getElementById(error).classList.add("is-invalid");
        }
    };

    $(document).ready(function() {
        appointmentModalEl = new bootstrap.Modal(appointmentModal);
        visitTimePicker = new TempusDominus(document.getElementById("visit_time"), tDConfigsWithTime);
        $("#visit_time").on('change.td', function(e) {
            $("#visit_time").removeClass("is-invalid");
        });

        const $assignmentSelector = $("#reason").selectize({
            onChange: function() {
                $("#reason").removeClass("is-invalid");
            }
        });
        assignmentSelector = $assignmentSelector[0].selectize;

        $("#appointmentsModalSubmitBtn").click(function(e) {
            handleSubmit(e);
        });
    });
</script>