<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const _checkoutModal = new bootstrap.Modal("#checkoutModal", {});
    let freePrescription;
    let patientSelector;
    let liveToast;
    let freeDiscountAmtImask;
    let freeDiscountPctgImask;
    let freeAmountImask;
    const _medSelectorModal = document.getElementById("medSelectorModal");
    let medSelectorModal;
    if (_medSelectorModal) {
        medSelectorModal = new bootstrap.Modal(_medSelectorModal, {});
    }
    let freeFullPrice = 0;
    let totalPrice = 0;
    let amountPaid = 0;
    let amountChange = 0;
    let freeItemImask = {};

    const calculateSum = () => {
        const storage = localStorage.getItem("freePrescription");
        const parsedRx = JSON.parse(storage);
        totalPrice = 0;
        if (parsedRx.length > 0) {
            // Calculate item
            if (parsedRx[0].data.length > 0) {
                parsedRx[0].data.map(item => {
                    let itemPrice = parseInt(item.price);
                    if (item.discount_value !== 0) {
                        if (item.discount_type === "amt") {
                            itemPrice = itemPrice - item.discount_value;
                        } else if (item.discount_type === "pctg") {
                            itemPrice = itemPrice - ((itemPrice * item.discount_value) / 100);
                        }
                    }
                    totalPrice += itemPrice * parseInt(item.qty)
                });
            }
        }

        $("#free-totalPrice").html(totalPrice.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));

        $("#free-amountPaid").html(amountPaid.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));

        $("#free-amountChange").html(amountChange.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));
    }

    const initPatientSelector = () => {
        const $patientSelector = $('#patient').selectize({
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                options: [],
                onChange: function() {
                    $("#patient").removeClass("is-invalid");
                    updateLocalStorage();

                    if ($("#patient").val() !== "") {
                        getPatientDetails($("#patient").val());
                    } else {
                        updatePatientDetails();
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback([]);
                    $.ajax({
                        type: 'get',
                        url: `/api/v1/appointment/patient-list/${query}`,
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
                },
                plugins: ["clear_button"],
            });
        patientSelector = $patientSelector[0].selectize;
    }

    const initPaymentMethod = () => {
        freeAmountImask = IMask(document.getElementById("free-amount"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });
        freeDiscountAmtImask = IMask(document.getElementById("free-totalDiscountAmt"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });
        freeDiscountPctgImask = IMask(document.getElementById("free-totalDiscountPctg"), {
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

        $('input[name="free-totalDiscType"]').change(function() {
            if ($('#free-pctgRadio').is(':checked')) {
                $('#free-totalDiscountPctgDiv').show();
                $('#free-totalDiscountAmtDiv').hide();
            } else if ($('#free-amtRadio').is(':checked')) {
                $('#free-totalDiscountAmtDiv').show();
                $('#free-totalDiscountPctgDiv').hide();
            }
            freeDiscountPctgImask.typedValue = 0;
            freeDiscountAmtImask.typedValue = 0;
            freeDiscountPctgImask.updateValue();
            freeDiscountAmtImask.updateValue();
            $("#free-totalDiscountAmt").trigger("keyup");
            $("#free-totalDiscountPctg").trigger("keyup");

            updateLocalStorage();
        });
        $("#free-payment").change(function() {
            updateLocalStorage();
        });
        $("#free-pctgRadio").prop("checked", true);
        $("#free-totalDiscountAmtDiv").hide();
        $("#free-amount").click(function(e) {
            $(this).select();
        });
        $("#free-totalDiscountPctg").keyup(function(e) {
            calculateTotalPrice(true, parseInt(e.target.value !== "" ? Number(e.target.value.replace(/\D/g, "")) : 0));
        });
        $("#free-totalDiscountPctg").click(function(e) {
            $(this).select();
        });
        $("#free-totalDiscountPctg").change(function(e) {
            updateLocalStorage();
        });
        $("#free-totalDiscountAmt").keyup(function(e) {
            calculateTotalPrice(false, parseInt(e.target.value !== "" ? Number(e.target.value.replace(/\D/g, "")) : 0));
        });
        $("#free-totalDiscountAmt").click(function(e) {
            $(this).select();
        });
        $("#free-totalDiscountAmt").change(function(e) {
            updateLocalStorage();
        });
        $("#free-amount").keyup(function(e) {
            calculateChange();
            checkAmountPaid();
            checkSubmitBtn();
        });
        $("#free-amount").change(function(e) {
            checkAmountPaid();
            updateLocalStorage();
            checkSubmitBtn();
        });
        $("#free-fullPriceBtn").click(function(e) {
            handleFullPrice();
        });
    }

    const initItemSection = () => {
        $("#addItemBtn").click(function(e) {
            if (e.target) {
                medSelectorModal.toggle(e.target);
            } else {
                showToast("Oops! There is an unexpected error. Please refresh page.", true);
            }
        });
        $("#closeModalIconBtn").click(function() {
            medSelectorModal.hide();
        });
        $("#closeModalBtn").click(function() {
            medSelectorModal.hide();
        });
    }

    const handleFullPrice = () => {
        const totalPrice = $("#free-totalPrice")[0].innerText;
        const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
        if (totalPriceNum === parseInt(freeAmountImask.unmaskedValue)) {
            return false;
        }
        $("#free-amount").val(totalPriceNum);
        freeAmountImask.typedValue = totalPriceNum;
        freeAmountImask.updateValue();
        $("#free-amount").trigger("input");
        $("#free-amount").trigger("change");

        calculateChange();
    }

    const calculateTotalPrice = (isPctg, amount) => {
        let discountRate = 0;
        let discountedPrice = 0;
        if (isPctg) {
            discountRate = amount / 100;
            discountedPrice = totalPrice - (totalPrice * discountRate);
        } else {
            discountedPrice = totalPrice - amount <= 0 ? 0 : totalPrice - amount;
        }

        $("#free-totalPrice").html(discountedPrice.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));

        calculateChange();
        checkSubmitBtn();
    }

    const checkAmountPaid = () => {
        const amountPaid = freeAmountImask.typedValue;
        const totalPrice = $("#free-totalPrice")[0].innerText;
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

    const calculateChange = () => {
        let amountPaid = parseInt(freeAmountImask.unmaskedValue !== "" ? freeAmountImask.unmaskedValue : 0);
        $("#free-amountPaid").html(amountPaid.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));
        const totalPrice = $("#free-totalPrice")[0].innerText;
        let changeAmt = amountPaid - Number(totalPrice.replace(/\D/g, ""));
        if (changeAmt < 0) {
            changeAmt = 0;
        }
        $("#free-amountChange").html(changeAmt.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));
    }

    const updateLocalStorage = () => {
        freePrescription = localStorage.getItem('freePrescription');
        if (freePrescription) {
            const parsedRx = JSON.parse(freePrescription);

            // Patient
            let patientId = null;
            if ($("#patient").val() !== "") {
                patientId = $("#patient").val();
            }

            // Prescription
            let rx = null;
            if (parsedRx.length > 0) {
                rx = parsedRx[0].data.length > 0 ? parsedRx[0].data : null;
            }

            // Set local storage
            const totalPrice = $("#free-totalPrice")[0].innerText;
            const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
            const amountChg = $("#free-amountChange")[0].innerText;
            const amountChgNum = Number(amountChg.replace(/\D/g, ""));
            const discountVal = document.querySelector('input[name="free-totalDiscType"]:checked').value === "pctg" ? freeDiscountPctgImask.typedValue : freeDiscountAmtImask.typedValue;

            if (parsedRx.length > 0) {
                // Update existing
                parsedRx[0].patient_id = patientId ? patientId : "";
                parsedRx[0].payment.method = $("#free-payment").val();
                parsedRx[0].payment.discount_value = discountVal;
                parsedRx[0].payment.discount_type = document.querySelector('input[name="free-totalDiscType"]:checked').value;
                parsedRx[0].payment.total = totalPriceNum;
                parsedRx[0].payment.amount = freeAmountImask.typedValue;
                parsedRx[0].payment.change = amountChgNum;
            } else {
                // Add new
                let obj = {
                    patient_id: patientId ? patientId : "",
                    data: rx ? rx : [],
                    payment: {
                        method: $("#free-payment").val(),
                        discount_value: discountVal,
                        discount_type: document.querySelector('input[name="free-totalDiscType"]:checked').value,
                        total: totalPriceNum,
                        amount: freeAmountImask.typedValue,
                        change: amountChgNum
                    }
                };

                parsedRx.push(obj);
            }

            localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
        }
    }

    const getPatientDetails = async (patientId) => {
        await fetch(`api/v1/patient/show/${patientId}`, {
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
            updatePatientDetails(response.data);
        }).catch(error => {
            showToast(error, true);
            return null;
        })
    }

    const updatePatientDetails = (response) => {
        if (response) {
            $("#free-patientPotrait").attr("src", response.patient_potrait ? response.patient_potrait.url[response.patient_potrait.url.length - 1] : `{{ asset('images/potrait-placeholder.png') }}`);
            $("#free-patientName").html(response.name ? response.name : '-' );
            $("#free-patientAddress").html(response.address ? response.address : '-');
            $("#free-patientEmail").html(response.email ? response.email : '-');
            $("#free-patientPhone").html(response.phone_number ? `+62 ${response.phone_number}` : '-');
        } else {
            // Guest transaction
            $("#free-patientPotrait").attr("src", `{{ asset('images/potrait-placeholder.png') }}`);
            $("#free-patientName").html('Guest');
            $("#free-patientAddress").html('-');
            $("#free-patientEmail").html('-');
            $("#free-patientPhone").html('-');
        }
    }

    const updateRxBody = (disableHtmlUpdate = false) => {
        let html = '';
        const storage = localStorage.getItem("freePrescription");
        if (storage) {
            const parseStorage = JSON.parse(storage);
            const itemList = parseStorage[0].data;

            if (itemList && itemList.length > 0) {
                if (!disableHtmlUpdate) {
                    html = iterateRxBody(itemList);
                    $("#free-rxBody").html(html);

                    freeInitiateItemImask(itemList.length);
                } else {
                    freeFullPrice = 0;
                    if (itemList.length > 0) {
                        itemList.map((item, idx) => {
                            const itemPrice = window.calculateItemPrice(item.price, item.discount_type, item.discount_value);
                            const subTotalPrice = itemPrice * item.qty;

                            freeFullPrice += subTotalPrice;
                            $(`#free-qty-${idx}`).val(item.qty);
                            $(`#free-itemPrice-${idx}`).html(itemPrice.toLocaleString('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }));
                            $(`#free-itemSubtotalPrice-${idx}`).html(subTotalPrice.toLocaleString('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }));
                        })
                    }
                }
            } else {
                html = '<li class="list-group-item"><p>No item.</p></li>';
                $("#free-rxBody").html(html);
            }
        } else {
            html = '<li class="list-group-item"><p>No item.</p></li>';
            $("#free-rxBody").html(html);
        }

        calculateSum();

        // Check total discount
        if ($('#free-pctgRadio').is(':checked')) {
            $("#free-totalDiscountPctg").trigger("keyup");
        } else if ($('#free-amtRadio').is(':checked')) {
            $("#free-totalDiscountAmt").trigger("keyup");
        }
        calculateChange();
    }

    const iterateRxBody = (list) => {
        let html = '';
        freeFullPrice = 0;
        list.map((item, idx) => {
            let totalPrice = item.price * item.qty;
            freeFullPrice += totalPrice;
            freeItemImask[idx] = null;
            html += `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between mt-2" style="gap: 1em">
                        <div class="flex-fill">
                            <h5 class="mb-1">${item.label}</h5>
                            <p class="mb-1 text-muted">${item.sku}</p>
                            <small>Notes: ${item.notes ? item.notes : '-'}</small>
                        </div>
                        <div class="d-flex align-items-start gap-1" style="min-width: 75px; max-width: 170px">
                            <button class="btn btn-sm rounded-pill btn-outline-primary" id="btnFreeEdit-${idx}" onclick="window.editItem(event)"><i id="iconFreeEdit-${idx}" class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-sm rounded-pill btn-danger" onclick="window.deleteItem(${idx})"><i class="bi bi-trash"></i></button>
                            <div class="input-group input-group-sm">
                                <button style="z-index: 0" onclick="window.freeSubtractItem(${idx})" class="btn btn-dark rounded-start-pill" type="button"><i class="bi bi-dash-lg"></i></button>
                                <input style="z-index: 0" id="free-qty-${idx}" class="form-control" type="text" value="${item.qty}" readonly>
                                <button style="z-index: 0" onclick="window.freeAddItem(${idx})" class="btn btn-dark rounded-end-pill" type="button"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top mb-2 mt-2">
                        <label for="free-itemDiscount-${idx}" class="form-label">Item Discount Type</label>
                        <div class="input-group">
                            <select id="free-itemDiscountType-${idx}" class="form-select" style="max-width: 150px; z-index: 0">
                                <option value="pctg">Percentage</option>
                                <option value="amt">Amount</option>
                            </select>
                            <span id="free-itemPrefix-${idx}" class="input-group-text">Rp</span>
                            <input type="text" id="free-itemDiscount-${idx}" class="form-control" style="z-index: 0">
                            <span id="free-itemSuffix-${idx}" class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between bg-body-secondary rounded p-2 mt-3 mb-3">
                        <p class="mb-0">Price: <span id="free-itemPrice-${idx}">${item.price.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span></p>
                        <p class="mb-0 fw-bold">Subtotal: <span id="free-itemSubtotalPrice-${idx}">${totalPrice.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span></p>
                    </div>
                </li>
            `;
        })

        return html;
    }

    const freeInitiateItemImask = (length) => {
        for (let i = 1; i <= length; i++) {
            $(`#free-itemDiscountType-${i - 1}`).change(function(e) {
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
                    $(`#free-itemSuffix-${i - 1}`).show();
                    $(`#free-itemPrefix-${i - 1}`).hide();
                } else {
                    opt = {
                        mask: Number,
                        scale: 0,
                        thousandsSeparator: '.',
                        padFractionalZeros: false,
                        normalizeZeros: true,
                        radix: ',',
                    };
                    $(`#free-itemSuffix-${i - 1}`).hide();
                    $(`#free-itemPrefix-${i - 1}`).show();
                }
                if (freeItemImask[i - 1] !== null) {
                    $(`#free-itemDiscount-${i - 1}`).val("");
                    freeItemImask[i - 1].destroy();
                }

                const imask = IMask(document.getElementById(`free-itemDiscount-${i - 1}`), opt);
                freeItemImask[i - 1] = imask;
                freeHandleItemDiscount(i - 1);
            });
            $(`#free-itemDiscount-${i - 1}`).keyup(function(e) {
                freeHandleItemDiscount(i - 1);
            });
            $(`#free-itemDiscount-${i - 1}`).click(function(e) {
                $(this).select();
            });
            $(`#free-itemDiscountType-${i - 1}`).trigger("change");
        }
    }

    const freeHandleItemDiscount = (idx) => {
        const rx = localStorage.getItem("freePrescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            parsedRx[0].data[idx].discount_type = $(`#free-itemDiscountType-${idx}`).val();
            parsedRx[0].data[idx].discount_value = parseInt(freeItemImask[idx].unmaskedValue !== "" ? freeItemImask[idx].unmaskedValue : 0);
            localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
            updateRxBody(true);
        }
    }

    const freeSubtractItem = (idx) => {
        const rx = localStorage.getItem("freePrescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            if (parseInt(parsedRx[0].data[idx].qty) - 1 >= 1) {
                parsedRx[0].data[idx].qty = parseInt(parsedRx[0].data[idx].qty) - 1;
                localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
                updateRxBody(true);
            }
        }
    }

    const freeAddItem = (idx) => {
        const rx = localStorage.getItem("freePrescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            parsedRx[0].data[idx].qty = parseInt(parsedRx[0].data[idx].qty) + 1;
            localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
            updateRxBody(true);
        }
    }

    const deleteItem = (idx) => {
        const storage = localStorage.getItem("freePrescription");
        if (storage) {
            let parsedRx = JSON.parse(storage);
            if (parsedRx[0].data.length > 0) {
                parsedRx[0].data.splice(idx, 1);
            }
            localStorage.setItem('freePrescription', JSON.stringify(parsedRx));
            updateRxBody();
        }
    }

    const editItem = (event) => {
        medSelectorModal.toggle(event.target);
    }

    const checkSubmitBtn = () => {
        let state = false;
        const btn = $("#free-submitBtn");
        const amount = $("#free-amount");
        let amountNum = 0;
        if (amount.val() !== "") {
            amountNum = Number(amount.val().replace(/\D/g, ""));
        }

        const totalPrice = $("#free-totalPrice")[0].innerText;
        const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
        const storage = localStorage.getItem("freePrescription");
        let parsedRx;
        let data;
        if (storage) {
            parsedRx = JSON.parse(storage);
            data = parsedRx[0].data;
        }
        // Check amount paid & item list
        if (amountNum >= totalPriceNum && data.length > 0) {
            state = true;
        }

        if (state) {
            btn.removeClass("disabled");
        } else {
            if (!btn.hasClass("disabled")) {
                btn.addClass("disabled");
            }
        }
    }

    const handleFreeSubmit = async (e) => {
        const btn = document.getElementById("checkoutModalSubmit");
        btn.classList.add('disabled');
        btn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const totalPrice = $("#free-totalPrice")[0].innerText;
        const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
        const amountChg = $("#free-amountChange")[0].innerText;
        const amountChgNum = Number(amountChg.replace(/\D/g, ""));
        const discountVal = document.querySelector('input[name="free-totalDiscType"]:checked').value === "pctg" ? freeDiscountPctgImask.typedValue : freeDiscountAmtImask.typedValue;
        const storage = localStorage.getItem("freePrescription");
        if (storage) {
            const parsed = JSON.parse(storage);
            const obj = {
                data: parsed[0].data,
                patient: parsed[0].patient_id,
                payment: {
                    method: $("#free-payment").val(),
                    discount_value: discountVal,
                    discount_type: document.querySelector('input[name="free-totalDiscType"]:checked').value,
                    total: totalPriceNum,
                    amount: freeAmountImask.typedValue,
                    change: amountChgNum
                }
            }
            const formData = new FormData();
            for (var key in obj) {
                let value = obj[key];
                if (typeof value === "object") {
                    value = JSON.stringify(value);
                }
                formData.append(key, value);
            }
            await fetch('/api/v1/cashier/checkout', {
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
                btn.classList.remove('disabled');
                document.getElementById("submitLoading").remove();
                resetAll();
            }).catch(error => {
                showToast(error, true);
                btn.classList.remove('disabled');
                document.getElementById("submitLoading").remove();
            })
        }
    }

    const resetAll = () => {
        $("#patient").val("");

        const storage = localStorage.getItem("freePrescription");
        if (storage) {
            // Data
            const parsedRx = JSON.parse(storage);
            parsedRx[0].data = [];

            // Patient
            $("#patient").val("");
            parsedRx[0].patient_id = "";

            // Payment
            parsedRx[0].payment.method = "CASH";
            parsedRx[0].payment.discount_value = 0;
            parsedRx[0].payment.discount_type = "pctg";
            parsedRx[0].payment.total = 0;
            parsedRx[0].payment.amount = 0;
            parsedRx[0].payment.change = 0;

            // Set local storage
            const totalPrice = $("#free-totalPrice")[0].innerText;
            const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
            const amountChg = $("#free-amountChange")[0].innerText;
            const amountChgNum = Number(amountChg.replace(/\D/g, ""));
            const discountVal = document.querySelector('input[name="free-totalDiscType"]:checked').value === "pctg" ? freeDiscountPctgImask.typedValue : freeDiscountAmtImask.typedValue;

            localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
        }

        // Update visual
        $("#free-payment").val("CASH");
        freeAmountImask.typedValue = "";
        freeAmountImask.updateValue();
        $("#free-amount").trigger("input");
        $("#free-amount").trigger("change");
        freeDiscountPctgImask.typedValue = "";
        freeDiscountPctgImask.updateValue();
        $("#free-totalDiscountPctg").trigger("input");
        $("#free-totalDiscountPctg").trigger("change");
        freeDiscountAmtImask.typedValue = "";
        freeDiscountAmtImask.updateValue();
        $("#free-totalDiscountAmt").trigger("input");
        $("#free-totalDiscountAmt").trigger("change");

        // Patient details
        $("#free-patientPotrait").attr("src", `{{ asset('images/potrait-placeholder.png') }}`);
        $("#free-patientName").html('Guest');
        $("#free-patientAddress").html('-');
        $("#free-patientEmail").html('-');
        $("#free-patientPhone").html('-');
        patientSelector.clear()

        $("#free-pctgRadio").prop("checked", true);

        updateRxBody();
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

    window.medSelectorModal = medSelectorModal;
    window.updateRxBody = updateRxBody;
    window.freeAddItem = freeAddItem;
    window.freeSubtractItem = freeSubtractItem;
    window.deleteItem = deleteItem;
    window.editItem = editItem;
    window.checkSubmitBtn = checkSubmitBtn;

    $(document).ready(function() {
        localStorage.setItem('freePrescription', JSON.stringify([]));
        liveToast = new bootstrap.Toast(_liveToast);

        calculateSum();
        initPatientSelector();
        initPaymentMethod();
        initItemSection();

        updatePatientDetails();
        updateLocalStorage();
        updateRxBody();

        // Fix CSS issue regarding patient selector
        const parentA = $("#patient-selectized").parent();
        const parentB = parentA.parent();
        parentB.css('z-index', 0);

        $("#free-submitBtn").click(function() {
            _checkoutModal.show();
        });

        $("#free-resetBtn").click(function() {
            resetAll();
        })

        $("#checkoutModalSubmit").click(function(e) {
            handleFreeSubmit(e);
        });

    });
</script>
