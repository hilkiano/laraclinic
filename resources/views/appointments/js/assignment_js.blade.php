<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const _approvalModal = new bootstrap.Modal("#approvementModal", {});
    const _medModal = document.getElementById("medSelectorModal");
    let medModal;
    if (_medModal) {
        medModal = new bootstrap.Modal('#medSelectorModal', {});
    }
    let liveToast;

    const getMyAssignment = async () => {
        setLoading(true);
        const [listCards, assigned] = await Promise.all([getListCards(), getAssigned()]);
        if (listCards) {
            setLoading(false);
            if (listCards.data.length > 0) {
                makeCards(listCards.data);
            } else {
                const emptyCard = `
                    <div class="card mx-4 w-100 bg-body-secondary border-0">
                        <div class="card-body p-0" style="height: 247.733px">
                            <div class="row h-100">
                                <div class="col-12 d-flex justify-content-center align-items-center">
                                    <p class="fs-3 text-muted">No assignment yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $("#myList").html(emptyCard);
            }
        }
        if (assigned.data) {
            $(".take-assignment-btn").addClass("disabled");
            updateMainContent(assigned.data);
            $("#cancelBtn").attr("data-uuid", assigned.data.uuid);
            $("#submitBtn").attr("data-uuid", assigned.data.uuid);
            setTakeLoading(false);
        }
    }

    const getListCards = async () => {
        return await fetch(`/api/v1/appointment/mine`, {
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
            status: getAssignedStatus()
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

    const getAssignedStatus = () => {
        const group = `{{ $group }}`;
        if (group == 3) {
            return 'DOC_ASSIGNED'
        } else if (group == 4) {
            return 'PHAR_ASSIGNED'
        }

        return null
    }

    const makeCards = (data) => {
        let html = '';

        data.map((row, i) => {
            html += `
            <div class="card ${ i + 1 === data.length ? 'mx-4' : 'ms-4' } shadow flex-shrink-0" style="width: 360px">
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
                        <button onclick="window.takeAssignment(event)" type="button" data-status="${row.status}" data-uuid="${row.uuid}" class="btn btn-primary take-assignment-btn">Take Assignment</button>
                    </li>
                </ul>
            </div>
            `;
        });

        $("#myList").html(html);
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
            case 'IN_PAYMENT':
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
        $("#patientBirthDate").html(data.patient.birth_date ? data.patient.birth_date : '-');
        $("#patientWeight").html(data.patient.weight ? `${data.patient.weight} kg` : '-');
        $("#patientHeight").html(data.patient.height ? `${data.patient.height} cm` : '-');
        $("#patientAge").html(data.patient.age ? `${data.patient.age} tahun` : '-');
        $("#patientDetails").html(data.patient.additional_note ? data.patient.additional_note : '-');
        // Medical records row
        let medRows = '';
        if (data.patient.medical_records.length > 0) {
            medRows = createMedicalRows(data.patient.medical_records);
        } else {
            medRows = `
                <tr>
                    <td colspan="4">No Data.</td>
                </tr>
            `;
        }
        $("#medicalRecordsRow").html(medRows);
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
            method: method,
            medical_note: $("#medicalNotes").val(),
            prescription: localStorage.getItem(`prescription-${uuid}`) ? JSON.parse(localStorage.getItem(`prescription-${uuid}`)) : null
        };
        for (var key in param) {
            formData.append(key, param[key]);
        }
        await fetch(`/api/v1/appointment/progress`, {
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
        }).catch(error => {
            showToast(error, true);
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            _approvalModal.hide();
        })
    }

    window.takeAssignment = takeAssignment;

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);

        getMyAssignment();

        $("#cancelBtn").click(function(e) {
            const uuid = e.target.getAttribute("data-uuid");
            if (uuid) {
                $("#approvementModalHeader").html("Cancel Assignment");
                $("#approvementModalSubmit").attr("data-method", "cancel");
                $("#approvementModalSubmit").attr("data-uuid", uuid);
                _approvalModal.show();
            } else {
                showToast('No UUID Found. Try to click the button again.', true);
            }
        });
        $("#submitBtn").click(function(e) {
            const uuid = e.target.getAttribute("data-uuid");
            if (uuid) {
                $("#approvementModalHeader").html("Submit Assignment");
                $("#approvementModalSubmit").attr("data-method", "submit");
                $("#approvementModalSubmit").attr("data-uuid", uuid);
                _approvalModal.show();
            } else {
                showToast('No UUID Found. Try to click the button again.', true);
            }
        });
        $("#approvementModalSubmit").click(function(e) {
            handleSubmit(e);
        });
        $("#clearPrescriptionBtn").click(function(e) {
            // Clear prescription in view
        });
        $("#addMedsBtn").click(function(e) {
            medModal.toggle();
        });
    });
</script>