<script type="module">
    const _liveToast = document.getElementById("liveToast");
    const _medicineModal = document.getElementById("medicineModal");
    const _confirmModal = document.getElementById("confirmModal");
    const medicineModalSubmitBtn = document.getElementById("medicineModalSubmitBtn");
    const confirmModalSubmitBtn = document.getElementById("confirmModalSubmitBtn");
    const canEdit = "{{ $canEdit }}" !== "" ? true : false;
    const canDelete = "{{ $canDelete }}" !== "" ? true : false;
    let medicineModal;
    let confirmModal;
    let liveToast;
    let buyPriceImask;
    let sellPriceImask;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const getList = async (p) => {
        showTableLoading(canEdit || canDelete ? 6 : 5, "#medicineRows");
        const page = p ? p : 0;
        const param = {
            limit: $("#itemPerPage").val(),
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
                        <td colspan="${ canEdit || canDelete ? 6 : 5 }">No Data.</td>
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
        const num = page * $("#itemPerPage").val();
        const iteration = i + 1;
        let html;
        html += `
            <tr>
                <td scope="row">${ num + iteration }</td>
                <td>${ row.sku }</td>
                <td>${ row.label }</td>
                <td>${ row.package }</td>
                <td>${ row.sell_price ? formatIdr(row.sell_price) : '-' }</td>
                ${ canEdit && canDelete ? `<td class="text-center">
                    ${ canEdit ? `<button class="btn btn-sm btn-outline-primary me-1" data-row='${ JSON.stringify(row) }' onclick="window.handleEdit(event)">Edit</button>` : '' }
                    ${ canDelete ? getDelResButton(row) : '' }
                </td>` : '' }
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

        $("#medicineRows").append(html);
    }

    const formatIdr = (num) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0
            }).format(num);
    }

    const showModalAdd = (e) => {
        $("#medicineModalHead").html("Add Medicine");
        medicineModal.toggle();
    }

    const showConfirmModal = (id, label, isDelete) => {
        $("#confirmModalHead").html(isDelete ? 'Delete Medicine' : 'Restore Medicine');
        $("#confirmModalBody").html(
            isDelete ?
            `<p>Do you want to delete this medicine?</p><div class="p-3 rounded bg-body-secondary">${label}</div>` :
            `<p>Do you want to restore this medicine?</p><div class="p-3 rounded bg-body-secondary">${label}</div>`
        );

        $("#confirmModalSubmitBtn").attr('data-id', id);

        confirmModal.toggle();
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

    const handleSubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData($("#medicineForm")[0]);
        medicineModalSubmitBtn.classList.add("disabled");
        medicineModalSubmitBtn.insertAdjacentHTML(
            "afterbegin",
            '<div id="spinner" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        // Update value for buy price and sell price to use
        formData.set('buy_price', buyPriceImask.unmaskedValue);
        formData.set('sell_price', sellPriceImask.unmaskedValue);

        await fetch(`/api/v1/medicines/save`, {
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
                medicineModalSubmitBtn.classList.remove("disabled");
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
                medicineModal.toggle();
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

        await fetch(`/api/v1/medicines/delete-restore`, {
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
        $("#medicineModalHead").html("Update Medicine");

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
        medicineModal.toggle();
    }

    window.showConfirmModal = showConfirmModal;
    window.handleEdit = handleEdit;

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);
        medicineModal = new bootstrap.Modal(_medicineModal);
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

        _medicineModal.addEventListener("hide.bs.modal", function(e) {
            $("#medicineForm")[0].reset();
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
        $("#addMedicineBtn").click(function(e) {
            showModalAdd(e);
        });
        $("#medicineForm").submit(function(e) {
            handleSubmit(e);
        });
        $("#confirmModalSubmitBtn").click(function(e) {
            handleConfirm(e);
        })
    });
</script>
