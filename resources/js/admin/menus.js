// Elements
const filterMenuBtn = document.getElementById("filterMenusBtn");
const filterMenuField = document.getElementById("filterMenusField");
const clearFilterMenusBtn = document.getElementById("clearFilterMenusBtn");
const chgStateMenuHandler = document.getElementById("chgStateMenuHandler");
const chgStateMenuModal = document.getElementById("chgStateMenuModal");
const liveToast = document.getElementById("liveToast");
const menusModal = document.getElementById("menusModal");
const menusForm = document.getElementById("menusForm");
const parentCheck = document.getElementById("is_parent");
const parentSelect = document.getElementById("parent");

const url = new URL(window.location.href);

const handleFilter = () => {
    if (filterMenuField.value !== "") {
        url.searchParams.set("filter", filterMenuField.value);
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
const updateMenuStateModal = (evt) => {
    const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
    chgStateMenuHandler.dataset.row =
        evt.relatedTarget.getAttribute("data-row");
    if (rowData) {
        document.getElementById("chgStateMenuModalHead").innerHTML =
            rowData.deleted_at ? "Activate Menu" : "Deactivate Menu";
        document.getElementById("chgStateMenuModalDetail").innerHTML =
            rowData.deleted_at
                ? `activate <strong>${rowData.name}</strong>`
                : `deactivate <strong>${rowData.name}</strong>`;
    } else {
        console.error("No row data.");
    }
};
const handleStateMenu = async () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const rowData = JSON.parse(chgStateMenuHandler.getAttribute("data-row"));
    const requestBody = {
        id: rowData.id,
        type: rowData.deleted_at ? "activate" : "deactivate",
    };
    const req = await fetch("/api/v1/master/menus/change-state", {
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
        document.getElementById("errorToastHeader").classList.add("d-block");
        document.getElementById("successToastHeader").classList.add("d-none");
        if (liveToast) {
            const errToast = new bootstrap.Toast(liveToast);
            document.getElementById("toastBody").innerHTML = response.message;
            errToast.show();
        }
    }
};
const updateHtmlMenusModal = (evt) => {
    const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
    if (rowData) {
        document.getElementById(
            "menusModalHead"
        ).innerHTML = `Edit Menu: ${rowData.name}`;
        document.getElementById("menu_id").value = rowData.id;
        document.getElementById("name").value = rowData.name;
        document.getElementById("name").readOnly = true;
        document.getElementById("label").value = rowData.label;
        document.getElementById("icon").value = rowData.icon;
        document.getElementById("order").value = rowData.order;
        document.getElementById("route").value = rowData.route;
        document.getElementById("parent").value = rowData.parent
            ? rowData.parent
            : "";
        document.getElementById("is_parent").checked = rowData.is_parent;
        parentCheck.dispatchEvent(new Event("change"));
    } else {
        document.getElementById("menusModalHead").innerHTML = "Add Menu";
        document.getElementById("name").readOnly = false;
    }
};
const submitHandler = async (event) => {
    event.preventDefault();
    const submitBtn = document.getElementById("menusModalSubmitBtn");
    submitBtn.classList.add("disabled");
    submitBtn.insertAdjacentHTML(
        "afterbegin",
        '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
    );
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const requestBody = new FormData(menusForm);
    const req = await fetch("/api/v1/master/menus/save", {
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
if (filterMenuBtn) {
    filterMenuBtn.addEventListener("click", handleFilter);
}
if (filterMenuField) {
    filterMenuField.addEventListener("keyup", function (evt) {
        if (evt.key === "Enter") {
            filterMenuBtn.click();
        }
    });
}
if (clearFilterMenusBtn) {
    clearFilterMenusBtn.addEventListener("click", clearFilter);
}
if (chgStateMenuModal) {
    chgStateMenuModal.addEventListener("show.bs.modal", updateMenuStateModal);
}
if (chgStateMenuHandler) {
    chgStateMenuHandler.addEventListener("click", handleStateMenu);
}
if (menusModal) {
    menusModal.addEventListener("show.bs.modal", updateHtmlMenusModal);
    menusModal.addEventListener("hidden.bs.modal", function (evt) {
        menusForm.reset();
    });
}
if (menusForm) {
    menusForm.addEventListener("submit", submitHandler);
}
if (parentCheck && parentSelect) {
    parentCheck.addEventListener("change", function () {
        if (parentCheck.checked) {
            parentSelect.value = "";
            parentSelect.setAttribute("disabled", true);
        } else {
            parentSelect.removeAttribute("disabled");
        }
    });
}

(function () {
    if (filterMenuField) {
        if (url.searchParams.has("filter")) {
            filterMenuField.value = url.searchParams.get("filter");
        }
    }
})();
