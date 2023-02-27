<script type="module">
    // Elements
    const filterRolesBtn = document.getElementById("filterRolesBtn");
    const filterRolesField = document.getElementById("filterRolesField");
    const clearFilterRolesBtn = document.getElementById("clearFilterRolesBtn");
    const chgStateRoleHandler = document.getElementById("chgStateRoleHandler");
    const chgStateRoleModal = document.getElementById("chgStateRoleModal");
    const liveToast = document.getElementById("liveToast");
    const rolesModal = document.getElementById("rolesModal");
    const rolesForm = document.getElementById("rolesForm");
    let menuSelector;
    let privilegeSelector;
    if (
        document.getElementById("menu_ids") &&
        document.getElementById("privilege_ids")
    ) {
        const $menuSelect = $("#menu_ids").selectize({
            plugins: ["remove_button"],
            onDropdownOpen: function() {
                privilegeSelector.blur();
            },
        });
        const $privilegeSelect = $("#privilege_ids").selectize({
            plugins: ["remove_button"],
            onDropdownOpen: function() {
                menuSelector.blur();
            },
        });
        menuSelector = $menuSelect[0].selectize;
        privilegeSelector = $privilegeSelect[0].selectize;
    }

    const url = new URL(window.location.href);

    const handleFilter = () => {
        if (filterRolesField.value !== "") {
            if (url.searchParams.has("page")) {
                url.searchParams.delete("page");
            }
            url.searchParams.set("filter", filterRolesField.value);
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
    const updateRoleStateModal = (evt) => {
        const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
        chgStateRoleHandler.dataset.row =
            evt.relatedTarget.getAttribute("data-row");
        if (rowData) {
            document.getElementById("chgStateRoleModalHead").innerHTML =
                rowData.deleted_at ? "Activate Role" : "Deactivate Role";
            document.getElementById("chgStateRoleModalDetail").innerHTML =
                rowData.deleted_at ?
                `activate <strong>${rowData.name}</strong>` :
                `deactivate <strong>${rowData.name}</strong>`;
        } else {
            console.error("No row data.");
        }
    };
    const handleStateRole = async () => {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const rowData = JSON.parse(chgStateRoleHandler.getAttribute("data-row"));
        const requestBody = {
            id: rowData.id,
            type: rowData.deleted_at ? "activate" : "deactivate",
        };
        const req = await fetch("/api/v1/master/roles/change-state", {
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
                document
                    .getElementById("errorToastHeader")
                    .classList.add("d-block");
                document
                    .getElementById("successToastHeader")
                    .classList.add("d-none");
                const errToast = new bootstrap.Toast(liveToast);
                document.getElementById("toastBody").innerHTML = response.message;
                errToast.show();
            }
        }
    };
    const updateHtmlRolesModal = (evt) => {
        const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
        if (rowData) {
            document.getElementById(
                "rolesModalHead"
            ).innerHTML = `Edit Role: ${rowData.name}`;
            document.getElementById("role_id").value = rowData.id;
            document.getElementById("name").value = rowData.name;
            document.getElementById("description").value = rowData.description;
            menuSelector.setValue(rowData.menu_ids.map((n) => n.toString()));
            privilegeSelector.setValue(
                rowData.privilege_ids.map((n) => n.toString())
            );
        } else {
            document.getElementById("rolesModalHead").innerHTML = "Add Role";
        }
    };
    const submitHandler = async (event) => {
        event.preventDefault();
        const submitBtn = document.getElementById("rolesModalSubmitBtn");
        submitBtn.classList.add("disabled");
        submitBtn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const requestBody = new FormData(rolesForm);
        const req = await fetch("/api/v1/master/roles/save", {
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
                url.searchParams.set("filter", response.data.name);
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
    const handleError = (errors, validation = false) => {
        if (validation) {
            for (let error in errors) {
                document.getElementById(error).classList.add("is-invalid");
            }
        }
    };

    // Elements events
    if (filterRolesBtn) {
        filterRolesBtn.addEventListener("click", handleFilter);
    }
    if (filterRolesField) {
        filterRolesField.addEventListener("keyup", function(evt) {
            if (evt.key === "Enter") {
                filterRolesBtn.click();
            }
        });
    }
    if (chgStateRoleModal) {
        chgStateRoleModal.addEventListener("show.bs.modal", updateRoleStateModal);
    }
    if (chgStateRoleHandler) {
        chgStateRoleHandler.addEventListener("click", handleStateRole);
    }
    if (clearFilterRolesBtn) {
        clearFilterRolesBtn.addEventListener("click", clearFilter);
    }
    if (rolesModal) {
        rolesModal.addEventListener("show.bs.modal", updateHtmlRolesModal);
        rolesModal.addEventListener("hidden.bs.modal", function(evt) {
            rolesForm.reset();
            menuSelector.clear();
            privilegeSelector.clear();
        });
    }
    if (rolesForm) {
        rolesForm.addEventListener("submit", submitHandler);
    }

    $(document).ready(function() {
        if (filterRolesField) {
            if (url.searchParams.has("filter")) {
                filterRolesField.value = url.searchParams.get("filter");
            }
        }
    });
</script>