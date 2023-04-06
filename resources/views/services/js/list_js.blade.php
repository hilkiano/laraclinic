<script type="module">
    const _liveToast = document.getElementById("liveToast");
    const _serviceModal = document.getElementById("serviceModal");
    const _confirmModal = document.getElementById("confirmModal");
    const serviceModalSubmitBtn = document.getElementById("serviceModalSubmitBtn");
    const confirmModalSubmitBtn = document.getElementById("confirmModalSubmitBtn");
    let serviceModal;
    let confirmModal;
    let liveToast;
    let buyPriceImask;
    let sellPriceImask;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const getList = async (p) => {
        showTableLoading(5, "#serviceRows");
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

        await fetch("/api/v1/services/list", {
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
            $("#serviceRows").empty();
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

                $("#serviceRows").append(html);
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
                <td>${ row.package ? row.package : '-' }</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary me-1" data-row='${ JSON.stringify(row) }' onclick="window.handleEdit(event)">Edit</button>
                    ${ getDelResButton(row) }
                </td>
            </tr>
        `;

        function getDelResButton(row) {
            let html = '';
            if (row.deleted_at) {
                html = `<button class="btn btn-sm btn-outline-success" onclick="window.showConfirmModal(${row.id}, '${row.label}', false)">Restore</button>`;
            } else {
                html = `<button class="btn btn-sm btn-outline-danger" onclick="window.showConfirmModal(${row.id}, '${row.label}', true)">Delete</button>`;
            }

            return html;
        }

        $("#serviceRows").append(html);
    }

    const showModalAdd = (e) => {
        $("#serviceModalHead").html("Add Service");
        serviceModal.toggle();
    }

    const showConfirmModal = (id, label, isDelete) => {
        $("#confirmModalHead").html(isDelete ? 'Delete Service' : 'Restore Service');
        $("#confirmModalBody").html(
            isDelete ?
            `<p>Do you want to delete this service?</p><div class="p-3 rounded bg-body-secondary">${label}</div>` :
            `<p>Do you want to restore this service?</p><div class="p-3 rounded bg-body-secondary">${label}</div>`
        );

        $("#confirmModalSubmitBtn").attr('data-id', id);

        confirmModal.toggle();
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData($("#serviceForm")[0]);
        serviceModalSubmitBtn.classList.add("disabled");
        serviceModalSubmitBtn.insertAdjacentHTML(
            "afterbegin",
            '<div id="spinner" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        // Update value for buy price and sell price to use 
        formData.set('buy_price', buyPriceImask.unmaskedValue);
        formData.set('sell_price', sellPriceImask.unmaskedValue);

        await fetch(`/api/v1/services/save`, {
                headers: {
                    Accept: "application/json, text-plain, */*",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                method: "post",
                credentials: "same-origin",
                body: formData
            })
            .then((response) => {
                serviceModalSubmitBtn.classList.remove("disabled");
                document.getElementById("spinner").remove();

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
            })
            .then((response) => {
                showToast(response.message, false);
                $("#filterCol").val("sku");
                $("#filterVal").val(response.data.sku);
                $("#applyFilterBtn").click();
                serviceModal.toggle();
            })
            .catch((error) => {
                showToast(error, true);
            });
    }

    const handleConfirm = async (e) => {
        const formData = new FormData();
        confirmModalSubmitBtn.classList.add("disabled");
        confirmModalSubmitBtn.insertAdjacentHTML(
            "afterbegin",
            '<div id="spinner" class="spinner-grow spinner-grow-sm me-2"></div>'
        );

        formData.set('id', $("#confirmModalSubmitBtn").attr("data-id"));

        await fetch(`/api/v1/services/delete-restore`, {
                headers: {
                    Accept: "application/json, text-plain, */*",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                method: "post",
                credentials: "same-origin",
                body: formData
            })
            .then((response) => {
                confirmModalSubmitBtn.classList.remove("disabled");
                document.getElementById("spinner").remove();
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
            })
            .then((response) => {
                showToast(response.message, false);
                $("#filterCol").val("sku");
                $("#filterVal").val(response.data.sku);
                $("#applyFilterBtn").click();
                confirmModal.toggle();
            })
            .catch((error) => {
                showToast(error, true);
            });
    }

    const handleEdit = (e) => {
        const data = JSON.parse(e.target.getAttribute("data-row"));
        $("#serviceModalHead").html("Update Service");

        buyPriceImask = new IMask(document.getElementById("buy_price"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });
        sellPriceImask = new IMask(document.getElementById("sell_price"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });

        $("#id").val(data.id);
        $("#name").val(data.label);
        $("#package").val(data.package);
        $("#description").val(data.description ? data.description : null);
        if (data.buy_price) {
            $("#buy_price").val(data.buy_price);
            buyPriceImask.typedValue = data.buy_price;
            buyPriceImask.updateValue();
            $("#buy_price").trigger("input");
        }

        $("#sell_price").val(data.sell_price);
        sellPriceImask.typedValue = data.sell_price;
        sellPriceImask.updateValue();
        $("#sell_price").trigger("input");
        serviceModal.toggle();
    }

    window.showConfirmModal = showConfirmModal;
    window.handleEdit = handleEdit;

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
        serviceModal = new bootstrap.Modal(_serviceModal);
        confirmModal = new bootstrap.Modal(_confirmModal);

        buyPriceImask = IMask(document.getElementById("buy_price"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });
        sellPriceImask = IMask(document.getElementById("sell_price"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });

        getList();

        _serviceModal.addEventListener("hide.bs.modal", function(e) {
            $("#serviceForm")[0].reset();
            $("#id").val(null);
        });
        $("#tableForm").submit(function(e) {
            e.preventDefault();
            getList();
        });
        $("#resetFilterBtn").click(function(e) {
            $("#tableForm").trigger("reset");
            getList();
        });
        $("#addServiceBtn").click(function(e) {
            showModalAdd(e);
        });
        $("#serviceForm").submit(function(e) {
            handleSubmit(e);
        });
        $("#confirmModalSubmitBtn").click(function(e) {
            handleConfirm(e);
        })
    });
</script>