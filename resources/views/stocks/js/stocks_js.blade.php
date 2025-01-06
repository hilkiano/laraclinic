<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const downloadTemplateBtn = document.getElementById("downloadTemplateBtn");
    let liveToast;

    // FUNCTIONS
    const getList = async (p = 0) => {
        // Define parameters
        const param = {
            limit: 20,
            page: p,
            filter_col: "medicine",
            filter_val: $("#filterName").val()
        }

        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }

        await fetch("/api/v1/stocks/list", {
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
            // Reset stock rows content and fill with response body
            $("#stockRows").empty();
            if (response.data.length > 0) {
                response.data.map((row, i) => iteratePaginationData(p, row, i));
                $("#allCount").html(`${response.count}`);
                $("#pagination").html(makePagination(response));
            } else {
                let html = `
                    <tr>
                        <td colspan="5">No Data.</td>
                    </tr>
                `;

                $("#stockRows").append(html);
            }
        })
    }
    window.getList = getList;

    const iteratePaginationData = (page, row, i) => {
        const num = parseInt(page) * 20;
        const iteration = i + 1;
        let html;
        html += `
            <tr>
                <td scope="row">${ new Intl.NumberFormat().format(num + iteration) }</td>
                <td>${moment(row.created_at).format(
            "YYYY-MM-DD HH:mm:ss")}</td>
                <td>${ row.medicine.label }</td>
                <td>${ new Intl.NumberFormat().format(row.base_quantity) }</td>
                <td>${ new Intl.NumberFormat().format(row.quantity_out) }</td>
            </tr>
        `;

        $("#stockRows").append(html);
    }

    const getTemplate = async () => {
        downloadTemplateBtn.classList.add("disabled");
        await fetch('/api/v1/stocks/register', {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "get",
            credentials: "same-origin",
        }).then(response => {
            downloadTemplateBtn.classList.remove("disabled");
            if (!response.ok) {
                throw new Error("Download template error")
            }

            return response.blob();
        }).then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `stock_registration.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }).catch(error => {
            showToast(error, true)
        })
    }

    const handleUpload = async () => {
        const formData = new FormData();
        formData.append("file", document.getElementById("templateFile").files[0])

        await fetch('/api/v1/stocks/register', {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: formData
        }).then(response => {
            if (!response.ok) {
                throw new Error("Failed while execute registration");
            }

            return response.json();
        }).then(response => {
            // TODO: Begin progress with broadcast
            console.log("res", response);
            document.getElementById("templateFile").value = '';
        }).catch(error => {
            document.getElementById("templateFile").value = '';
            showToast(error, true);
        })

        // Clear the field

    }

    // TOAST
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

        $("#downloadTemplateBtn").click(function(e) {
            getTemplate();
        });

        $("#templateFile").change(function(e) {
            const fileInput = document.getElementById('templateFile');
            const uploadBtn = document.getElementById('uploadBtn');

            if (fileInput.files.length > 0) {
                uploadBtn.disabled = false;
            } else {
                uploadBtn.disabled = true;
            }
        });

        $("#uploadBtn").click(function(e) {
            handleUpload();
        });

        $("#resetFilterBtn").click(function(e) {
            $("#filterForm").each(function() {
                this.reset();
            });

            getList();
        });

        $("#filterForm").submit(function(e) {
            e.preventDefault();

            getList();
        })
    });
</script>
