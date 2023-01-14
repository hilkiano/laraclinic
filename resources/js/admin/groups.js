// Elements
const filterGroupsBtn = document.getElementById("filterGroupsBtn");
const filterGroupsField = document.getElementById("filterGroupsField");
const clearFilterGroupsBtn = document.getElementById("clearFilterGroupsBtn");

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

(function () {
    if (filterGroupsField) {
        if (url.searchParams.has("filter")) {
            filterGroupsField.value = url.searchParams.get("filter");
        }
    }
})();
