<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const _approvalModal = new bootstrap.Modal("#approvementModal", {});
    const _cancelModal = document.getElementById("cancelAssignmentModal");
    let cancelModal;
    let liveToast;
    if (_cancelModal) {
        cancelModal = new bootstrap.Modal('#cancelAssignmentModal', {});
        _cancelModal.addEventListener("hidden.bs.modal", function(e) {
            $("#cancelAssignmentForm")[0].reset();
        });
    }
    let prescription;
    let assignedUuid;
    let discountAmtImask;
    let discountPctgImask;
    let fullPrice;
    let amountChange;
    let itemLength = 0;
    let itemImask;
    let fromDTPicker;
    let toDTPicker;

    const getMyAssignment = async (onlyCards = false) => {
        setLoading(true);
        const [listCards, assigned] = await Promise.all([getListCards(), getAssigned()]);
        if (listCards) {
            setLoading(false);
            if (listCards.data.length > 0) {
                makeCards(listCards.data);
            } else {
                const emptyCard = `
                    <div class="card w-100 bg-body-secondary border-0">
                        <div class="card-body p-0" style="height: calc(100vh - 320px)">
                            <div class="row h-100">
                                <div class="col-12 d-flex justify-content-center align-items-center">
                                    <p class="fs-3 text-muted">No cards yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $("#myList").html(emptyCard);
            }
        }
        if (assigned.data && !onlyCards) {
            $(".take-assignment-btn").addClass("disabled");
            updateMainContent(assigned.data);
            $("#cancelBtn").attr("data-uuid", assigned.data.uuid);
            $("#submitBtn").attr("data-uuid", assigned.data.uuid);
            assignedUuid = assigned.data.uuid;
            updateRxBody(assignedUuid);
            initiateItemImask(itemLength);
            setTakeLoading(false);
            checkAmountPaid();
        } else if (assigned.data && onlyCards) {
            $(".take-assignment-btn").addClass("disabled");
        }
    }

    const getListCards = async () => {
        let url = `/api/v1/appointment/mine`;
        const params = new URLSearchParams();
        const name = $("#filterName").val() !== "" ? $("#filterName").val() : null;
        const fromDate = moment.parseZone(fromDTPicker.dates.lastPicked).startOf('day').utc().format();
        const toDate = moment.parseZone(toDTPicker.dates.lastPicked).endOf('day').utc().format();

        if (name) {
            params.append("name", name);
        }
        params.append("from", fromDate);
        params.append("to", toDate);

        url += '?' + params.toString();

        return await fetch(url, {
            headers: {
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "get",
            credentials: "same-origin"
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
            return response;
        }).catch(error => {
            showToast(error, true);
            return null;
        })
    }

    const getAssigned = async () => {
        const formData = new FormData();
        const param = {
            pic: '{{ auth()->id() }}',
            status: 'IN_PAYMENT'
        };
        for (var key in param) {
            formData.append(key, param[key]);
        }
        return await fetch(`/api/v1/appointment/get-assignation`, {
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
            return response;
        }).catch(error => {
            showToast(error, true);
            return null;
        })
    }

    const makeCards = (data) => {
        let html = '';

        data.map((row, i) => {
            html += `
            <div class="card mb-4 shadow flex-shrink-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                    <div class="row">
                        <div class="col d-flex flex-grow-0">
                            <i class="me-2 bi ${getIcon(row.status)} fs-4 text-primary"></i><span class="text-primary fs-4">${row.daily_code}</span>
                        </div>
                        <div class="col d-flex justify-content-end align-items-center gap-2 flex-grow-1">
                            <button onclick="window.cancelAssignment(event)" data-uuid="${row.uuid}" class="btn btn-sm btn-outline-danger">Cancel</button>
                            <a href="/appointments/detail_blank/${row.uuid}" target="_blank" class="btn btn-sm btn-outline-primary">View Detail</a>
                        </div>
                    </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-4">
                                <img src="${row.patient.patient_potrait ? row.patient.patient_potrait.url[row.patient.patient_potrait.url.length - 1] : `{{ asset('images/potrait-placeholder.png') }}`}" class="img-thumbnail" />
                            </div>
                            <div class="col-8 text-truncate">
                                <p class="mb-0 text-muted">Patient</p>
                                <span class="fw-bold">${row.patient.name}</span>
                                <p class="mb-0 text-muted">Additional Note</p>
                                <div class="p-3 bg-body-secondary rounded text-truncate">
                                    <span class="fw-bold">${row.additional_note ? row.additional_note : '-'}</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item d-grid p-2">
                        <button onclick="window.takeAssignment(event)" type="button" data-status="${row.status}" data-uuid="${row.uuid}" class="btn btn-primary take-assignment-btn">Checkout</button>
                    </li>
                </ul>
            </div>
            `;
        });

        $("#myList").html(html);
    }

    const takeAssignment = async (e) => {
        const uuid = e.target.getAttribute("data-uuid");
        const currentStatus = e.target.getAttribute("data-status");
        setTakeLoading(true);
        const btn = e.target;
        btn.classList.add('disabled');
        btn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );

        const formData = new FormData();
        const param = {
            uuid: uuid,
            status: currentStatus
        };
        for (var key in param) {
            formData.append(key, param[key]);
        }
        await fetch(`/api/v1/appointment/take`, {
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
            getMyAssignment();
            $(".take-assignment-btn").addClass("disabled");
            updateMainContent(response.data);
            $("#cancelBtn").attr("data-uuid", response.data.uuid);
            $("#submitBtn").attr("data-uuid", response.data.uuid);
            document.getElementById("submitLoading").remove();
            setTakeLoading(false);
            discountPctgImask.typedValue = 0;
            discountAmtImask.typedValue = 0;
            discountPctgImask.updateValue();
            discountAmtImask.updateValue();
            $('#pctgRadio').prop('checked', true);
        }).catch(error => {
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            showToast(error, true);
        })
    }

    const updateMainContent = (data) => {
        // Patient info
        $("#patientPotrait").attr("src", data.patient.patient_potrait ? data.patient.patient_potrait.url[data
            .patient.patient_potrait.url.length - 1] : `{{ asset('images/potrait-placeholder.png') }}`);
        $("#patientName").html(data.patient.name);
        $("#patientAddress").html(data.patient.address ? data.patient.address : '-');
        $("#patientEmail").html(data.patient.email ? data.patient.email : '-');
        $("#patientPhone").html(data.patient.phone_number ? `+62 ${data.patient.phone_number}` : '-');
        // Prescription
        const rx = localStorage.getItem("prescription");
        if (rx) {
            let parsedRx = [];
            let obj = {
                uuid: data.uuid,
                data: data.prescription ? data.prescription.list : []
            };
            parsedRx.push(obj);
            localStorage.setItem("prescription", JSON.stringify(parsedRx));
        }
    }

    const updateRxBody = (uuid, disableHtmlUpdate = false) => {
        // check uuid
        const parsedRx = JSON.parse(localStorage.getItem("prescription"));
        const filtered = parsedRx.filter(a => a.uuid === uuid);
        if (!disableHtmlUpdate) {
            let html = '';
            fullPrice = 0;
            itemImask = {};
            if (filtered.length > 0) {
                filtered.map(a => {
                    if (a.data.length > 0) {
                        a.data.map((item, idx) => {
                            itemLength++;
                            itemImask[idx] = null;
                            let totalPrice = item.price * item.qty;
                            fullPrice += totalPrice;
                            html += `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between mt-2" style="gap: 1em">
                                    <div class="flex-fill">
                                        <h5 class="mb-1">${item.label}</h5>
                                        <p class="mb-1 text-muted">${item.sku}</p>
                                        <small>Notes: ${item.notes ? item.notes : '-'}</small>
                                    </div>
                                    <div class="d-flex align-items-start gap-1" style="min-width: 75px; max-width: 140px">
                                        <button class="btn btn-sm rounded-pill btn-danger" onclick="window.deleteItem(${idx})"><i class="bi bi-trash"></i></button>
                                        <div class="input-group input-group-sm">
                                            <button style="z-index: 0" onclick="window.subtractItem(${idx})" class="btn btn-dark rounded-start-pill" type="button"><i class="bi bi-dash-lg"></i></button>
                                            <input style="z-index: 0" id="qty-${idx}" class="form-control" type="text" value="${item.qty}" readonly>
                                            <button style="z-index: 0" onclick="window.addItem(${idx})" class="btn btn-dark rounded-end-pill" type="button"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2 border-top mb-2 mt-2">
                                    <label for="itemDiscount-${idx}" class="form-label">Item Discount Type</label>
                                    <div class="input-group">
                                        <select id="itemDiscountType-${idx}" class="form-select" onchange="window.checkAmountPaid()" style="max-width: 150px; z-index: 0">
                                            <option value="pctg">Percentage</option>
                                            <option value="amt">Amount</option>
                                        </select>
                                        <span id="itemPrefix-${idx}" class="input-group-text">Rp</span>
                                        <input type="text" id="itemDiscount-${idx}" onkeyup="window.checkAmountPaid()" class="form-control" style="z-index: 0">
                                        <span id="itemSuffix-${idx}" class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between bg-body-secondary rounded p-2 mt-3 mb-3">
                                    <p class="mb-0">Price: <span id="itemPrice-${idx}">${item.price.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span></p>
                                    <p class="mb-0 fw-bold">Subtotal: <span id="itemSubtotalPrice-${idx}">${totalPrice.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span></p>
                                </div>
                            </li>
                        `;
                        });
                    }
                });
            }
            $("#rxBody").html(html);
        } else {
            fullPrice = 0;
            if (filtered.length > 0) {
                filtered.map(a => {
                    if (a.data.length > 0) {
                        a.data.map((item, idx) => {
                            const itemPrice = calculateItemPrice(item.price, item.discount_type,
                                item.discount_value);
                            const subTotalPrice = itemPrice * item.qty;

                            fullPrice += subTotalPrice;

                            $(`#qty-${idx}`).val(item.qty);
                            $(`#itemPrice-${idx}`).html(itemPrice.toLocaleString('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }));
                            $(`#itemSubtotalPrice-${idx}`).html(subTotalPrice.toLocaleString(
                                'id-ID', {
                                    style: 'currency',
                                    currency: 'IDR',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }));
                        })
                    }
                })
            }
        }

        // Check total discount
        if ($('#pctgRadio').is(':checked')) {
            $("#totalDiscountPctg").trigger("keyup");
        } else if ($('#amtRadio').is(':checked')) {
            $("#totalDiscountAmt").trigger("keyup");
        }
    }

    const initiateItemImask = (length) => {
        for (let i = 1; i <= length; i++) {
            $(`#itemDiscountType-${i - 1}`).change(function(e) {
                let opt = null;
                if ($(this).val() === "pctg") {
                    opt = {
                        mask: Number,
                        scale: 0,
                        thousandsSeparator: '.',
                        padFractionalZeros: false,
                        normalizeZeros: true,
                        radix: ',',
                        validate: function(value) {
                            var intValue = parseInt(value.replace(/\D/g, ''));
                            return intValue >= 0 && intValue <= 100;
                        },
                    };
                    $(`#itemSuffix-${i - 1}`).show();
                    $(`#itemPrefix-${i - 1}`).hide();
                } else {
                    opt = {
                        mask: Number,
                        scale: 0,
                        thousandsSeparator: '.',
                        padFractionalZeros: false,
                        normalizeZeros: true,
                        radix: ',',
                    };
                    $(`#itemSuffix-${i - 1}`).hide();
                    $(`#itemPrefix-${i - 1}`).show();
                }
                if (itemImask[i - 1] !== null) {
                    $(`#itemDiscount-${i - 1}`).val("");
                    itemImask[i - 1].destroy();
                }

                const imask = IMask(document.getElementById(`itemDiscount-${i - 1}`), opt);
                itemImask[i - 1] = imask;
                handleItemDiscount(i - 1);
            });
            $(`#itemDiscount-${i - 1}`).keyup(function(e) {
                handleItemDiscount(i - 1);
            });
            $(`#itemDiscount-${i - 1}`).click(function(e) {
                $(this).select();
            });
            $(`#itemDiscountType-${i - 1}`).trigger("change");
        }
    }

    const calculateItemPrice = (originalPrice, type, amount) => {
        let price = 0;
        if (type === 'pctg') {
            let rate = amount / 100;
            price = originalPrice - (originalPrice * rate);
        } else if (type === 'amt') {
            price = originalPrice - amount;
        }

        return price;
    }

    const deleteItem = (idx) => {
        const storage = localStorage.getItem("prescription");
        if (storage) {
            let parsedRx = JSON.parse(storage);
            if (parsedRx[0].data.length > 0) {
                parsedRx[0].data.splice(idx, 1);
            }
            localStorage.setItem('prescription', JSON.stringify(parsedRx));
            updateRxBody(assignedUuid, false);
        }

        window.checkAmountPaid();
    }

    const setTakeLoading = (status) => {
        if (status) {
            if (!$("#loadingIndicator").hasClass("d-block")) {
                $("#loadingIndicator").addClass("d-block");
            }
            if ($("#loadingIndicator").hasClass("d-none")) {
                $("#loadingIndicator").removeClass("d-none");
            }
            if ($("#selectedAssignment").hasClass("d-block")) {
                $("#selectedAssignment").removeClass("d-block");
            }
            if (!$("#selectedAssignment").hasClass("d-none")) {
                $("#selectedAssignment").addClass("d-none");
            }
        } else {
            if ($("#loadingIndicator").hasClass("d-block")) {
                $("#loadingIndicator").removeClass("d-block");
            }
            if (!$("#loadingIndicator").hasClass("d-none")) {
                $("#loadingIndicator").addClass("d-none");
            }
            if (!$("#selectedAssignment").hasClass("d-block")) {
                $("#selectedAssignment").addClass("d-block");
            }
            if ($("#selectedAssignment").hasClass("d-none")) {
                $("#selectedAssignment").removeClass("d-none");
            }
        }
    }

    const setLoading = (status) => {
        if (status) {
            if (!$("#loadingList").hasClass("d-flex")) {
                $("#loadingList").addClass("d-flex");
            }
            if ($("#loadingList").hasClass("d-none")) {
                $("#loadingList").removeClass("d-none");
            }
            if ($("#myList").hasClass("d-flex")) {
                $("#myList").removeClass("d-flex");
            }
            if (!$("#myList").hasClass("d-none")) {
                $("#myList").addClass("d-none");
            }
        } else {
            if ($("#loadingList").hasClass("d-flex")) {
                $("#loadingList").removeClass("d-flex");
            }
            if (!$("#loadingList").hasClass("d-none")) {
                $("#loadingList").addClass("d-none");
            }
            if (!$("#myList").hasClass("d-flex")) {
                $("#myList").addClass("d-flex");
            }
            if ($("#myList").hasClass("d-none")) {
                $("#myList").removeClass("d-none");
            }
        }
    }

    const getIcon = (status) => {
        switch (status) {
            case 'DOC_WAITING':
                return 'bi-person-heart';
                break;
            case 'DOC_ASSIGNED':
                return 'bi-person-heart';
                break;
            case 'PHAR_WAITING':
                return 'bi-capsule';
                break;
            case 'PHAR_ASSIGNED':
                return 'bi-capsule';
                break;
            case 'PAYMENT_WAITING':
                return 'bi-wallet2';
                break;

            default:
                return 'bi-question-lg';
                break;
        }
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
        const uuid = e.target.getAttribute("data-uuid");
        const method = e.target.getAttribute("data-method");
        const totalPrice = $("#totalPrice")[0].innerText;
        const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
        const amountPaid = $("#amountPaid")[0].innerText;
        const amountPaidNum = Number(amountPaid.replace(/\D/g, ""));
        const amountChg = $("#amountChange")[0].innerText;
        const amountChgNum = Number(amountChg.replace(/\D/g, ""));
        setTakeLoading(true);
        const btn = document.getElementById("approvementModalSubmit");
        btn.classList.add('disabled');
        btn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const formData = new FormData();
        const param = {
            uuid: uuid,
            method: method,
            prescription: localStorage.getItem(`prescription`) ? localStorage.getItem(`prescription`) :
                null,
            payment_with: "CASH",
            payment_amount: amountPaidNum,
            total_discount_type: document.querySelector('input[name="totalDiscType"]:checked').value,
            total_amount: totalPriceNum,
            change: amountChgNum,
            payment_details: localStorage.getItem("payments")
        };
        if (param.total_discount_type === 'pctg') {
            param['total_discount'] = discountPctgImask.typedValue;
        } else if (param.total_discount_type === 'amt') {
            param['total_discount'] = discountAmtImask.typedValue;
        }
        for (var key in param) {
            formData.append(key, param[key]);
        }

        await fetch('/api/v1/cashier/progress', {
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
            showToast(response.message, false);
            getMyAssignment();
            if ($("#selectedAssignment").hasClass("d-block")) {
                $("#selectedAssignment").removeClass("d-block");
            }
            $("#selectedAssignment").addClass("d-none");
            if ($("#loadingIndicator").hasClass("d-block")) {
                $("#loadingIndicator").removeClass("d-block");
            }
            $("#loadingIndicator").addClass("d-none");
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            _approvalModal.hide();
            $("#cashierForm")[0].reset();
            localStorage.setItem('prescription', JSON.stringify([]));
            resetPayment();
        }).catch(error => {
            showToast(error, true);
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            _approvalModal.hide();
        })
    }

    const checkAmountPaid = () => {
        const amountPaid = 0;
        const totalPrice = $("#totalPrice")[0].innerText;
        const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
        if (amountPaid >= totalPriceNum) {
            if ($("#submitBtn").hasClass("disabled")) {
                $("#submitBtn").removeClass("disabled");
            }
        } else {
            if (!$("#submitBtn").hasClass("disabled")) {
                $("#submitBtn").addClass("disabled");
            }
        }
    }

    const calculateTotalPrice = (isPctg, amount) => {
        let discountRate = 0;
        let discountedPrice = 0;
        if (isPctg) {
            discountRate = amount / 100;
            discountedPrice = fullPrice - (fullPrice * discountRate);
        } else {
            discountedPrice = fullPrice - amount <= 0 ? 0 : fullPrice - amount;
        }

        $("#totalPrice").html(discountedPrice.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));
    }

    const subtractItem = (idx) => {
        const rx = localStorage.getItem("prescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            if (parseInt(parsedRx[0].data[idx].qty) - 1 >= 1) {
                parsedRx[0].data[idx].qty = parseInt(parsedRx[0].data[idx].qty) - 1;
                localStorage.setItem("prescription", JSON.stringify(parsedRx));
                updateRxBody(assignedUuid, true);
            }
        }

        window.checkAmountPaid();
    }

    const addItem = (idx) => {
        const rx = localStorage.getItem("prescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            parsedRx[0].data[idx].qty = parseInt(parsedRx[0].data[idx].qty) + 1;
            localStorage.setItem("prescription", JSON.stringify(parsedRx));
            updateRxBody(assignedUuid, true);
        }

        window.checkAmountPaid();
    }

    const handleItemDiscount = (idx) => {
        const rx = localStorage.getItem("prescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            parsedRx[0].data[idx].discount_type = $(`#itemDiscountType-${idx}`).val();
            parsedRx[0].data[idx].discount_value = parseInt(itemImask[idx].unmaskedValue !== "" ? itemImask[idx]
                .unmaskedValue : 0);
            localStorage.setItem("prescription", JSON.stringify(parsedRx));
            updateRxBody(assignedUuid, true);
        }
    }

    const handleCancleAssignment = async (e) => {
        $("#cancelAssignmentSubmitBtn").addClass("disabled");
        $("#cancelAssignmentSubmitBtn").prepend(
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const requestBody = new FormData($("#cancelAssignmentForm")[0]);
        await fetch("/api/v1/appointment/make-detail", {
                headers: {
                    Accept: "application/json, text-plain, */*",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                method: "post",
                credentials: "same-origin",
                body: requestBody
            })
            .then(response => {
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
            })
            .then(response => {
                cancelModal.hide();
                getMyAssignment();
                if ($("#selectedAssignment").hasClass("d-block")) {
                    $("#selectedAssignment").removeClass("d-block");
                }
                $("#selectedAssignment").addClass("d-none");
                if ($("#loadingIndicator").hasClass("d-block")) {
                    $("#loadingIndicator").removeClass("d-block");
                }
                $("#loadingIndicator").addClass("d-none");
                showToast(response.message);
            })
            .catch(error => {
                showToast(error, true);
                $("#cancelAssignmentSubmitBtn").removeClass("disabled");
                $("#submitLoading").remove();
                cancelModal.hide();
            });
    }

    // Listen when assignment is created
    window.Echo.channel("assignment_created").listen(
        "AssignmentCreated",
        (event) => {
            getMyAssignment(true);
        }
    );
    // Listen when assignment is taken
    window.Echo.channel("assignment_taken").listen(
        "AssignmentTaken",
        (event) => {
            getMyAssignment(true);
        }
    );

    const getCurrentStock = async (uuid) => {
        const storageRx = localStorage.getItem('prescription');
        const parsedRx = JSON.parse(storageRx);
        const prescription = parsedRx[0].data;

        const param = {
            prescription: JSON.stringify(prescription),
        }
        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }

        $("#approvementModalBody").html("");
        $("#approvementModalSubmit").attr("disabled", false);

        return await fetch("/api/v1/stocks/current-stock", {
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
            let items = '';
            let canProceed = true;
            prescription.map((p, idx) => {
                let color = "";

                if (response.data[p.sku] !== null) {
                    const stockLeft = response.data[p.sku] - p.qty;
                    if (stockLeft < 0) {
                        color = "list-group-item-danger";
                        $("#approvementModalSubmit").attr("disabled", true);
                        canProceed = false;
                    } else if (stockLeft < 10) {
                        color = "list-group-item-warning";
                    }
                }

                items += `
                    <li class="list-group-item d-flex justify-content-between align-items-start ${color}">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">${p.label}</div>
                            ${p.sku}
                        </div>
                        <h5><span class="badge text-bg-light rounded-pill">Stock ${response.data[p.sku] !== null ? Intl.NumberFormat("id").format(response.data[p.sku]) : "♾️" }</span></h5>
                    </li>
                `;
            });
            let list =
                `
                    <ol class="list-group list-group-numbered">${items}</ol>
                    <p class="fw-bold mt-4">${canProceed ? "The transaction will be made and stock will be deducted after this point. Are you sure?" : "There is an out-of-stock item, or requested items are bigger than available stock. Please check the inventory."}</p>
                `;

            $("#approvementModalBody").html(list);

            return response.data;
        }).catch(error => {
            showToast(error, true);
        })
    }

    window.calculateItemPrice = calculateItemPrice;
    window.takeAssignment = takeAssignment;
    window.subtractItem = subtractItem;
    window.deleteItem = deleteItem;
    window.addItem = addItem;
    window.cancelAssignment = (e) => {
        const uuid = e.target.getAttribute("data-uuid");
        $("#cancelUuid").val(uuid);
        $("#cancelStatus").val("CANCELED");
        cancelModal.toggle();
    };

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

        prescription = localStorage.getItem('prescription');
        if (!prescription) {
            localStorage.setItem('prescription', JSON.stringify([]));
        }
        discountAmtImask = IMask(document.getElementById("totalDiscountAmt"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });
        discountPctgImask = IMask(document.getElementById("totalDiscountPctg"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
            validate: function(value) {
                var intValue = parseInt(value.replace(/\D/g, ''));
                return intValue >= 0 && intValue <= 100;
            },
        });

        getMyAssignment();

        $("#cancelBtn").click(function(e) {
            const uuid = $(this).get(0).getAttribute("data-uuid");
            if (uuid) {
                $("#approvementModalBody").html("Are you sure?");
                $("#approvementModalSubmit").attr("disabled", false);
                $("#approvementModalHeader").html("Cancel Payment");
                $("#approvementModalSubmit").attr("data-method", "cancel");
                $("#approvementModalSubmit").attr("data-uuid", uuid);
                if (!$("#approvementModalSubmit").hasClass("btn-danger")) {
                    $("#approvementModalSubmit").addClass("btn-danger");
                    $("#approvementModalSubmit").removeClass("btn-success");
                }
                _approvalModal.show();
            } else {
                showToast('No UUID Found. Try to click the button again.', true);
            }
        });
        $("#submitBtn").click(async function(e) {
            const stocks = await getCurrentStock();
            const uuid = $(this).get(0).getAttribute("data-uuid");
            if (uuid) {
                $("#approvementModalHeader").html("Submit Payment");
                $("#approvementModalSubmit").attr("data-method", "submit");
                $("#approvementModalSubmit").attr("data-uuid", uuid);
                if (!$("#approvementModalSubmit").hasClass("btn-success")) {
                    $("#approvementModalSubmit").addClass("btn-success");
                    $("#approvementModalSubmit").removeClass("btn-danger");
                }
                _approvalModal.show();
            } else {
                showToast('No UUID Found. Try to click the button again.', true);
            }
        });
        $("#approvementModalSubmit").click(function(e) {
            handleSubmit(e);
        });
        $("#cashierForm").submit(function(e) {
            e.preventDefault();
        });
        $("#totalDiscountPctg").keyup(function(e) {
            calculateTotalPrice(true, parseInt(e.target.value !== "" ? Number(e.target.value.replace(
                /\D/g, "")) : 0));
        });
        $("#totalDiscountPctg").click(function(e) {
            $(this).select();
        });
        $("#totalDiscountAmt").keyup(function(e) {
            calculateTotalPrice(false, parseInt(e.target.value !== "" ? Number(e.target.value.replace(
                /\D/g, "")) : 0));
        });
        $("#totalDiscountAmt").click(function(e) {
            $(this).select();
        });
        $("#filterForm").submit(function(e) {
            e.preventDefault();
            getMyAssignment(true);
        });
        $("#filterForm").change(function(e) {
            console.log("change", e);
        });
        // Show/hide the discount divs based on the selected radio button
        $('input[name="totalDiscType"]').change(function() {
            if ($('#pctgRadio').is(':checked')) {
                $('#totalDiscountPctgDiv').show();
                $('#totalDiscountAmtDiv').hide();
            } else if ($('#amtRadio').is(':checked')) {
                $('#totalDiscountAmtDiv').show();
                $('#totalDiscountPctgDiv').hide();
            }
            discountPctgImask.typedValue = 0;
            discountAmtImask.typedValue = 0;
            discountPctgImask.updateValue();
            discountAmtImask.updateValue();
            $("#totalDiscountAmt").trigger("keyup");
            $("#totalDiscountPctg").trigger("keyup");
        });
        $("#pctgRadio").prop("checked", true);
        $("#totalDiscountAmtDiv").hide();
        $("#cancelAssignmentSubmitBtn").click(function(e) {
            handleCancleAssignment(e);
        });
        $("#markAsCancelBtn").click(function(e) {
            $("#cancelUuid").val(assignedUuid);
            $("#cancelStatus").val("CANCELED");
            cancelModal.toggle();
        });
    });
</script>
