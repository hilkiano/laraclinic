<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Cashier'])

<body>
    @include('template.navbar', ['title' => 'Cashier'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-sm-12 col-md-8">
                            <ul class="nav nav-pills nav-fill" id="posTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="assignmentTab" data-bs-toggle="tab"
                                        data-bs-target="#assignmentTabPane" type="button"
                                        role="tab">Assignment</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="freeTab" data-bs-toggle="tab"
                                        data-bs-target="#freeTabPane" type="button" role="tab">Free
                                        Transaction</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="posTabContent">
                                <div class="tab-pane fade show active" id="assignmentTabPane" role="tabpanel"
                                    tabindex="0">
                                    <div id="loadingIndicator" class="mt-4 d-none">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"
                                                role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="selectedAssignment" class="p-0 m-0 d-none">
                                        <div id="cashierAction" style="z-index: 1"
                                            class="col-12 px-4 py-4 d-flex justify-content-between sticky-top gap-3 align-items-center">
                                            <div style="flex: 1">
                                                <div class="p-2 bg-body-secondary rounded">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-6">
                                                            <label class="form-label mb-0">Total</label>
                                                            <p class="fs-5 text-primary mb-0" id="totalPrice"></p>
                                                        </div>
                                                        <div class="col-sm-12 col-md-6">
                                                            <small class="text-muted mb-0">Amount Paid</small>
                                                            <p class="fs-6 mb-0 mt-0" id="amountPaid"></p>
                                                            <small class="text-muted mb-0">Change</small>
                                                            <p class="fs-6 mb-0" id="amountChange"></p>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="w-100">
                                                        <button id="cancelBtn" type="button"
                                                            class="btn btn-danger rounded-pill me-2"><i
                                                                class="bi bi-x-lg me-2"></i>Cancel</button>
                                                        <button id="submitBtn" type="button"
                                                            class="btn btn-success rounded-pill disabled"><i
                                                                class="bi bi-check-lg me-2"></i>Submit</button>
                                                    </div>
                                                    <div class="w-100 d-grid">
                                                        <button id="markAsCancelBtn" type="button"
                                                            class="btn btn-light rounded-pill text-danger-emphasis"><i
                                                                class="bi bi-x-lg me-2"></i>Mark As Cancelled</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="row gy-3">
                                                <div class="col-sm-12 col-md-6 col-lg-5">
                                                    <div class="row">
                                                        <div class="col-sm-5 col-md-5">
                                                            <img id="patientPotrait"
                                                                src="{{ asset('images/potrait-placeholder.png') }}"
                                                                class="img-thumbnail" />
                                                        </div>
                                                        <div class="col-sm-7 col-md-7">
                                                            <p class="mb-0 text-muted">Name</p>
                                                            <p id="patientName" class="mb-1 fw-bold"></p>
                                                            <p class="mb-0 text-muted">Address</p>
                                                            <p id="patientAddress" class="mb-1 fw-bold"></p>
                                                            <p class="mb-0 text-muted">Email</p>
                                                            <p id="patientEmail" class="mb-1 fw-bold"></p>
                                                            <p class="mb-0 text-muted">Phone No.</p>
                                                            <p id="patientPhone" class="mb-1 fw-bold"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6 col-lg-7">
                                                    <div class="row gy-3">
                                                        <div class="col-12 p-3 bg-body-secondary rounded mb-3">
                                                            <label class="form-label mb-2">Item List</label>
                                                            <ul class="list-group mb-3" id="rxBody">
                                                            </ul>
                                                        </div>
                                                        <form id="cashierForm" class="px-0 mb-5">
                                                            <div class="col-12 p-3 bg-body-secondary rounded">
                                                                <div class="mb-3">
                                                                    <label for="payment" class="form-label">Payment
                                                                        with</label>
                                                                    <div class="input-group">
                                                                        <select class="form-select" id="payment"
                                                                            name="payment" autocomplete="off">
                                                                            <option value="CASH">Cash</option>
                                                                            <option value="CREDIT_CARD">Credit Card
                                                                            </option>
                                                                            <option value="DEBIT_CARD">Debit Card
                                                                            </option>
                                                                            <option value="BANK_TRANSFER">Bank Transfer
                                                                            </option>
                                                                        </select>
                                                                        <button id="fullPriceBtn"
                                                                            class="btn btn-primary"
                                                                            style="z-index: 0">Full Price</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-2 gy-3">
                                                                    <div class="col-sm-12 col-lg-7">
                                                                        <label for="amount"
                                                                            class="form-label">Payment
                                                                            amount</label>
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input type="text" id="amount"
                                                                                name="amount" class="form-control"
                                                                                autocomplete="off">
                                                                        </div>
                                                                    </div>
                                                                    <div id="totalDiscountPctgDiv"
                                                                        class="col-sm-12 col-lg-5">
                                                                        <label for="totalDiscountPctg"
                                                                            class="form-label">Discount</label>
                                                                        <div class="input-group">
                                                                            <input type="text"
                                                                                id="totalDiscountPctg"
                                                                                class="form-control"
                                                                                autocomplete="off">
                                                                            <span class="input-group-text">%</span>
                                                                        </div>
                                                                    </div>
                                                                    <div id="totalDiscountAmtDiv"
                                                                        class="col-sm-12 col-lg-5">
                                                                        <label for="totalDiscountAmt"
                                                                            class="form-label">Discount</label>
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input type="text"
                                                                                id="totalDiscountAmt"
                                                                                class="form-control"
                                                                                autocomplete="off">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12">
                                                                        <label class="form-label">Discount Type</label>
                                                                    </div>
                                                                    <div class="col-sm-12 mt-0">
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input"
                                                                                type="radio" name="totalDiscType"
                                                                                id="pctgRadio" value="pctg">
                                                                            <label class="form-check-label"
                                                                                for="pctgRadio">Percentage</label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input"
                                                                                type="radio" name="totalDiscType"
                                                                                id="amtRadio" value="amt">
                                                                            <label class="form-check-label"
                                                                                for="amtRadio">Amount</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="freeTabPane" role="tabpanel" tabindex="0">
                                    @include('freepos.freepos')
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="row sticky-top gy-2" id="cardsRow">
                                <div class="col-12 px-4">
                                    <p class="fs-4 mb-0">Ready for Payment</p>
                                </div>
                                <form id="filterForm">
                                    <div class="col-12 mb-2">
                                        <div class="input-group px-3" id="fromDate_group"
                                            data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                                            <span class="input-group-text bg-dark-subtle" style="min-width: 70px"
                                                data-td-target="#fromDate" data-td-toggle="datetimepicker" required>
                                                From
                                            </span>
                                            <input autocomplete="off" id="fromDate" name="fromDate" type="text"
                                                class="form-control" data-td-target="#fromDate"
                                                placeholder="Transaction from..." required readonly>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group px-3 mb-3" id="toDate_group"
                                            data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                                            <span class="input-group-text bg-dark-subtle" style="min-width: 70px"
                                                data-td-target="#toDate" data-td-toggle="datetimepicker" required>
                                                To
                                            </span>
                                            <input autocomplete="off" id="toDate" name="toDate" type="text"
                                                class="form-control" data-td-target="#toDate" placeholder="to..."
                                                required readonly>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group mb-3 px-3">
                                            <input type="text" autocomplete="off" class="form-control"
                                                id="filterName" placeholder="Search by patient name">
                                            <button class="btn btn-outline-secondary" type="submit"
                                                form="filterForm" id="filterBtn">Search</button>
                                        </div>
                                    </div>
                                </form>
                                <div style="height: calc(100vh - 290px); overflow: scroll;">
                                    <div class="col-12">
                                        <div id="loadingList" class="d-flex flex-column flex-nowrap overflow-scroll">
                                            @foreach ([0, 1, 2, 3, 4, 5, 6, 7, 8, 9] as $idx => $card)
                                                <div class="card mb-4 flex-shrink-0 border-0" style="width: 100%">
                                                    <div class="card-body p-0">
                                                        <span class="placeholder-glow">
                                                            <span
                                                                class="placeholder border-0 bg-dark-subtle col-12 rounded"
                                                                style="height: 247.733px"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div id="myList"
                                            class="d-none flex-column flex-nowrap overflow-scroll px-3">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<div class="modal fade" id="approvementModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="approvementModalHeader">Cancel Assignment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="approvementModalBody">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                    id="approvementModalSubmit">Yes</button>
            </div>
        </div>
    </div>
</div>

<div id="cancelAssignmentModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelAssignmentHead">Cancel Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cancelAssignmentBody">
                <form id="cancelAssignmentForm">
                    <input type="hidden" name="uuid" id="cancelUuid" />
                    <input type="hidden" id="cancelStatus" name="status" />
                    <div class="row gy-2">
                        <div class="col-12">
                            <label for="additionalNote" class="form-label">Add Notes</label>
                            <textarea autocomplete="off" class="form-control" name="additional_note" rows="3" autocomplete="off"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                    id="cancelAssignmentCloseBtn">Cancel</button>
                <button type="submit" form="detailForm" class="btn btn-primary"
                    id="cancelAssignmentSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

</html>
@include('toasts.live_toast')
@include('cashier_js')
@include('template.footer')
