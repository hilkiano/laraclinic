<script type="module">
    const uid = "{{ Auth::id() }}";
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'), {
        backdrop: 'static',
        keyboard: false
    })
    const editModal = new bootstrap.Modal(document.getElementById('editModal'), {})
    const downloadTemplateBtn = document.getElementById("downloadTemplateBtn");
    let liveToast;
    let medSelector;
    let qtyField;

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
                        <td colspan="7">No Data.</td>
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
            <tr class="${row.quantity_out >= row.base_quantity ? "table-danger" : ""}">
                <td scope="row">${ new Intl.NumberFormat().format(num + iteration) }</td>
                <td>${moment(row.created_at).format(
            "YYYY-MM-DD HH:mm:ss")}</td>
            <td>${moment(row.updated_at).format(
            "YYYY-MM-DD HH:mm:ss")}</td>
                <td>${ row.medicine.label }</td>
                <td>${ new Intl.NumberFormat().format(row.base_quantity) }</td>
                <td>${ new Intl.NumberFormat().format(row.quantity_out) }</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" data-row='${ JSON.stringify(row).replace(/[\']/g, "&apos;") }' onclick="window.handleEdit(event)" ${row.quantity_out >= row.base_quantity ? "disabled" : ""}>Edit</button>
                </td>
            </tr>
        `;

        $("#stockRows").append(html);
    }

    const seeHistories = (e) => {
        const data = JSON.parse(e.target.getAttribute("data-row"));
        console.log("histories", data)
    }
    window.seeHistories = seeHistories;

    const handleEdit = (e) => {
        const data = JSON.parse(e.target.getAttribute("data-row"));

        // Reset form
        document.getElementById("stockForm").reset();

        qtyField = IMask(document.getElementById("baseQuantity"), {
            mask: Number,
            max: 9999999999,
            scale: 0,
            thousandsSeparator: ',',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: '.',
        });

        $("#id").val(data.id);
        $("#baseQuantity").val(data.base_quantity);
        qtyField.typedValue = data.base_quantity;
        qtyField.updateValue();
        $("#baseQuantity").trigger("input");
        editModal.toggle();

        const $medSelector = $("#medicineId").selectize({
            valueField: "id",
            labelField: "label",
            searchField: "label",
            options: [],
            onChange: function(e) {
                $("#medicineId").removeClass("is-invalid");
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    type: 'get',
                    url: `/api/v1/stocks/medicine-list/${query}`,
                    headers: {
                        Accept: "application/json, text-plain, */*",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    dataType: 'json',
                    error: function() {
                        callback([]);
                    },
                    success: function(res) {
                        callback(res.data);
                    }
                })
            }
        })

        medSelector = $medSelector[0].selectize;
        medSelector.addOption({
            id: data.medicine_id,
            label: data.medicine.label
        });
        medSelector.setValue(data.medicine_id);
    }
    window.handleEdit = handleEdit;

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
                return response
                    .json()
                    .catch(() => {
                        throw new Error(response.status);
                    })
                    .then(({
                        message
                    }) => {
                        throw new Error(message || response.status);
                    });
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
                return response
                    .json()
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
            document.getElementById("templateFile").value = '';
            $("#templateFile").trigger("change");
        }).catch(error => {
            document.getElementById("templateFile").value = '';
            showToast(error, true);
            $("#templateFile").trigger("change");
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

    const handleProgress = (event) => {
        const progress = event.progress;
        const errorMsg = event.errorMsg;
        const isFinished = event.isFinished;

        $(".progress-bar").css("width", `${progress}%`).html(`${progress}%`);

        if (isFinished) {
            $("#dismissProgressBtn").prop("disabled", false);
        }

        if (errorMsg) {
            $("#errorMsg").html(errorMsg);
        }
    }

    const updateStock = async (payload) => {
        await fetch('/api/v1/stocks/save', {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json"
            },
            method: "post",
            credentials: "same-origin",
            body: JSON.stringify(payload)
        }).then(response => {
            if (!response.ok) {
                return response
                    .json()
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
            editModal.toggle();
            getList();
        }).catch(error => {
            showToast(error, true);
        })
    }

    // ECHO
    window.Echo.channel(`register_progress_${uid}`).listen(
        "RegisterProgress",
        (event) => {
            $("#dismissProgressBtn").prop("disabled", true);
            handleProgress(event)
        }
    );

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);

        getList();

        document.getElementById("editModal").addEventListener("hidden.bs.modal", function(e) {
            medSelector.destroy();
        });

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
            $(".progress-bar").css("width", `0%`).html(`0%`);
            $("#errorMsg").html('');
            progressModal.toggle();
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

        $("#stockForm").submit(function(e) {
            e.preventDefault();

            const payload = {
                id: parseInt($("#id").val()),
                medicine_id: parseInt(medSelector.getValue()),
                base_quantity: parseInt(qtyField.unmaskedValue)
            }

            updateStock(payload);
        })

        $("#dismissProgressBtn").click(function(e) {
            getList();
        });
    });
</script>
