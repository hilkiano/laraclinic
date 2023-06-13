<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Transaction List'])

<body>
    @include('template.navbar', ['title' => 'Transaction List'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-12">
                            <div class="accordion" id="settingsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button show" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseTableSettings">
                                            Table Settings or Filters
                                        </button>
                                    </h2>
                                    <div id="collapseTableSettings" class="accordion-collapse collapse show"
                                        data-bs-parent="#settingsAccordion">
                                        <div class="accordion-body" style="overflow-x: auto;">
                                            <form id="tableForm">
                                                <div class="row gy-4">
                                                    <div class="col-sm-12 col-md-4">
                                                        <label for="filter" class="form-label">Patient</label>
                                                        <input autocomplete="off" type="text" class="form-control"
                                                            id="patientName" placeholder="Search by patient name...">
                                                    </div>
                                                    <div class="col-sm-12 col-md-8">
                                                        <div class="row gy-2">
                                                            <div class="col-sm-12 col-md-6">
                                                                <label for="fromDate" class="form-label">From</label>
                                                                <div class="input-group" id="fromDate_group"
                                                                    data-td-target-input="nearest"
                                                                    data-td-target-toggle="nearest" required>
                                                                    <input autocomplete="off" id="fromDate"
                                                                        name="fromDate" type="text"
                                                                        class="form-control" data-td-target="#fromDate"
                                                                        placeholder="Click here to select date..."
                                                                        required readonly>
                                                                    <span class="input-group-text"
                                                                        data-td-target="#fromDate"
                                                                        data-td-toggle="datetimepicker" required>
                                                                        <i class="bi bi-calendar"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12 col-md-6">
                                                                <label for="toDate" class="form-label">To</label>
                                                                <div class="input-group" id="toDate_group"
                                                                    data-td-target-input="nearest"
                                                                    data-td-target-toggle="nearest" required>
                                                                    <input autocomplete="off" id="toDate"
                                                                        name="toDate" type="text"
                                                                        class="form-control" data-td-target="#toDate"
                                                                        placeholder="Click here to select date..."
                                                                        required readonly>
                                                                    <span class="input-group-text"
                                                                        data-td-target="#toDate"
                                                                        data-td-toggle="datetimepicker" required>
                                                                        <i class="bi bi-calendar"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button id="resetFilterBtn" class="btn btn-light"
                                                            type="button"><i class="me-2 bi bi-eraser"></i>Reset
                                                            Filter</button>
                                                        <button id="applyFilterBtn" class="btn btn-primary ms-2"
                                                            type="submit"><i class="me-2 bi bi-check-lg"></i>Apply
                                                            Filter</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="col-12">
                                <div class="mt-0 mb-4">
                                    <div class="row">
                                        <div class="col-12">
                                            <h4>Summary Total Payment</h4>
                                        </div>
                                        <div class="col-12">
                                            <div class="row gy-4">
                                                <div class="col-sm-12 col-md-12 col-lg-3">
                                                    <div class="card bg-dark bg-gradient">
                                                        <div class="card-header">
                                                            <p class="mb-0 text-light">Cash</p>
                                                        </div>
                                                        <div class="card-body">
                                                            <h3 class="mb-0 text-light" id="cashTotal">Rp 0</h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-3">
                                                    <div class="card bg-dark bg-gradient">
                                                        <div class="card-header">
                                                            <p class="mb-0 text-light">Transfer Bank</p>
                                                        </div>
                                                        <div class="card-body">
                                                            <h3 class="mb-0 text-light" id="transferTotal">Rp 0</h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-3">
                                                    <div class="card bg-dark bg-gradient">
                                                        <div class="card-header">
                                                            <p class="mb-0 text-light">Debit Card</p>
                                                        </div>
                                                        <div class="card-body">
                                                            <h3 class="mb-0 text-light" id="debitTotal">Rp 0</h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-3">
                                                    <div class="card bg-dark bg-gradient">
                                                        <div class="card-header">
                                                            <p class="mb-0 text-light">Credit Card</p>
                                                        </div>
                                                        <div class="card-body">
                                                            <h3 class="mb-0 text-light" id="ccTotal">Rp 0</h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover caption-top"
                                        style="min-width: 1250px;">
                                        <thead class="table-dark">
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Created At</th>
                                                <th scope="col">Patient</th>
                                                <th scope="col">Receipt</th>
                                                <th scope="col">Total Amount</th>
                                                <th scope="col">Payment With</th>
                                                <th scope="col">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trxRows">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 mb-5 d-flex row">
                                    <div class="col">
                                        <p class="small text-muted">Total <span class="fw-semibold"
                                                id="allCount">0</span>
                                        </p>
                                    </div>
                                    <div class="col d-flex justify-content-end">
                                        <div id="pagination"></div>
                                    </div>
                                </div>
                                <p class="mb-0 fw-bold">Color Info:</p>
                                <p class="mb-0"><span class="badge bg-primary">&nbsp;</span> Created with doctor
                                    consultation.
                                </p>
                                <p class="mb-0"><span class="badge bg-danger">&nbsp;</span> A patient directly buy
                                    medicine/service to
                                    pharmacist/cashier.</p>
                                <p class="mb-6"><span class="badge bg-warning">&nbsp;</span> Created from online
                                    transaction.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

@include('toasts.live_toast')
@include('transaction.modal')
@include('transaction.js.list_js')
@include('template.footer')
