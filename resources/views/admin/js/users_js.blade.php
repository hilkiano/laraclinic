<script type="module">
    // Elements
    const filterUsersBtn = document.getElementById("filterUsersBtn");
    const filterUsersField = document.getElementById("filterUsersField");
    const clearFilterUsersBtn = document.getElementById("clearFilterUsersBtn");
    const chgStateHandler = document.getElementById("chgStateHandler");
    const chgStateModal = document.getElementById("chgStateModal");
    const liveToast = document.getElementById("liveToast");
    const phoneNumberField = document.getElementById("phone_number");
    const usersModal = document.getElementById("usersModal");
    const usersForm = document.getElementById("usersForm");

    const url = new URL(window.location.href);

    const handleFilter = () => {
        if (filterUsersField.value !== "") {
            if (url.searchParams.has("page")) {
                url.searchParams.delete("page");
            }
            url.searchParams.set("filter", filterUsersField.value);
            window.location = url.href;
        }
    };
    const clearFilter = () => {
        if (url.searchParams.has("filter")) {
            url.searchParams.delete("filter");
            window.location = url.href;
        } else {
            console.error("Filter value is not exist in URL.");
        }
    };
    const updateUserStateModal = (evt) => {
        const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
        chgStateHandler.dataset.row = evt.relatedTarget.getAttribute("data-row");
        if (rowData) {
            document.getElementById("chgStateModalHead").innerHTML =
                rowData.deleted_at ? "Activate User" : "Deactivate User";
            document.getElementById("chgStateModalDetail").innerHTML =
                rowData.deleted_at ?
                `activate <strong>${rowData.username}</strong>` :
                `deactivate <strong>${rowData.username}</strong>`;
        } else {
            console.error("No row data.");
        }
    };
    const updateHtmlUsersModal = (evt) => {
        const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
        if (rowData) {
            document.getElementById(
                "usersModalHead"
            ).innerHTML = `Edit User: ${rowData.username}`;
            document.getElementById("user_id").value = rowData.id;
            document.getElementById("username").value = rowData.username;
            document.getElementById("username").readOnly = true;
            document.getElementById("name").value = rowData.name;
            document.getElementById("email").value = rowData.email;
            document.getElementById("phone_number").value = rowData.phone_number;
            document.getElementById("group").value = rowData.group_id;
        } else {
            document.getElementById("usersModalHead").innerHTML = "Add User";
            document.getElementById("username").readOnly = false;
        }
    };
    const handleStateUser = async () => {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const rowData = JSON.parse(chgStateHandler.getAttribute("data-row"));
        const requestBody = {
            id: rowData.id,
            type: rowData.deleted_at ? "activate" : "deactivate",
        };
        const req = await fetch("/api/v1/master/users/change-state", {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: JSON.stringify(requestBody),
        });
        const response = await req.json();
        if (response.status) {
            window.location.reload();
        } else {
            if (liveToast) {
                const errToast = new bootstrap.Toast(liveToast);
                document.getElementById("toastBody").innerHTML = response.message;
                errToast.show();
            }
        }
    };
    const submitHandler = async (event) => {
        resetFormClass();
        event.preventDefault();
        const submitBtn = document.getElementById("usersModalSubmitBtn");
        submitBtn.classList.add("disabled");
        submitBtn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const requestBody = new FormData(usersForm);
        const req = await fetch("/api/v1/master/users/save", {
            headers: {
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: requestBody,
        });
        const response = await req.json();
        if (response) {
            const toast = new bootstrap.Toast(liveToast);
            submitBtn.classList.remove("disabled");
            document.getElementById("submitLoading").remove();
            if (response.status) {
                const url = new URL(window.location.href);
                if (url.searchParams.has("page")) {
                    url.searchParams.delete("page");
                }
                url.searchParams.set("filter", response.data.username);
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
    };
    /**
     * Iterate errors and add invalid class to matching form fields
     */
    const handleError = (errors) => {
        for (let error in errors) {
            document.getElementById(error).classList.add("is-invalid");
        }
    };
    const resetFormClass = () => {
        document.getElementById("username").classList.remove("is-invalid");
        document.getElementById("name").classList.remove("is-invalid");
        document.getElementById("email").classList.remove("is-invalid");
        document.getElementById("phone_number").classList.remove("is-invalid");
    };

    // Elements events
    if (filterUsersBtn) {
        filterUsersBtn.addEventListener("click", handleFilter);
    }
    if (filterUsersField) {
        filterUsersField.addEventListener("keyup", function(evt) {
            if (evt.key === "Enter") {
                filterUsersBtn.click();
            }
        });
    }
    if (clearFilterUsersBtn) {
        clearFilterUsersBtn.addEventListener("click", clearFilter);
    }
    if (chgStateModal) {
        chgStateModal.addEventListener("show.bs.modal", updateUserStateModal);
    }
    if (chgStateHandler) {
        chgStateHandler.addEventListener("click", handleStateUser);
    }
    if (usersModal) {
        usersModal.addEventListener("show.bs.modal", updateHtmlUsersModal);
        usersModal.addEventListener("hidden.bs.modal", function(evt) {
            usersForm.reset();
        });
    }
    if (usersForm) {
        usersForm.addEventListener("submit", submitHandler);
    }

    // DOM Events
    $(document).ready(function() {
        if (filterUsersField) {
            if (url.searchParams.has("filter")) {
                filterUsersField.value = url.searchParams.get("filter");
            }
        }
        if (phoneNumberField && url.pathname === "/master/users") {
            IMask(phoneNumberField, {
                mask: Number,
                signed: false,
            });
        }
    });
</script>