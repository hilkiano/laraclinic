<script type="module">
    const _liveToast = document.getElementById("liveToast");
    const _medModal = document.getElementById("medSelectorModal");
    const _approvalModal = new bootstrap.Modal("#approvementModal", {});
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let phoneNumberImask;
    let liveToast;
    let medModal;
    let prescription;
    if (_medModal) {
        medModal = new bootstrap.Modal('#medSelectorModal', {});
    }
    let patientResults = null;
    let selectedPatient = null;
    const placeholderUuid = "{{ $uuid }}";

    const handleSearch = async () => {
        setLoading(true);
        const param = {
            name: $("#filterName").val() !== "" ? $("#filterName").val() : undefined,
            phone_number: $("#filterPhone").val() !== "" ? $("#filterPhone").val() : undefined,
            address: $("#filterAddress").val() !== "" ? $("#filterAddress").val() : undefined,
            record_no: $("#filterRecordNo").val() !== "" ? $("#filterRecordNo").val() : undefined,
        };
        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }
        await fetch("/api/v1/online-trx/find-patient", {
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
            // Make patient cards
            makeCards(response.data);
            patientResults = response.data;
            setLoading(false);

            if (response.data.length === 50) {
                showToast(`Search result: ${response.data.length}+ patients. Please add more query.`, false);
            } else if (response.data.length !== 1 && response.data.length !== 0) {
                showToast(`Search result: ${response.data.length} patients. `, false);
            } else if (response.data.length === 0) {
                showToast('No data.', true);
            }
        }).catch(error => {
            patientResults = null;
            setLoading(false);
            showToast(error, true);
        })
    }

    const makeCards = (data) => {
        let html = '';

        data.map((row, i) => {
            html += `
            <div class="card ${ i + 1 === data.length ? 'mx-4' : 'ms-4' } shadow flex-shrink-0" style="width: 360px">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-4">
                                <img src="${row.patient_potrait ? row.patient_potrait.url[row.patient_potrait.url.length - 1] : `{{ asset('images/potrait-placeholder.png') }}`}" class="img-thumbnail" />
                            </div>
                            <div class="col-8 text-truncate">
                                <p class="mb-0 text-muted">Patient</p>
                                <span class="fw-bold">${row.name}</span>
                                <p class="mb-0 text-muted">Additional Note</p>
                                <div class="p-3 bg-body-secondary rounded text-truncate">
                                    <span class="fw-bold">${row.additional_note ? row.additional_note : '-'}</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item d-grid p-2">
                        <button onclick="window.selectPatient(event)" type="button" data-id="${row.id}" class="btn btn-primary take-assignment-btn">Select</button>
                    </li>
                </ul>
            </div>
            `;
        });

        $("#myList").html(html);
    }

    const selectPatient = (evt) => {
        const patientId = evt.target.getAttribute("data-id");
        const patient = patientResults.find((p) => p.id === parseInt(patientId))

        selectedPatient = patient;

        $("#patientPotrait").attr("src", patient.patient_potrait ? patient.patient_potrait.url[patient.patient_potrait.url.length - 1] : `{{ asset('images/potrait-placeholder.png') }}`);
        $("#patientName").html(patient.name);
        $("#patientAddress").html(patient.address ? patient.address : '-');
        $("#patientEmail").html(patient.email ? patient.email : '-');
        $("#patientPhone").html(patient.phone_number ? `+62 ${patient.phone_number}` : '-');
        $("#patientBirthDate").html(patient.birth_date ? patient.birth_date : '-');
        $("#patientWeight").html(patient.weight ? `${patient.weight} kg` : '-');
        $("#patientHeight").html(patient.height ? `${patient.height} cm` : '-');
        $("#patientAge").html(patient.age ? `${patient.age} tahun` : '-');
        $("#patientDetails").html(patient.additional_note ? patient.additional_note : '-');
        if ($("#showMedicalRecordsBtn").length) {
            $("#showMedicalRecordsBtn").attr("href", encodeURI(`/medical_records?id=${patient.id}`));
        }

        // Medical records row
        let medRows = '';
        if (patient.prescriptions) {
            if (patient.prescriptions.length > 0) {
                medRows = createMedicalRows(patient.prescriptions);
            } else {
                medRows = `
                <tr>
                    <td colspan="5">No Data.</td>
                </tr>
            `;
            }
        }

        $("#medicalRecordsRow").html(medRows);

        // Prescription
        const rx = localStorage.getItem("prescription");
        if (rx) {
            let parsedRx = JSON.parse(rx);
            let filtered = parsedRx.filter(a => a.uuid === placeholderUuid);
            if (filtered.length > 0) {
                localStorage.setItem("prescription", JSON.stringify([]));
                const clearedRx = JSON.parse(localStorage.getItem("prescription"));
                let obj = {
                    uuid: placeholderUuid,
                    data: []
                };
                clearedRx.push(obj);
                localStorage.setItem("prescription", JSON.stringify(clearedRx));
            } else {
                let obj = {
                    uuid: placeholderUuid,
                    data: []
                };
                parsedRx.push(obj);
                localStorage.setItem("prescription", JSON.stringify(parsedRx));
            }
        }

        // Show patient row
        if ($("#patientRow").hasClass("d-none")) {
            $("#patientRow").removeClass("d-none");
        }
    }

    const createMedicalRows = (data) => {
        let html = '';
        data.map((d, idx) => {
            html += `
                <tr class="${d.source === "DOCTOR" ? 'table-primary' : d.source === "SELF" ? 'table-danger' : d.source === "ONLINE" ? 'table-warning' : 'table-secondary'}">
                    <td>${idx + 1}</td>
                    <td>${d.medical_record ? d.medical_record.record_no : "-"}</td>
                    <td>${d.created_at}</td>
                    <td>${d.created_by}</td>
                    <td class="text-center"><button type="button" onclick="window.getPrescription(event, ${d.id})" class="btn btn-outline-primary btn-sm">Copy Prescription</button></td>
                </tr>
            `;
        });
        return html;
    }

    window.selectPatient = selectPatient;

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

    const updateRxBody = (uuid) => {
        // check uuid
        const parsedRx = JSON.parse(localStorage.getItem("prescription"));
        const filtered = parsedRx.filter(a => a.uuid === uuid);
        let html = '';
        if (filtered.length > 0) {
            filtered.map(a => {
                if (a.data.length > 0) {
                    html = `
                        <ol class="list-group list-group-numbered list-group-flush">
                        ${a.data.map((d, idx) => {
                            return `<li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                                    <div class="ms-2 w-100">
                                        <div class="fw-bold mb-2">
                                        ${d.label}
                                        </div>
                                        <div class="p-3 bg-body-secondary rounded text-break">
                                        ${d.notes ? d.notes : '-'}
                                        </div>
                                        <div class="mt-2 mb-2">
                                        <button type="button" id="editBtn-${idx}" onclick="window.editItem('${uuid}', ${idx}, event)" class="btn btn-sm btn-outline-primary rounded-circle"><i id="editIcon-${idx}" class="bi bi-pencil-square"></i></button>
                                        <button type="button" id="delBtn-${idx}" onclick="window.deleteItem('${uuid}', ${idx})" class="btn btn-sm btn-outline-danger rounded-circle ms-1"><i id="delIcon-${idx}" class="bi bi-trash3"></i></button>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary rounded-pill fs-6">x ${d.qty}</span>
                                    </div>
                            </li>`
                        }).join(" ")}
                        </ol>
                    `;
                } else {
                    html = '<p class="mb-0 text-muted">Nothing added yet.</p>';
                }
            });
        } else {
            html = '<p class="mb-0 text-muted">Nothing added yet.</p>';
        }

        $("#rxBody").html(html);
    }

    const editItem = (uuid, idx, event) => {
        $('#medSelectorModalSave').attr('data-uuid', uuid);
        medModal.toggle(event.target.id);
    }

    const deleteItem = (uuid, idx) => {
        const rx = localStorage.getItem('prescription');
        if (rx) {
            let parsedRx = JSON.parse(rx);
            const filtered = parsedRx.filter(a => a.uuid === uuid);
            if (filtered[0].data.length > 0) {
                filtered[0].data.splice(idx, 1);
            }
            localStorage.setItem('prescription', JSON.stringify(parsedRx));
            updateRxBody(uuid);
        }
        checkPrescription();
    }

    const getPrescription = async (event, id) => {
        const button = event.target;
        button.classList.add('disabled');
        await fetch(`/api/v1/records/prescription/${id}`, {
            headers: {
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "get",
            credentials: "same-origin",
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
            const prescription = response.data;
            const storageRx = localStorage.getItem('prescription');
            if (storageRx) {
                const parsedRx = JSON.parse(storageRx);
                parsedRx[0].data = prescription;

                localStorage.setItem('prescription', JSON.stringify(parsedRx));
                updateRxBody(placeholderUuid);
            }
            showToast(response.message, false);
            button.classList.remove('disabled');
            checkPrescription();
        }).catch(error => {
            showToast(error, true);
            button.classList.remove('disabled');
        })
    }

    const handleSubmit = async (evt) => {
        const btn = document.getElementById("approvementModalSubmit");
        btn.classList.add('disabled');
        btn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );

        const storageRx = localStorage.getItem('prescription');
        const parsedRx = JSON.parse(storageRx);
        const prescription = parsedRx[0].data;
        const param = {
            patient_id: selectedPatient.id,
            prescription: JSON.stringify(prescription),
            notes: $("#additionalNotes").val()
        }
        const formData = new FormData();
        for (var key in param) {
            if (typeof param[key] !== "undefined") {
                formData.append(key, param[key]);
            }
        }
        await fetch("/api/v1/online-trx/make-trx", {
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

            // Reset everything
            $("#cancelBtn").click();
            $("#clearFilterBtn").click();
            $("#additionalNotes").val("");

            _approvalModal.hide();
        }).catch(error => {
            showToast(error, true);
            btn.classList.remove('disabled');
            document.getElementById("submitLoading").remove();
            _approvalModal.hide();
        })
    }

    window.medModal = medModal;
    window.editItem = editItem;
    window.deleteItem = deleteItem;
    window.checkPrescription = checkPrescription;
    window.getPrescription = getPrescription;

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);
        localStorage.setItem('prescription', JSON.stringify([]));

        phoneNumberImask = IMask(document.getElementById("filterPhone"), {
            mask: Number,
            signed: false
        });

        $("#filterForm").submit(function(e) {
            e.preventDefault();
            handleSearch();
        });

        $("#clearFilterBtn").click(function() {
            $("#filterForm")[0].reset();
            $("#myList").addClass("d-none").empty();
            patientResults = null;
        });

        $("#cancelBtn").click(function() {
            // Hide patient row
            if (!$("#patientRow").hasClass("d-none")) {
                $("#patientRow").addClass("d-none");
            }
            selectedPatient = null;
            $("#clearPrescriptionBtn").click();
        });

        $("#submitBtn").click(function(e) {
            if (placeholderUuid) {
                $("#approvementModalHeader").html("Submit Assignment");
                $("#approvementModalSubmit").attr("data-method", "submit");
                $("#approvementModalSubmit").attr("data-uuid", placeholderUuid);
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

        $("#clearPrescriptionBtn").click(function(e) {
            const parsedRx = JSON.parse(localStorage.getItem("prescription"));
            let filtered = parsedRx.filter(a => a.uuid === placeholderUuid);
            if (filtered.length > 0) {
                localStorage.setItem("prescription", JSON.stringify([]));
                const clearedRx = JSON.parse(localStorage.getItem("prescription"));
                let obj = {
                    uuid: placeholderUuid,
                    data: []
                };
                clearedRx.push(obj);
                localStorage.setItem("prescription", JSON.stringify(clearedRx));
            }
            updateRxBody(placeholderUuid);
            checkPrescription();
        });

        $("#addMedsBtn").click(function(e) {
            medModal.toggle(e.target.id);
            if (placeholderUuid) {
                $('#medSelectorModalSubmit').attr('data-uuid', placeholderUuid);
            } else {
                showToast('No UUID Found. Try to click the button again.', true);
            }
        });

        $("#medSelectorModalHeader").html("Select Medicine");
        $("#itemIdLabel").html("Medicine");
    })
</script>
