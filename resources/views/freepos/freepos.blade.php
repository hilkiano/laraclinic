<style>
    .selectize-control.single .selectize-input:after {
        display: none;
    }
</style>

<div class="mt-4">
    <div id="free-cashierAction" style="z-index: 1"
        class="col-12 px-4 py-4 d-flex justify-content-between sticky-top gap-3 align-items-center">
        <div style="flex: 1">
            <div class="p-2 bg-body-secondary rounded">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <label class="form-label mb-0">Total</label>
                        <p class="fs-5 text-primary mb-0" id="free-totalPrice"></p>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <small class="text-muted mb-0">Amount Paid</small>
                        <p class="fs-6 mb-0 mt-0" id="free-amountPaid"></p>
                        <small class="text-muted mb-0">Change</small>
                        <p class="fs-6 mb-0" id="free-amountChange"></p>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="d-flex flex-column gap-2">
                <div class="w-100">
                    <button id="free-resetBtn" type="button" class="btn btn-light rounded-pill me-2"><i
                            class="bi me-2 bi-arrow-counterclockwise"></i>Reset</button>
                    <button id="free-submitBtn" type="button" class="btn btn-success rounded-pill disabled"><i
                            class="bi bi-check-lg me-2"></i>Submit</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="p-3 bg-body-secondary rounded">
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-5">
                    <div class="row">
                        <div class="col-sm-5 col-md-5">
                            <img id="free-patientPotrait" src="{{ asset('images/potrait-placeholder.png') }}"
                                class="img-thumbnail" />
                        </div>
                        <div class="col-sm-7 col-md-7">
                            <p class="mb-0 text-muted">Name</p>
                            <p id="free-patientName" class="mb-1 fw-bold"></p>
                            <p class="mb-0 text-muted">Address</p>
                            <p id="free-patientAddress" class="mb-1 fw-bold"></p>
                            <p class="mb-0 text-muted">Email</p>
                            <p id="free-patientEmail" class="mb-1 fw-bold"></p>
                            <p class="mb-0 text-muted">Phone No.</p>
                            <p id="free-patientPhone" class="mb-1 fw-bold"></p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-md-7">
                    <label for="patient" class="form-label">Patient</label>
                    <select placeholder="Search..." id="patient" name="patient_id"
                        style="flex-grow: 1; z-index: 0;"></select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="p-3 bg-body-secondary rounded">
            <div class="row">
                <div class="col-12">
                    <ul class="list-group mb-3" id="free-rxBody">
                    </ul>
                </div>

                <div class="col-sm-12 col-md-6 offset-md-6 d-flex justify-content-end">
                    <button class="btn btn-dark rounded-pill" id="addItemBtn"><i class="bi bi-plus-lg me-2"></i>Add
                        Item</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="col-sm-12 offset-md-6 col-md-6 p-3 bg-body-secondary rounded">
            <div class="row mb-2 gy-3">
                <div class="col-sm-12 col-lg-7 d-none">
                    <label for="free-amount" class="form-label">Payment
                        amount</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="free-amount" name="amount" class="form-control" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div id="free-totalDiscountPctgDiv">
                        <label for="free-totalDiscountPctg" class="form-label">Total Discount</label>
                        <div class="input-group">
                            <input type="text" id="free-totalDiscountPctg" class="form-control" autocomplete="off">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div id="free-totalDiscountAmtDiv">
                        <label for="free-totalDiscountAmt" class="form-label">Total Discount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="free-totalDiscountAmt" class="form-control" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="col-sm-12">
                        <label class="form-label">Discount Type</label>
                    </div>
                    <div class="col-sm-12 mt-0">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="free-totalDiscType"
                                id="free-pctgRadio" value="pctg">
                            <label class="form-check-label" for="free-pctgRadio">Percentage</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="free-totalDiscType"
                                id="free-amtRadio" value="amt">
                            <label class="form-check-label" for="free-amtRadio">Amount</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('freepos.payment')
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="checkoutModalHeader">Make Transaction</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="checkoutModalBody">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal"
                    id="checkoutModalSubmit">Yes</button>
            </div>
        </div>
    </div>
</div>

@include('freepos.js.freepos_js')
@include('freepos.meds_selector')
