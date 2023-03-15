<script type="module">
    const _liveToast = document.getElementById("liveToast");
    let liveToast;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const getList = async (p) => {
        showTableLoading(5, "#medicineRows");
        const page = p ? p : 0;
        const param = {
            limit: 10,
            page: page ? page : 0,
            filter_val: $("#filterVal").val() !== "" ? $("#filterVal").val() : undefined,
            filter_col: $("#filterCol").val()
        };
        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }

        await fetch("/api/v1/medicines/list", {
            headers: {
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: formData
        }).then(response => {
            if (!response.ok) {
                return response.json()
                    .catch(() => {
                        throw new Error(response.status);
                    })
                    .then(({
                        message
                    }) => {
                        throw new Error(message || response.status);
                    });
            }

            return response.json();
        }).then(response => {
            $("#medicineRows").empty();
            if (response.data.length > 0) {
                response.data.map((row, i) => iteratePaginationData(page, row, i));
                $("#allCount").html(`${response.count}`);
                $("#pagination").html(makePagination(response));
            } else {
                let html = `
                    <tr>
                        <td colspan="5">No Data.</td>
                    </tr>
                `;

                $("#medicineRows").append(html);
            }
        }).catch(error => {
            showToast(error, true);
        })
    }

    window.getList = getList;

    const iteratePaginationData = (page, row, i) => {
        const num = page * 10;
        const iteration = i + 1;
        let html;
        html += `
            <tr>
                <td scope="row">${ num + iteration }</td>
                <td>${ row.sku }</td>
                <td>${ row.label }</td>
                <td>${ row.package }</td>
                <td>${ row.category ? row.category : '-' }</td>
            </tr>
        `;

        $("#medicineRows").append(html);
    }

    const showToast = (text, isError = false) => {
        if (isError) {
            $("#errorToastHeader").removeClass("d-none").addClass("d-block");
            $("#successToastHeader").removeClass("d-block").addClass("d-none");
        } else {
            $("#errorToastHeader").removeClass("d-block").addClass("d-none");
            $("#successToastHeader").removeClass("d-none").addClass("d-block");
        }

        $("#toastBody").html(text);
        liveToast.show();
    }

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);

        getList();
        $("#tableForm").submit(function(e) {
            e.preventDefault();
            getList();
        });
        $("#resetFilterBtn").click(function(e) {
            $("#tableForm").trigger("reset");
            getList();
        });
    });
</script>