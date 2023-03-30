<style>
    .selectize-control.single .selectize-input:after {
        display: none;
    }
</style>

<div class="modal fade" id="medSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="medSelectorModalHeader">Medicines & Services</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="medSelectorModalBody">
                <form id="medsSvcForm">
                    <input type="hidden" name="index" id="index" />
                    <div class="row gy-2">
                        <div class="col-sm-12 col-md-8">
                            <label for="itemId" class="form-label">Medicines or Services</label>
                            <select placeholder="Search..." id="itemId" name="item_id" style="flex-grow: 1"></select>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <label class="form-label" for="qty">Qty</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" id="subtractBtn"><i class="bi bi-dash-lg"></i></button>
                                <input type="text" class="form-control" name="qty" id="qty" value="1" readonly />
                                <button type="button" class="btn btn-primary" id="addBtn"><i class="bi bi-plus-lg"></i></button>
                            </div>

                        </div>
                        <div class="col-sm-12 col-md-12">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control" placeholder="Add notes..." rows="3" name="notes" id="notes"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="medsSvcForm" class="btn btn-primary" id="medSelectorModalSubmit">Add To Prescription</button>
                <button type="submit" form="medsSvcForm" class="btn btn-success" id="medSelectorModalSave">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    let itemSelector;
    let qtyField;
    const medSelectorForm = document.getElementById("medsSvcForm");
    const modal = document.getElementById("medSelectorModal");
    const subtractBtn = document.getElementById("subtractBtn");
    const addBtn = document.getElementById("addBtn");
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const initiateForm = (e) => {
        subtractBtnCheckState();
        if (e.explicitOriginalTarget.id === "addMedsBtn") {
            // hide save btn
            $("#medSelectorModalSave").addClass("d-none").removeClass("d-block");
            $("#medSelectorModalSubmit").addClass("d-block").removeClass("d-none");
            medSelectorForm.reset();
            const $itemSelector = $('#itemId').selectize({
                valueField: 'sku',
                labelField: 'label',
                searchField: 'label',
                options: [],
                onChange: function() {
                    $("#itemId").removeClass("is-invalid");
                    checkFormState();
                },
                load: function(query, callback) {
                    if (!query.length) return callback([]);
                    $.ajax({
                        type: 'get',
                        url: `/api/v1/appointment/item-list/${query}`,
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
                }
            });
            itemSelector = $itemSelector[0].selectize;
        } else {
            $("#medSelectorModalSubmit").addClass("d-none").removeClass("d-block");
            $("#medSelectorModalSave").removeClass("d-none").addClass("d-block");
            medSelectorForm.reset();
            const id = e.explicitOriginalTarget.id;
            const idx = id.indexOf('-');
            const index = id.substring(idx + 1);
            const rx = localStorage.getItem('prescription');
            if (rx) {
                let parsedRx = JSON.parse(rx);
                if (parsedRx[0].data.length > 0) {
                    parsedRx[0].data.some((d, idx) => {
                        if (idx === parseInt(index)) {
                            $("#index").val(idx);
                            $("#qty").val(d.qty);
                            $("#notes").val(d.notes);

                            const $itemSelector = $('#itemId').selectize({
                                valueField: 'sku',
                                labelField: 'label',
                                searchField: 'label',
                                options: [],
                                onChange: function() {
                                    $("#itemId").removeClass("is-invalid");
                                    checkFormState();
                                },
                                load: function(query, callback) {
                                    if (!query.length) return callback([]);
                                    $.ajax({
                                        type: 'get',
                                        url: `/api/v1/appointment/item-list/${query}`,
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
                                }
                            });
                            itemSelector = $itemSelector[0].selectize;
                            itemSelector.addOption({
                                sku: d.sku,
                                label: d.label
                            });
                            itemSelector.setValue(d.sku);
                            return true;
                        }
                    });
                }
            }
        }
    }

    const subtractBtnCheckState = () => {
        if (parseInt($("#qty").val()) === 1) {
            $("#subtractBtn").addClass("disabled");
        } else {
            $("#subtractBtn").removeClass("disabled");
        }
    }

    const checkFormState = () => {
        const itemIdVal = $("#itemId").val();
        const qtyVal = parseInt($("#qty").val());
        if (itemIdVal === "" || qtyVal < 1) {
            $("#medSelectorModalSubmit").addClass("disabled");
        } else {
            $("#medSelectorModalSubmit").removeClass("disabled");
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
                                        <button type="button" id="editBtn-${idx}" onclick="window.editItem('${uuid}', ${idx})" class="btn btn-sm btn-outline-primary rounded-circle"><i id="editIcon-${idx}" class="bi bi-pencil-square"></i></button>
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

    const handleAddRx = (e) => {
        const uuid = $("#medSelectorModalSubmit").get(0).getAttribute("data-uuid");
        const obj = {
            sku: $("#itemId").val(),
            label: itemSelector.getOption(itemSelector.getValue())[0].innerText,
            qty: $("#qty").val(),
            notes: $("#notes").val()
        }
        // add obj to localstorage
        const rx = localStorage.getItem("prescription");
        if (rx) {
            let parsedRx = JSON.parse(rx);
            const filtered = parsedRx.filter(a => a.uuid === uuid);
            if (filtered.length > 0) {
                // check if there is same SKU
                if (filtered[0].data.length > 0) {
                    let sameSku = false;
                    filtered[0].data.some(d => {
                        if (d.sku === obj.sku) {
                            sameSku = true;
                            return true;
                        }
                        return false;
                    });
                    if (sameSku) {
                        const item = parsedRx[0].data.find(item => item.sku === obj.sku);

                        item.qty = parseInt(item.qty) + parseInt(obj.qty);

                        const updatedPrescriptionString = JSON.stringify(parsedRx);
                        localStorage.setItem("prescription", updatedPrescriptionString);
                    } else {
                        filtered[0].data.push(obj);
                    }
                } else {
                    filtered[0].data.push(obj);
                }
            }
            localStorage.setItem("prescription", JSON.stringify(filtered));
            medModal.toggle();
            updateRxBody(uuid);
        }

        window.checkPrescription();
    }

    const handleItemSave = (e) => {
        const uuid = $("#medSelectorModalSave").get(0).getAttribute("data-uuid");
        const obj = {
            sku: $("#itemId").val(),
            label: itemSelector.getOption(itemSelector.getValue())[0].innerText,
            qty: $("#qty").val(),
            notes: $("#notes").val()
        }
        const rx = localStorage.getItem("prescription");
        if (rx) {
            let parsedRx = JSON.parse(rx);
            parsedRx[0].data[$("#index").val()] = obj;
            localStorage.setItem("prescription", JSON.stringify(parsedRx));
            medModal.toggle();
            updateRxBody(uuid);
        }

        window.checkPrescription();
    }

    medSelectorForm.addEventListener("submit", function(e) {
        e.preventDefault();
        if (e.submitter.id === "medSelectorModalSubmit") {
            handleAddRx(e);
        } else if (e.submitter.id === "medSelectorModalSave") {
            handleItemSave(e);
        }
    });
    modal.addEventListener("show.bs.modal", initiateForm);
    modal.addEventListener("shown.bs.modal", function() {
        subtractBtnCheckState();
        checkFormState();
    });
    modal.addEventListener("hidden.bs.modal", function() {
        itemSelector.clear();
        itemSelector.clearOptions();
        subtractBtnCheckState();
    });
    subtractBtn.addEventListener("click", function() {
        const currentVal = $("#qty").val();
        $("#qty").val(parseInt(currentVal) - 1);
        subtractBtnCheckState();
    });
    addBtn.addEventListener("click", function() {
        const currentVal = $("#qty").val();
        $("#qty").val(parseInt(currentVal) + 1);
        subtractBtnCheckState();
    });

    $(document).ready(function() {
        qtyField = IMask(document.getElementById("qty"), {
            mask: Number,
            signed: false,
        });

        // disable certain buttons
        subtractBtnCheckState();
    });
</script>