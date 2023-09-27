<script type="module">
    let payments;
    const paymentAmountImask = [null, null, null];
    const paymentDiscountImask = [null, null, null];
    let nextPaymentOpt = 1;

    const maxPaymentOpts = 3;

    const freePaymentHtml = () => {
        const html = `
        <div class="row gy-2 gx-3 mt-2 align-items-center p-2 pb-3 bg-body-secondary rounded w-100 position-relative d-none" id="free-option-${nextPaymentOpt}">
            <div class="col">
                <label class="form-label">Payment
                    amount</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="free-payment-amount-${nextPaymentOpt}" name="payment-amount-${nextPaymentOpt}" class="form-control"
                        autocomplete="off">
                </div>
            </div>
            <div class="col-auto">
                <label class="form-label">Payment
                    with</label>
                <div class="input-group input-group-sm">
                    <select class="form-select" id="free-payment-with-${nextPaymentOpt}" name="payment-with-${nextPaymentOpt}" autocomplete="off">
                        <option value="CASH">Cash</option>
                        <option value="CREDIT_CARD">Credit Card
                        </option>
                        <option value="DEBIT_CARD">Debit Card
                        </option>
                        <option value="BANK_TRANSFER">Bank Transfer
                        </option>
                    </select>

                </div>
            </div>
            <div class="col-auto d-none">
                <label class="form-label">Discount</label>
                <div class="input-group input-group-sm">
                    <select id="free-payment-discount-type-${nextPaymentOpt}" name="payment-discount-type-${nextPaymentOpt}" autocomplete="off"
                        class="form-select" style="max-width: 130px; z-index: 0">
                        <option value="pctg">Percentage</option>
                        <option value="amt">Amount</option>
                    </select>
                    <span id="item-payment-prefix-${nextPaymentOpt}" class="input-group-text">Rp</span>
                    <input type="text" style="max-width: 100px" id="free-payment-total-discount-${nextPaymentOpt}"
                        name="payment-total-discount-${nextPaymentOpt}" class="form-control" autocomplete="off">
                    <span id="item-payment-suffix-${nextPaymentOpt}" class="input-group-text">%</span>
                </div>
            </div>
        </div>
        `;

        $("#free-payment-form").append(html);

        // Functionality
        paymentAmountImask[nextPaymentOpt] = IMask(document.getElementById(
            `free-payment-amount-${nextPaymentOpt}`), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });
        $(`#free-payment-amount-${nextPaymentOpt}`).click(function(e) {
            $(this).select();
        });
        $(`#free-payment-amount-${nextPaymentOpt}`).keyup(function(e) {
            freeCalculateChange();
            freeCheckAmountPaid();
        });
        paymentAmountImask[nextPaymentOpt].typedValue = 0;
        paymentAmountImask[nextPaymentOpt].updateValue();
        setTimeout(() => {
            $(`#free-payment-discount-type-${nextPaymentOpt - 1}`).change((e) => {
                if (paymentDiscountImask[nextPaymentOpt - 1]) {
                    paymentDiscountImask[nextPaymentOpt - 1].typedValue = "";
                    paymentDiscountImask[nextPaymentOpt - 1].updateValue();
                    paymentDiscountImask[nextPaymentOpt - 1].destroy();
                }
                const value = e.target.value;
                freeToggleSuffix(value, nextPaymentOpt);
            });
            $(`#free-payment-discount-type-${nextPaymentOpt - 1}`).trigger("change");
            $(`#free-option-${nextPaymentOpt - 1}`).removeClass('d-none');
        }, 100);
        // End of Functionality
    }

    const freeInitializePayment = () => {
        // IMask
        paymentAmountImask[0] = IMask(document.getElementById("free-payment-amount-0"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });

        // Initial form events
        $("#free-payment-amount-0").click(function(e) {
            $(this).select();
        });
        $("#free-payment-amount-0").keyup(function(e) {
            freeCalculateChange();
            freeCheckAmountPaid();
        });
        $("#free-payment-discount-type-0").change((e) => {
            if (paymentDiscountImask[0]) {
                paymentDiscountImask[0].typedValue = "";
                paymentDiscountImask[0].updateValue();
                paymentDiscountImask[0].destroy();
            }
            const value = e.target.value;
            freeToggleSuffix(value, 1);
        });
        $("#free-payment-discount-type-0").trigger("change");
        paymentAmountImask[0].typedValue = 0;
        paymentAmountImask[0].updateValue();
    }

    const freeToggleSuffix = (value, optionIndex) => {
        const prefix = $(`#item-payment-prefix-${optionIndex - 1}`);
        const suffix = $(`#item-payment-suffix-${optionIndex - 1}`);
        if (value === "pctg") {
            paymentDiscountImask[optionIndex - 1] = IMask(document.getElementById(
                `free-payment-total-discount-${optionIndex - 1}`), {
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
            prefix.hide();
            suffix.show();
        } else {
            paymentDiscountImask[optionIndex - 1] = IMask(document.getElementById(
                `free-payment-total-discount-${optionIndex - 1}`), {
                mask: Number,
                scale: 0,
                thousandsSeparator: '.',
                padFractionalZeros: false,
                normalizeZeros: true,
                radix: ',',
            });
            prefix.show();
            suffix.hide();
        }
    }

    const freeAddPayment = () => {
        freePaymentHtml();
        $('#free-payment-form').trigger("change");
        nextPaymentOpt++;

        if (nextPaymentOpt >= maxPaymentOpts) {
            if (!$('#free-add-payment-btn').hasClass("disabled")) {
                $('#free-add-payment-btn').addClass("disabled");
            }
        }
        if (nextPaymentOpt <= 1) {
            $('#free-remove-payment-btn').addClass("disabled");
        } else {
            $('#free-remove-payment-btn').removeClass("disabled");
        }


    }

    const freeRemovePayment = () => {
        $(`#free-option-${nextPaymentOpt - 1}`).remove();
        paymentAmountImask[nextPaymentOpt - 1] = null;
        paymentDiscountImask[nextPaymentOpt - 1] = null;
        $('#free-payment-form').trigger("change");
        nextPaymentOpt -= 1;
        if (nextPaymentOpt <= maxPaymentOpts) {
            $('#free-add-payment-btn').removeClass("disabled");
        }
        if (nextPaymentOpt <= 1) {
            $('#free-remove-payment-btn').addClass("disabled");
        } else {
            $('#free-remove-payment-btn').removeClass("disabled");
        }

        freeCalculateChange();
        freeCheckAmountPaid();
    }

    const freeFormChangeHandler = (e) => {
        const formData = freeGetFormData($("#free-payment-form"));

        localStorage.setItem('freePayments', JSON.stringify(formData));
    }

    const freeGetFormData = (form) => {
        const unindexed = form.serializeArray();
        const indexed = [];

        $.map(unindexed, function(n, i) {
            const lastIndex = n.name.lastIndexOf('-');
            const name = n.name.substring(0, lastIndex);
            const suffix = n.name.substring(lastIndex + 1);
            const isNumber = name === "payment-amount" || name === "payment-total-discount";
            const value = isNumber ? parseInt(n[
                    "value"]
                .replace(/\D/g, "")) : n["value"];

            if (typeof indexed[suffix] === 'undefined') {
                indexed.push({});
            }
            indexed[suffix][name] = isNumber ? isNaN(value) ? null : value : value;
        });

        return indexed;
    }

    const freeCalculateChange = () => {
        const amountImasks = paymentAmountImask.filter(imask => imask);
        let amountPaid = 0;
        amountImasks.map(imask => {
            amountPaid += imask.typedValue;
        });
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

    const freeCheckAmountPaid = () => {
        const amountImasks = paymentAmountImask.filter(imask => imask);
        const freePrescription = JSON.parse(localStorage.getItem("freePrescription"));
        let total = 0;
        amountImasks.map(imask => {
            total += imask.typedValue;
        });
        const totalPrice = $("#free-totalPrice")[0].innerText;
        const totalPriceNum = Number(totalPrice.replace(/\D/g, ""));
        if (total >= totalPriceNum && freePrescription[0].data.length > 0) {
            if ($("#free-submitBtn").hasClass("disabled")) {
                $("#free-submitBtn").removeClass("disabled");
            }
        } else {
            if (!$("#free-submitBtn").hasClass("disabled")) {
                $("#free-submitBtn").addClass("disabled");
            }
        }
    }

    const freeResetPayment = () => {
        // remove other options
        [2, 1].map(idx => {
            $(`#free-option-${idx}`).remove();
            paymentAmountImask[idx] = null;
            paymentDiscountImask[idx] = null;
            $('#free-payment-form').trigger("change");
        })
        // reset nextPaymentOpt
        nextPaymentOpt = 1;
        if (nextPaymentOpt <= maxPaymentOpts) {
            $('#free-add-payment-btn').removeClass("disabled");
        }
        if (nextPaymentOpt <= 1) {
            $('#free-remove-payment-btn').addClass("disabled");
        } else {
            $('#free-remove-payment-btn').removeClass("disabled");
        }
        // reset option 0
        $("#free-payment-with-0").val("CASH");
        $("#free-payment-discount-type-0").val("pctg");
        $("#free-payment-discount-type-0").trigger("change");
        paymentAmountImask[0].typedValue = 0;
        paymentAmountImask[0].updateValue();
        paymentDiscountImask[0].typedValue = 0;
        paymentDiscountImask[0].updateValue();
        $("#free-payment-amount-0").trigger("keyup");
        localStorage.setItem('freePayments', JSON.stringify([]));
    }

    window.freeRemovePayment = freeRemovePayment;
    window.freeResetPayment = freeResetPayment;
    window.freeCalculateChange = freeCalculateChange;
    window.freeCheckAmountPaid = freeCheckAmountPaid;

    $(document).ready(function() {
        // Initialize payment storage
        payments = localStorage.getItem('freePayments');
        if (!payments) {
            localStorage.setItem('freePayments', JSON.stringify([]));
        }

        freeInitializePayment();
        freeCalculateChange();

        // Control events
        $('#free-add-payment-btn').click(() => freeAddPayment());
        $('#free-remove-payment-btn').click(() => freeRemovePayment());
        $('#free-payment-form').submit((e) => e.preventDefault());
        $('#free-payment-form').change((e) => freeFormChangeHandler(e));

        if (nextPaymentOpt <= 1) {
            $('#free-remove-payment-btn').addClass("disabled");
        }
    });
</script>
