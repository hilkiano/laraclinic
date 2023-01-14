// Elements
const filterPrivilegesBtn = document.getElementById("filterPrivilegesBtn");
const filterPrivilegesField = document.getElementById("filterPrivilegesField");
const clearFilterPrivilegesBtn = document.getElementById(
    "clearFilterPrivilegesBtn"
);

const url = new URL(window.location.href);

const handleFilter = () => {
    if (filterPrivilegesField.value !== "") {
        if (url.searchParams.has("page")) {
            url.searchParams.delete("page");
        }
        url.searchParams.set("filter", filterPrivilegesField.value);
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
if (filterPrivilegesBtn) {
    filterPrivilegesBtn.addEventListener("click", handleFilter);
}
if (filterPrivilegesField) {
    filterPrivilegesField.addEventListener("keyup", function (evt) {
        if (evt.key === "Enter") {
            filterPrivilegesBtn.click();
        }
    });
}
if (clearFilterPrivilegesBtn) {
    clearFilterPrivilegesBtn.addEventListener("click", clearFilter);
}

(function () {
    if (filterPrivilegesField) {
        if (url.searchParams.has("filter")) {
            filterPrivilegesField.value = url.searchParams.get("filter");
        }
    }
})();
