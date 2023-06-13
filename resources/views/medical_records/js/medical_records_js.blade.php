<script type="module">
    const _liveToast = document.getElementById("liveToast");
    const _prescriptionModal = document.getElementById("prescriptionModal");
    let prescriptionModal;
    let liveToast;
    let tableData;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const getList = async (p) => {
        showTableLoading(8, "#medicalRecordsRow");
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

        await fetch("/api/v1/records/list", {
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
            $("#medicalRecordsRow").empty();
            if (response.data.length > 0) {
                tableData = response.data;
                response.data.map((row, i) => {
                    if (row.patient) {
                        iteratePaginationData(page, row, i)
                    }

                });
                $("#allCount").html(`${response.count}`);
                $("#pagination").html(makePagination(response));
            } else {
                tableData = [];
                let html = `
                    <tr>
                        <td colspan="8">No Data.</td>
                    </tr>
                `;

                $("#medicalRecordsRow").append(html);
            }
        }).catch(error => {
            showToast(error, true);
        })
    }

    const iteratePaginationData = (page, row, i) => {
        const num = page * 10;
        const iteration = i + 1;
        let html;
        html += `
            <tr class="${row.source === "DOCTOR" ? 'table-primary' : row.source === "SELF" ? 'table-danger' : row.source === "ONLINE" ? 'table-warning' : 'table-secondary'}">
                <td scope="row">${ num + iteration }</td>
                <td>${ row.transaction_id ? `${row.transaction_id} <i class="bi bi-check2-circle text-success"></i>` : '<i class="bi bi-x-circle text-danger"></i>' }</td>
                <td>${ row.medical_record ? row.medical_record.record_no : '-' }</td>
                <td>${ row.patient.name }</td>
                <td style="text-align: center"><button class="btn btn-sm btn-outline-primary me-1" onclick="window.showPrescription(${i})">See Prescription</button></td>
                <td>${ getNotes(row) }</td>
                <td>${ row.created_by }</td>
                <td>${ row.created_at }</td>
            </tr>
        `;



        function getNotes(row) {
            if (row.medical_record) {
                return row.medical_record.additional_note ? row.medical_record.additional_note : '-'
            } else {
                return row.additional_info ? row.additional_info : '-'
            }
        }

        $("#medicalRecordsRow").append(html);
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

    const showPrescription = (index) => {
        const list = tableData[index].list;
        const source = tableData[index].source;
        let html = '';
        $("#prescriptionModalRow").html(html);
        list.map(rx => {
            html += `
                <tr class="${source === "DOCTOR" ? 'table-primary' : source === "SELF" ? 'table-danger' : source === "ONLINE" ? 'table-warning' : 'table-secondary'}">
                    <td>${rx.sku}</td>
                    <td>${rx.label}</td>
                    <td style="text-align: right">${rx.qty}</td>
                    <td>${rx.notes}</td>
                    <td>${tableData[index].created_by}</td>
                    <td><h5><span class="badge ${source === "DOCTOR" ? 'text-bg-primary' : source === "SELF" ? 'text-bg-danger' : source === "ONLINE" ? 'text-bg-warning' : 'text-bg-secondary'}">${source}</span></h5></td>
                </tr>
            `;
        });
        $("#prescriptionModalRow").html(html);
        prescriptionModal.toggle();
    }

    window.getList = getList;
    window.showPrescription = showPrescription;

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);
        prescriptionModal = new bootstrap.Modal(_prescriptionModal);

        // Check for additional URL
        const url = new URL(window.location.href);
        const searchParams = url.searchParams;
        const name = searchParams.get("name");
        const id = searchParams.get("id");
        if (name) {
            $("#filterCol").val("name");
            $("#filterVal").val(name);
        }
        if (id) {
            $("#filterCol").val("id");
            $("#filterVal").val(id);
        }

        getList();

        $("#tableForm").submit(function(e) {
            e.preventDefault();
            getList();
        });
        $("#resetFilterBtn").click(function(e) {
            $("#tableForm").trigger("reset");
            getList();
        });
    });
</script>
