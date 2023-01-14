// Elements
const filterMenuBtn = document.getElementById("filterMenusBtn");
const filterMenuField = document.getElementById("filterMenusField");
const clearFilterMenusBtn = document.getElementById("clearFilterMenusBtn");

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

(function () {
    if (filterMenuField) {
        if (url.searchParams.has("filter")) {
            filterMenuField.value = url.searchParams.get("filter");
        }
    }
})();
