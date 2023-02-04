<script type="module">
    const filterField = document.getElementById("filterField");
    const filterBtn = document.getElementById("filterBtn");
    const clearFilterBtn = document.getElementById("clearFilterBtn");
    const statusFilter = document.getElementById("statusFilter");
    const reasonFilter = document.getElementById("reasonFilter");
    const resetFilterBtn = document.getElementById("resetFilterBtn");
    const url = new URL(window.location.href);

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

    $(document).ready(function() {

    });
</script>