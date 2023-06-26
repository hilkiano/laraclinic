<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Medical Records'])

<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="d-flex flex-column w-100">
                <div class="container mt-4">
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
                                                    <div class="col-sm-12">
                                                        <label for="filter" class="form-label">Filter</label>
                                                        <div class="input-group">
                                                            <select style="max-width: 200px" class="form-select"
                                                                id="filterCol" autocomplete="off">
                                                                <option value="name">Patient Name</option>
                                                                <option value="id">Patient ID</option>
                                                                <option value="code">Patient Code</option>
                                                            </select>
                                                            <input autocomplete="off" type="text"
                                                                class="form-control" id="filterVal"
                                                                placeholder="Type anything...">
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
                        <div class="col-12 mb-4">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover caption-top" style="min-width: 1600px;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Transaction</th>
                                            <th scope="col" style="width: 350px">Record No.</th>
                                            <th scope="col" style="width: 180px">Patient</th>
                                            <th scope="col" style="width: 150px; text-align: center">Prescription
                                            </th>
                                            <th scope="col">Notes</th>
                                            <th scope="col" style="width: 180px">Created By</th>
                                            <th scope="col" style="width: 180px">Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody id="medicalRecordsRow">
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 mb-2 d-flex row">
                                <div class="col">
                                    <p class="small text-muted">Total <span class="fw-semibold" id="allCount">0</span>
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
</body>

<div id="prescriptionModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prescriptionModalHead">Prescription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prescriptionModalBody">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover caption-top" style="min-width: 1300px;">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 100px">SKU</th>
                                <th scope="col" style="width: 200px">Name</th>
                                <th scope="col" style="width: 50px; text-align: right">Qty</th>
                                <th scope="col" style="width: 300px">Notes</th>
                                <th scope="col" style="width: 150px">Created By</th>
                                <th scope="col" style="width: 100px">Type</th>
                            </tr>
                        </thead>
                        <tbody id="prescriptionModalRow">
                        </tbody>
                    </table>
                </div>
                <div class="my-3">
                    <p class="mb-0 fw-bold">Color Info:</p>
                    <p class="mb-0"><span class="badge bg-primary">&nbsp;</span> Created with doctor consultation.
                    </p>
                    <p class="mb-0"><span class="badge bg-danger">&nbsp;</span> A patient directly buy
                        medicine/service to
                        pharmacist/cashier.</p>
                    <p class="mb-6"><span class="badge bg-warning">&nbsp;</span> Created from online transaction.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                    id="prescriptionModalCloseBtn">Close</button>
            </div>
        </div>
    </div>
</div>

</html>

@include('medical_records.js.medical_records_js')
@include('toasts.live_toast')
@include('template.footer')
