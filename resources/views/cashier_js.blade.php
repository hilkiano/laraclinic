<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const _approvalModal = new bootstrap.Modal("#approvementModal", {});
    const _medModal = document.getElementById("medSelectorModal");
    let medModal;
    let liveToast;
    if (_medModal) {
        medModal = new bootstrap.Modal('#medSelectorModal', {});
    }
    let prescription;
    let assignedUuid;
    let amountImask;
    let fullPrice;
    let amountChange;

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
                        <div class="card-body p-0" style="height: calc(100vh - 245px)">
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
            setTakeLoading(false);
            checkPrescription();
        }
    }

    const getListCards = async () => {
        const filterParam = $("#filterName").val() !== "" ? $("#filterName").val() : null;
        let url = `/api/v1/appointment/mine`;
        if (filterParam) {
            url = url + `?name=${filterParam}`;
        }
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
                        <div class="col">
                            <i class="me-2 bi ${getIcon(row.status)} fs-4 text-primary"></i><span class="text-primary fs-4">${row.daily_code}</span>
                        </div>
                        <div class="col d-flex justify-content-end align-items-center">
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
        }).catch(error => {
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            showToast(error, true);
        })
    }

    const updateMainContent = (data) => {
        // Patient info
        $("#patientPotrait").attr("src", data.patient.patient_potrait ? data.patient.patient_potrait.url[data.patient.patient_potrait.url.length - 1] : `{{ asset('images/potrait-placeholder.png') }}`);
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

    const updateRxBody = (uuid) => {
        // check uuid
        const parsedRx = JSON.parse(localStorage.getItem("prescription"));
        const filtered = parsedRx.filter(a => a.uuid === uuid);
        let html = '';
        fullPrice = 0;
        amountImask.typedValue = 0;
        if (filtered.length > 0) {
            filtered.map(a => {
                if (a.data.length > 0) {
                    a.data.map((item, idx) => {
                        let totalPrice = item.price * item.qty;
                        fullPrice += totalPrice;
                        html += `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-fill">
                                        <h5 class="mb-1">${item.label}</h5>
                                        <p class="mb-1 text-muted">${item.sku}</p>
                                        <small>Notes: ${item.notes ? item.notes : '-'}</small>
                                    </div>
                                    <div style="min-width: 75px; max-width: 100px">
                                        <div class="input-group input-group-sm">
                                            <button style="z-index: 0" onclick="window.subtractItem(${idx})" class="btn btn-dark rounded-start-pill" type="button"><i class="bi bi-dash-lg"></i></button>
                                            <input id="qty-${idx}" class="form-control" type="text" value="${item.qty}" readonly>
                                            <button style="z-index: 0" onclick="window.addItem(${idx})" class="btn btn-dark rounded-end-pill" type="button"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between border-top mt-2">
                                    <p class="mt-2">Price: ${item.price.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                                    <p class="mt-2 fw-bold">Subtotal: ${totalPrice.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                                </div>
                                <div class="d-flex justify-content-between border-top mb-2">
                                    <button class="btn btn-sm btn-dark rounded-pill mt-3"><i class="bi bi-tag-fill me-2"></i>Add Discount</button>
                                </div>
                            </li>
                        `;
                    });
                }
            });
        }
        $("#rxBody").html(html);
        $("#totalPrice").html(fullPrice.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));
        calculateChange();
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
            method: method
        };
        for (var key in param) {
            formData.append(key, param[key]);
        }
        await fetch(`/api/v1/cashier/progress`, {
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
        }).catch(error => {
            showToast(error, true);
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            _approvalModal.hide();
        })
    }

    const checkPrescription = () => {
        const rx = localStorage.getItem("prescription");
        if (rx) {
            // check data
            const parsedRx = JSON.parse(rx);
            if (parsedRx[0].data.length > 0) {
                if ($("#submitBtn").hasClass("disabled")) {
                    $("#submitBtn").removeClass("disabled");
                }
            } else {
                if (!$("#submitBtn").hasClass("disabled")) {
                    $("#submitBtn").addClass("disabled");
                }
            }
        }
    }

    const handleFullPrice = () => {
        $("#amount").val(fullPrice);
        amountImask.typedValue = fullPrice;
        amountImask.updateValue();
        $("#amount").trigger("input");

        calculateChange();
    }

    const calculateChange = () => {
        let amountPaid = parseInt(amountImask.unmaskedValue !== "" ? amountImask.unmaskedValue : 0);
        $("#amountPaid").html(amountPaid.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }));
        let changeAmt = amountPaid - fullPrice;
        if (changeAmt < 0) {
            changeAmt = 0;
        }
        $("#amountChange").html(changeAmt.toLocaleString('id-ID', {
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
                updateRxBody(assignedUuid);
            }
        }
    }

    const addItem = (idx) => {
        const rx = localStorage.getItem("prescription");
        if (rx) {
            const parsedRx = JSON.parse(rx);
            parsedRx[0].data[idx].qty = parseInt(parsedRx[0].data[idx].qty) + 1;
            localStorage.setItem("prescription", JSON.stringify(parsedRx));
            updateRxBody(assignedUuid);
        }
    }

    // Listen when assignment is created
    window.Echo.channel("assignment_created").listen(
        "AssignmentCreated",
        (event) => {
            getMyAssignment();
        }
    );
    // Listen when assignment is taken 
    window.Echo.channel("assignment_taken").listen(
        "AssignmentTaken",
        (event) => {
            getMyAssignment();
        }
    );

    window.takeAssignment = takeAssignment;
    window.checkPrescription = checkPrescription;
    window.subtractItem = subtractItem;
    window.addItem = addItem;

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);
        prescription = localStorage.getItem('prescription');
        if (!prescription) {
            localStorage.setItem('prescription', JSON.stringify([]));
        }
        amountImask = IMask(document.getElementById("amount"), {
            mask: Number,
            scale: 0,
            thousandsSeparator: '.',
            padFractionalZeros: false,
            normalizeZeros: true,
            radix: ',',
        });

        getMyAssignment();

        $("#cancelBtn").click(function(e) {
            const uuid = $(this).get(0).getAttribute("data-uuid");
            if (uuid) {
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
        $("#submitBtn").click(function(e) {
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
        $("#fullPriceBtn").click(function(e) {
            handleFullPrice();
        });
        $("#amount").keyup(function(e) {
            calculateChange();
        });
        $("#amount").click(function(e) {
            $(this).select();
        });
        $("#filterForm").submit(function(e) {
            e.preventDefault();
            getMyAssignment(true);
        });
    });
</script>