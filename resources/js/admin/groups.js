// Elements
const filterGroupsBtn = document.getElementById("filterGroupsBtn");
const filterGroupsField = document.getElementById("filterGroupsField");
const clearFilterGroupsBtn = document.getElementById("clearFilterGroupsBtn");
const chgStateGroupHandler = document.getElementById("chgStateGroupHandler");
const chgStateGroupModal = document.getElementById("chgStateGroupModal");
const liveToast = document.getElementById("liveToast");
const groupsModal = document.getElementById("groupsModal");
const groupsForm = document.getElementById("groupsForm");
let roleSelector;
if (document.getElementById("role_ids")) {
    const $roleSelect = $('#role_ids').selectize({
        plugins: ["remove_button"],
    });
    roleSelector = $roleSelect[0].selectize;
}

const url = new URL(window.location.href);

const handleFilter = () => {
    if (filterGroupsField.value !== "") {
        if (url.searchParams.has("page")) {
            url.searchParams.delete("page");
        }
        url.searchParams.set("filter", filterGroupsField.value);
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
const updateGroupStateModal = (evt) => {
    const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
    chgStateGroupHandler.dataset.row =
        evt.relatedTarget.getAttribute("data-row");
    if (rowData) {
        document.getElementById("chgStateGroupModalHead").innerHTML =
            rowData.deleted_at ? "Activate Group" : "Deactivate Group";
        document.getElementById("chgStateGroupModalDetail").innerHTML =
            rowData.deleted_at
                ? `activate <strong>${rowData.name}</strong>`
                : `deactivate <strong>${rowData.name}</strong>`;
    } else {
        console.error("No row data.");
    }
};
const handleStateGroup = async () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const rowData = JSON.parse(chgStateGroupHandler.getAttribute("data-row"));
    const requestBody = {
        id: rowData.id,
        type: rowData.deleted_at ? "activate" : "deactivate",
    };
    const req = await fetch("/api/v1/master/groups/change-state", {
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
            document.getElementById("errorToastHeader").classList.add("d-block");
            document.getElementById("successToastHeader").classList.add("d-none");
            const errToast = new bootstrap.Toast(liveToast);
            document.getElementById("toastBody").innerHTML = response.message;
            errToast.show();
        }
    }
};
const updateHtmlGroupsModal = (evt) => {
    const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
    if (rowData) {
        document.getElementById(
            "groupsModalHead"
        ).innerHTML = `Edit Group: ${rowData.name}`;
        document.getElementById("group_id").value = rowData.id;
        document.getElementById("name").value = rowData.name;
        document.getElementById("description").value = rowData.description;
        roleSelector.setValue(rowData.role_ids.map(n => n.toString()));
    } else {
        document.getElementById("groupsModalHead").innerHTML = "Add Group";
    }
};
const submitHandler = async (event) => {
    event.preventDefault();
    const submitBtn = document.getElementById("groupsModalSubmitBtn");
    submitBtn.classList.add("disabled");
    submitBtn.insertAdjacentHTML(
        "afterbegin",
        '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
    );
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const requestBody = new FormData(groupsForm);
    const req = await fetch("/api/v1/master/groups/save", {
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

// Elements events
if (filterGroupsBtn) {
    filterGroupsBtn.addEventListener("click", handleFilter);
}
if (filterGroupsField) {
    filterGroupsField.addEventListener("keyup", function (evt) {
        if (evt.key === "Enter") {
            filterGroupsBtn.click();
        }
    });
}
if (clearFilterGroupsBtn) {
    clearFilterGroupsBtn.addEventListener("click", clearFilter);
}
if (chgStateGroupModal) {
    chgStateGroupModal.addEventListener("show.bs.modal", updateGroupStateModal);
}
if (chgStateGroupHandler) {
    chgStateGroupHandler.addEventListener("click", handleStateGroup);
}
if (groupsModal) {
    groupsModal.addEventListener("show.bs.modal", updateHtmlGroupsModal);
    groupsModal.addEventListener("hidden.bs.modal", function (evt) {
        groupsForm.reset();
        roleSelector.clear();
    });
}
if (groupsForm) {
    groupsForm.addEventListener("submit", submitHandler);
}

(function () {
    if (filterGroupsField) {
        if (url.searchParams.has("filter")) {
            filterGroupsField.value = url.searchParams.get("filter");
        }
    }
})();
