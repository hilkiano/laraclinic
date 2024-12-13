<script type="module">
    const _liveToast = document.getElementById("liveToast");
    const _receiptModal = document.getElementById("receiptModal");
    const printBtn = document.getElementById("receiptModalPrintBtn");
    let receiptModal;
    if (_receiptModal) {
        receiptModal = new bootstrap.Modal("#receiptModal", {});
    }
    let liveToast;
    let tableData;
    let fromDTPicker;
    let toDTPicker;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    // Get transaction list
    const getList = async (p) => {
        showTableLoading(7, "#trxRows");
        const page = p ? p : 0;
        const param = {
            limit: 25,
            page: page ? page : 0,
            patient_name: $("#patientName").val() !== "" ? $("#patientName").val() : undefined
        };
        if (fromDTPicker.dates.lastPicked && toDTPicker.dates.lastPicked) {
            param['startDate'] = moment.parseZone(fromDTPicker.dates.lastPicked).startOf('day').utc().format();
            param['endDate'] = moment.parseZone(toDTPicker.dates.lastPicked).endOf('day').utc().format();
        }
        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }

        await fetch("/api/v1/transactions/list", {
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
            $("#trxRows").empty();
            if (response.data.length > 0) {
                tableData = response.data;
                response.data.map((row, i) => iteratePaginationData(page, row, i));
                $("#allCount").html(`${response.count}`);
                $("#pagination").html(makePagination(response));
            } else {
                let html = `
                    <tr>
                        <td colspan="7">No Data.</td>
                    </tr>
                `;

                $("#trxRows").append(html);
            }

            // Update summary
            $("#cashTotal").html(`Rp ${response.summary.cash.toLocaleString('id-ID')}`);
            $("#transferTotal").html(`Rp ${response.summary.transfer.toLocaleString('id-ID')}`);
            $("#debitTotal").html(`Rp ${response.summary.debit.toLocaleString('id-ID')}`);
            $("#ccTotal").html(`Rp ${response.summary.cc.toLocaleString('id-ID')}`);
            $("#changeTotal").html(`-Rp ${response.summary.change.toLocaleString('id-ID')}`)
        }).catch(error => {
            showToast(error, true);
        })
    }

    // Handle download
    const handleDownload = async () => {
        let startDate;
        let endDate;
        let formattedStartDate;
        let formattedEndDate;

        if (fromDTPicker.dates.lastPicked && toDTPicker.dates.lastPicked) {
            // Diff
            const start = moment.parseZone(fromDTPicker.dates.lastPicked).startOf('day');
            const end = moment.parseZone(toDTPicker.dates.lastPicked).endOf('day');

            if (end.diff(start, 'months') > 1) {
                showToast("Download detailed report only support maximum 3 months range.", true);

                return false;
            }

            startDate = moment.parseZone(fromDTPicker.dates.lastPicked).startOf('day').utc().format();
            endDate = moment.parseZone(toDTPicker.dates.lastPicked).endOf('day').utc().format();
            formattedStartDate = moment.parseZone(fromDTPicker.dates.lastPicked).startOf('day').format(
                "YYYY-MM-DD_HH:mm:ss");
            formattedEndDate = moment.parseZone(toDTPicker.dates.lastPicked).endOf('day').format(
                "YYYY-MM-DD_HH:mm:ss");
        }

        await fetch(`/api/v1/transactions/report?startDate=${startDate}&endDate=${endDate}`, {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "get",
            credentials: "same-origin",
        }).then(response => {
            if (!response.ok) {
                throw new Error("Failed download data");
            }
            return response.blob();
        }).then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `TrxDetailReport_${formattedStartDate}-${formattedEndDate}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }).catch(error => {
            showToast(error, true);
        })
    }

    // Iterate from getList and returns row html
    const iteratePaginationData = (page, row, i) => {
        const num = page * 10;
        const iteration = i + 1;
        let html;
        html += `
            <tr class="${row.source === "APPOINTMENT" ? 'table-primary' : row.source === "SELF" ? 'table-danger' : row.source === "ONLINE" ? 'table-warning' : 'table-secondary'}">
                <td scope="row">${ row.id }</td>
                <td>${ row.created_at }</td>
                <td>${ row.patient_name ? row.patient_name : '<span class="fst-italic text-muted">Guest</span>' }</td>
                <td><button class="btn btn-sm btn-outline-primary" onclick="window.showReceipt(${i})">Cart Content</button></td>
                <td>${ row.total_amount }</td>
                <td>${ getPaymentType(row) }</td>
                <td>${ row.additional_info ? row.additional_info : '-' }</td>
            </tr>
        `;

        $("#trxRows").append(html);
    }

    const getPaymentType = (row) => {
        if (row.payment_details) {
            return row.payment_details.map(detail => {
                if (detail["payment-with"] === "CASH") {
                    return "Cash";
                } else if (detail["payment-with"] === "CREDIT_CARD") {
                    return "Credit Card"
                } else if (detail["payment-with"] === "DEBIT_CARD") {
                    return "Debit Card"
                } else if (detail["payment-with"] === "BANK_TRANSFER") {
                    return "Bank Transfer"
                }
            }).join(", ");
        } else {
            return row.payment_type
        }
    }

    // Show toast message
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

    const showReceipt = (tableRowIndex) => {
        const hasData = tableData[tableRowIndex].prescription[0].hasOwnProperty("data");
        let prescription;
        if (hasData) {
            prescription = tableData[tableRowIndex].prescription[0].data;
        } else {
            prescription = tableData[tableRowIndex].prescription;
        }

        // add data attribute of trx id to print btn
        if (printBtn) {
            const myElement = document.querySelector('#receiptModalPrintBtn');
            myElement.dataset.trxId = tableData[tableRowIndex].id;

            // Hide print
            if (tableData[tableRowIndex].source === "ONLINE") {
                if (!myElement.classList.contains('d-none')) {
                    myElement.classList.add('d-none');
                }
            } else {
                if (myElement.classList.contains('d-none')) {
                    myElement.classList.remove('d-none');
                }
            }
        }

        let html = '';
        prescription.map((item, idx) => {
            const qtyNum = Number(item.qty);
            html += `
                <tr>
                    <td scope="row">${ idx + 1 }</td>
                    <td>${item.sku}</td>
                    <td>${item.label}</td>
                    <td>Rp ${item.price ? item.price.toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0}) : '0'}</td>
                    <td>${item.qty.toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0})}</td>
                    <td>${getDiscountHtml(item)}</td>
                    <td>${getSubTotal(item)}</td>
                </tr>
            `;
        });
        $("#itemRows").html(html);
        $("#totalDiscount").html(getTotalDiscountHtml(tableData[tableRowIndex]));
        $("#totalAmount").html(tableData[tableRowIndex].total_amount);
        $("#paidAmount").html(
            `Rp ${Number(tableData[tableRowIndex].payment_amount).toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0})}`
        );
        $("#changeAmount").html(
            `Rp ${Number(tableData[tableRowIndex].change).toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0})}`
        );
        receiptModal.toggle();
    }

    const getTotalDiscountHtml = (row) => {
        let html = '';

        if (row.discount_amount > 0) {
            if (row.discount_type === "pctg") {
                html = `${row.discount_amount}%`
            } else if (row.discount_type === "amt") {
                html =
                    `Rp ${row.discount_amount.toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0})}`;
            }
        } else {
            html = '-';
        }

        return html;
    }

    const getDiscountHtml = (item) => {
        let html = '';

        if (item.discount_value > 0) {
            if (item.discount_type === "pctg") {
                html = `${item.discount_value}%`;
            } else if (item.discount_type === "amt") {
                html =
                    `Rp ${item.discount_value.toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0})}`;
            }
        } else {
            html = '-'
        }

        return html;
    }

    const getSubTotal = (item) => {
        let html = '';
        let subTotal = 0;
        let discountAmt = 0;
        let itemPrice = item.price ? item.price : 0;
        if (item.discount_value > 0) {
            if (item.discount_type === "pctg") {
                discountAmt = item.price * (item.discount_value / 100);
            } else if (item.discount_type === "amt") {
                discountAmt = item.discount_value;
            }
            itemPrice = itemPrice - discountAmt;
        }

        subTotal = itemPrice * item.qty;

        html = `Rp ${subTotal.toLocaleString('id-ID', {style: 'decimal', minimumFractionDigits: 0})}`

        return html;
    }

    const sendPrintRequest = async (e) => {
        const trxId = e.target.getAttribute('data-trx-id');

        if (!trxId) {
            console.error("No Transaction ID");
            return false;
        }

        const param = {
            id: trxId
        };
        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }

        await fetch("/api/v1/dispatch-print", {
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
            // do nothing
        }).catch(error => {
            showToast(error, true);
        })
    }

    window.getList = getList;
    window.showReceipt = showReceipt;

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);
        fromDTPicker = new TempusDominus(document.getElementById("fromDate"), tDConfigsNoClear);
        toDTPicker = new TempusDominus(document.getElementById("toDate"), tDConfigsNoClear);
        fromDTPicker.updateOptions({
            restrictions: {
                maxDate: new DateTime().endOf('hours')
            }
        });
        toDTPicker.disable();
        toDTPicker.updateOptions({
            useCurrent: false
        })
        $("#fromDate").on("change.td", function(e) {
            toDTPicker.enable();
            toDTPicker.updateOptions({
                restrictions: {
                    minDate: e.detail.date,
                    maxDate: new DateTime().endOf('hours')
                }
            })
        });

        // set initial date
        fromDTPicker.dates.setValue(new DateTime());
        toDTPicker.dates.setValue(new DateTime());

        getList();

        $("#tableForm").submit(function(e) {
            e.preventDefault();
            getList();
        });
        $("#resetFilterBtn").click(function(e) {
            $("#tableForm").trigger("reset");
            fromDTPicker.dates.setValue(new DateTime());
            toDTPicker.dates.setValue(new DateTime());
            toDTPicker.disable();
            getList();
        });
        if (printBtn) {
            $("#receiptModalPrintBtn").click(function(e) {
                sendPrintRequest(e);
            });
        }

        $("#downloadBtn").click(function(e) {
            handleDownload();
        });
    });
</script>
