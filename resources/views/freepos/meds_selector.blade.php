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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="closeModalIconBtn"></button>
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
                                <button type="button" class="btn btn-primary" id="subtractBtn"><i
                                        class="bi bi-dash-lg"></i></button>
                                <input type="text" class="form-control" name="qty" id="qty" value="1"
                                    readonly />
                                <button type="button" class="btn btn-primary" id="addBtn"><i
                                        class="bi bi-plus-lg"></i></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    id="closeModalBtn">Close</button>
                <button type="submit" form="medsSvcForm" class="btn btn-primary" id="medSelectorModalSubmit">Add To
                    Prescription</button>
                <button type="submit" form="medsSvcForm" class="btn btn-success"
                    id="medSelectorModalSave">Save</button>
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

    const initiateForm = (target) => {
        subtractBtnCheckState();
        if (target.id === "addItemBtn") {
            // hide save btn
            $("#medSelectorModalSave").addClass("d-none").removeClass("d-block");
            $("#medSelectorModalSubmit").addClass("d-block").removeClass("d-none");
            medSelectorForm.reset();
            const $itemSelector = $('#itemId').selectize({
                valueField: 'sku',
                labelField: 'label',
                searchField: 'label',
                options: [],
                onChange: function(e) {
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
        } else if (target.id.indexOf("btnFreeEdit") || target.id.indexOf("iconFreeEdit")) {
            $("#medSelectorModalSubmit").addClass("d-none").removeClass("d-block");
            $("#medSelectorModalSave").removeClass("d-none").addClass("d-block");
            medSelectorForm.reset();

            // get index
            const idx = target.id.indexOf('-');
            const index = target.id.substring(idx + 1);
            const rx = localStorage.getItem('freePrescription');
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

    const checkFormState = () => {
        const itemIdVal = $("#itemId").val();
        const qtyVal = parseInt($("#qty").val());
        if (itemIdVal === "" || qtyVal < 1) {
            $("#medSelectorModalSubmit").addClass("disabled");
        } else {
            $("#medSelectorModalSubmit").removeClass("disabled");
        }
    }

    const subtractBtnCheckState = () => {
        if (parseInt($("#qty").val()) === 1) {
            $("#subtractBtn").addClass("disabled");
        } else {
            $("#subtractBtn").removeClass("disabled");
        }
    }

    const handleAddRx = (e) => {
        const obj = {
            sku: $("#itemId").val(),
            label: itemSelector.getOption(itemSelector.getValue())[0].innerText,
            qty: $("#qty").val(),
            notes: $("#notes").val(),
            price: null,
            discount_type: "pctg",
            discount_value: 0
        }

        // Get item price
        $.ajax({
            type: 'get',
            url: `/api/v1/appointment/item-price/${obj.sku}`,
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
                const price = res.data;
                obj.price = price;

                // Update local storage
                const rx = localStorage.getItem("freePrescription");
                if (rx) {
                    let parsedRx = JSON.parse(rx);
                    if (parsedRx[0].data.length > 0) {
                        let sameSku = false;
                        parsedRx[0].data.some(d => {
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
                            localStorage.setItem("freePrescription", updatedPrescriptionString);
                        } else {
                            parsedRx[0].data.push(obj);
                        }
                    } else {
                        parsedRx[0].data.push(obj);
                    }
                    localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
                    medSelectorModal.toggle();
                    window.updateRxBody();
                }
            }
        })
    }

    const handleItemSave = (e) => {
        const obj = {
            sku: $("#itemId").val(),
            label: itemSelector.getOption(itemSelector.getValue())[0].innerText,
            qty: $("#qty").val(),
            notes: $("#notes").val(),
            price: null,
            discount_type: "pctg",
            discount_value: 0
        }

        $.ajax({
            type: 'get',
            url: `/api/v1/appointment/item-price/${obj.sku}`,
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
                const price = res.data;
                obj.price = price;

                const rx = localStorage.getItem("freePrescription");
                if (rx) {
                    let parsedRx = JSON.parse(rx);
                    // Check if there is an item with same SKU already inside data. If found, just delete and add quantity to said item.
                    parsedRx[0].data[$("#index").val()] = obj;
                    localStorage.setItem("freePrescription", JSON.stringify(parsedRx));
                    medSelectorModal.toggle();
                    window.updateRxBody();
                }
            }
        })
    }

    medSelectorForm.addEventListener("submit", function(e) {
        e.preventDefault();
        if (e.submitter.id === "medSelectorModalSubmit") {
            handleAddRx(e);
            window.checkSubmitBtn();
        } else if (e.submitter.id === "medSelectorModalSave") {
            handleItemSave(e);
            window.checkSubmitBtn();
        }
    });
    modal.addEventListener("show.bs.modal", function(event) {
        initiateForm(event.relatedTarget);
    });
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

        subtractBtnCheckState();
    });
</script>
