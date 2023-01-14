// Elements
const filterRolesBtn = document.getElementById("filterRolesBtn");
const filterRolesField = document.getElementById("filterRolesField");
const clearFilterRolesBtn = document.getElementById("clearFilterRolesBtn");

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

// Elements events
if (filterRolesBtn) {
    filterRolesBtn.addEventListener("click", handleFilter);
}
if (filterRolesField) {
    filterRolesField.addEventListener("keyup", function (evt) {
        if (evt.key === "Enter") {
            filterRolesBtn.click();
        }
    });
}
if (clearFilterRolesBtn) {
    clearFilterRolesBtn.addEventListener("click", clearFilter);
}

(function () {
    if (filterRolesField) {
        if (url.searchParams.has("filter")) {
            filterRolesField.value = url.searchParams.get("filter");
        }
    }
})();
