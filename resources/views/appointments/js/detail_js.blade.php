<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const _liveToast = document.getElementById("liveToast");
    const modal = new bootstrap.Modal('#appointmentsDetail', {});
    const uuid = "{{ request()->segment(3) }}";
    let liveToast;

    const getDetail = async (uuid) => {
        setLoading(true);
        await fetch(`/api/v1/appointment/get-detail/${uuid}`, {
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
            setLoading(false);
            const latestStatus = response.data[response.data.length - 1].status;
            if (latestStatus === "CANCELED" || latestStatus === "COMPLETED") {
                if (!$("#assignmentActions").hasClass("d-none")) {
                    $("#assignmentActions").addClass("d-none");
                }
            } else {
                $("#assignmentActions").removeClass("d-none");
            }

            $("#mainCard").addClass(getCurrentBorder(latestStatus));
            $("#currentBadge").html(getCurrentBadgeText(latestStatus));
            $("#statusCards").html(getStatusCards(response.data));
        }).catch(error => {
            showToast(error, true);
        })
    }

    const getStatusCards = (data) => {
        let html = '';

        data.map(d => {
            html += `
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h4>${getCurrentBadgeText(d.status)}</h4>
                                <p class="mb-0 fs-6 text-muted">${d.created_at} by ${d.created_by.name}</p>
                                <hr />
                                <label class="mb-1">Additional Note</label>
                                <div class="p-3 bg-body-secondary rounded">
                                    ${d.additional_note ? d.additional_note : '-'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })

        return html;
    }

    const getCurrentBorder = (status) => {
        switch (status) {
            case 'DOC_WAITING':
                return 'border-primary';
                break;
            case 'DOC_ASSIGNED':
                return 'border-primary';
                break;
            case 'PHAR_WAITING':
                return 'border-primary';
                break;
            case 'PHAR_ASSIGNED':
                return 'border-primary';
                break;
            case 'IN_PAYMENT':
                return 'border-info';
                break;
            case 'COMPLETED':
                return 'border-success';
                break;
            case 'CANCELED':
                return 'border-danger';
                break;

            default:
                return '';
                break;
        }
    }

    const getCurrentBadgeText = (status) => {

        switch (status) {
            case 'DOC_WAITING':
                return `
                    <div class="badge rounded-pill text-bg-primary">
                        <i class="bi bi-person-heart me-2"></i> Waiting for Doctor
                    </div>
                `;
                break;
            case 'DOC_ASSIGNED':
                return `
                    <div class="badge rounded-pill text-bg-primary">
                        <i class="bi bi-person-heart me-2"></i> Doctor Assigned
                    </div>
                `;
                break;
            case 'PHAR_WAITING':
                return `
                    <div class="badge rounded-pill text-bg-primary">
                        <i class="bi bi-capsule me-2"></i> Waiting for Pharmacy
                    </div>
                `;
                break;
            case 'PHAR_ASSIGNED':
                return `
                    <div class="badge rounded-pill text-bg-primary">
                        <i class="bi bi-capsule me-2"></i> Pharmacist Assigned
                    </div>
                `;
                break;
            case 'IN_PAYMENT':
                return `
                    <div class="badge rounded-pill text-bg-info text-light">
                        <i class="bi bi-wallet2 me-2"></i> In Payment
                    </div>
                `;
                break;
            case 'COMPLETED':
                return `
                    <div class="badge rounded-pill text-bg-success">
                        <i class="bi bi-check-lg me-2"></i> Completed
                    </div>
                `;
                break;
            case 'CANCELED':
                return `
                    <div class="badge rounded-pill text-bg-danger">
                        <i class="bi bi-x-lg me-2"></i> Canceled
                    </div>
                `;
                break;

            default:
                return 'Unknown';
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

    const setLoading = (isLoading) => {
        if (isLoading) {
            $("#loadingBadge").addClass('d-block').removeClass('d-none');
            $("#currentBadge").addClass('d-none').removeClass('d-block');
            $("#loadingCards").addClass('d-block').removeClass('d-none');
            $("#statusCards").addClass('d-none').removeClass('d-block');
        } else {
            $("#loadingBadge").addClass('d-none').removeClass('d-block');
            $("#currentBadge").addClass('d-block').removeClass('d-none');
            $("#loadingCards").addClass('d-none').removeClass('d-block');
            $("#statusCards").addClass('d-block').removeClass('d-none');
        }
    }

    const cancelAssignment = (e) => {
        $("#appointmentsDetailHead").html(`<i class="bi bi-x-lg text-danger me-2"></i>Cancel Assignment`);
        $("#status").val("CANCELED");
        modal.show();
    }

    const completeAssignment = (e) => {
        $("#appointmentsDetailHead").html(`<i class="bi bi-check-lg text-success me-2"></i>Complete Assignment`);
        $("#status").val("COMPLETED");
        modal.show();
    }

    const handleSubmit = async (type) => {
        $("#appointmentsDetailSubmitBtn").addClass("disabled");
        $("#appointmentsDetailSubmitBtn").prepend(
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const requestBody = new FormData($("#detailForm")[0]);
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
                modal.hide();
                showToast(response.message);
                getDetail(uuid);
            })
            .catch(error => {
                showToast(error, true);
                $("#appointmentsDetailSubmitBtn").removeClass("disabled");
                $("#submitLoading").remove();
            });
    }

    $(document).ready(function() {
        liveToast = new bootstrap.Toast(_liveToast);
        getDetail(uuid);

        $("#cancelBtn").click(function(e) {
            cancelAssignment(e)
        });
        $("#completeBtn").click(function(e) {
            completeAssignment(e)
        });

        const modalEl = document.getElementById("appointmentsDetail");
        modalEl.addEventListener("hidden.bs.modal", function(e) {
            $("#detailForm").trigger("reset");
            $("#appointmentsDetailSubmitBtn").removeClass("disabled");
            $("#submitLoading").remove();
        });
        $("#detailForm").submit(function(e) {
            e.preventDefault();
            handleSubmit();
        });
    });
</script>