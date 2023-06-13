<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Online Transaction'])

<body>
    @include('template.navbar', ['title' => 'Online Transaction'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-12">
                            <div class="accordion" id="searchAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button show" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseSearch">
                                            Find Patient
                                        </button>
                                    </h2>
                                    <div id="collapseSearch" class="accordion-collapse collapse show"
                                        data-bs-parent="#searchAccordion">
                                        <div class="accordion-body" style="overflow-x: auto;">
                                            <form id="filterForm">
                                                <div class="row gy-4">
                                                    <div class="col-sm-12 col-md-12 col-lg-4">
                                                        <label for="filter" class="form-label">Name</label>
                                                        <input autocomplete="off" type="text" class="form-control"
                                                            id="filterName" />
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 col-lg-4">
                                                        <label for="filter" class="form-label">Phone Number</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">+62</span>
                                                            <input autocomplete="off" type="text"
                                                                class="form-control" id="filterPhone" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 col-lg-4">
                                                        <label for="filter" class="form-label">Address</label>
                                                        <input autocomplete="off" type="text" class="form-control"
                                                            id="filterAddress" />
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 col-lg-4">
                                                        <label for="filter" class="form-label">Medical Record
                                                            Number</label>
                                                        <input autocomplete="off" type="text" class="form-control"
                                                            id="filterRecordNo" />
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button id="clearFilterBtn" class="btn btn-light"
                                                            type="button"><i class="me-2 bi bi-eraser"></i>Clear
                                                            Result</button>
                                                        <button id="applyFilterBtn" class="btn btn-primary ms-2"
                                                            type="submit"><i
                                                                class="me-2 bi bi-search"></i>Find</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid p-0 mt-4">
                        <div id="loadingList" class="d-none flex-row flex-nowrap overflow-scroll pb-4">
                            @foreach ([0, 1, 2, 3, 4, 5, 6, 7, 8, 9] as $idx => $card)
                                <div class="card ms-4 {{ $idx === 9 ? 'me-4' : '' }} flex-shrink-0 border-0"
                                    style="width: 360px">
                                    <div class="card-body p-0">
                                        <span class="placeholder-glow">
                                            <span class="placeholder border-0 bg-dark-subtle col-12 rounded"
                                                style="height: 196.167px"></span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="myList" class="d-none flex-row flex-nowrap overflow-scroll pb-4">

                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div id="assignmentAction" style="z-index: 1"
                            class="col-12 px-4 py-4 d-flex justify-content-end sticky-top">
                            <div class="row w-100 gx-0">
                                <div class="col d-flex justify-content-end align-items-center gap-2">
                                    <button id="cancelBtn" type="button" class="btn btn-danger rounded-pill d-flex"><i
                                            class="bi bi-x-lg me-2"></i>Cancel</button>
                                    <button id="submitBtn" type="button"
                                        class="btn btn-success rounded-pill disabled d-flex"><i
                                            class="bi bi-check-lg me-2"></i>Submit</button>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none mt-3" id="patientRow">
                            <div class="col-sm-12 col-md-12 col-lg-7">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-12 col-md-3">
                                                <img id="patientPotrait"
                                                    src="{{ asset('images/potrait-placeholder.png') }}"
                                                    class="img-thumbnail" />
                                            </div>
                                            <div class="col-sm-12 col-md-9">
                                                <p class="mb-0 text-muted">Name</p>
                                                <p id="patientName" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Address</p>
                                                <p id="patientAddress" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Email</p>
                                                <p id="patientEmail" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Phone No.</p>
                                                <p id="patientPhone" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Birth Date</p>
                                                <p id="patientBirthDate" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Weight</p>
                                                <p id="patientWeight" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Height</p>
                                                <p id="patientHeight" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Age</p>
                                                <p id="patientAge" class="mb-1 fw-bold"></p>
                                                <p class="mb-0 text-muted">Details</p>
                                                <p id="patientDetails" class="mb-1 fw-bold"></p>
                                            </div>
                                            <hr class="mt-3 mb-3" />
                                            <div class="col-12">
                                                <p class="mb-1 fw-bold">Medical Records</p>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover caption-top"
                                                        style="min-width: 500px;">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th scope="col">#</th>
                                                                <th scope="col">Record No.</th>
                                                                <th scope="col">Created At</th>
                                                                <th scope="col">Created By</th>
                                                                <th scope="col" style="width: 150px"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="medicalRecordsRow"></tbody>
                                                    </table>
                                                </div>
                                                <a href="/medical_records" target="_blank" id="showMedicalRecordsBtn"
                                                    class="btn btn-primary">See
                                                    Complete List</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-5">
                                <div class="row">
                                    <div class="col-12">
                                        <p class="fs-5"><i class="bi bi-prescription me-2 text-primary"></i>
                                            Prescription</p>
                                        <div class="mb-3" id="rxBody">

                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button id="clearPrescriptionBtn" type="button"
                                                class="btn btn-outline-secondary rounded-pill me-2"><i
                                                    class="bi bi-eraser me-2"></i> Clear</button>
                                            <button id="addMedsBtn" type="button"
                                                class="btn btn-primary rounded-pill"><i
                                                    class="bi bi-plus-lg me-2"></i> Add Medicine</button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <p class="fs-5"><i class="bi bi-sticky me-2 text-primary"></i> Notes</p>
                                        <textarea id="additionalNotes" class="form-control" rows="3" autocomplete="off"></textarea>
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

</html>
@include('modals.meds_selector_modal')
@include('toasts.live_toast')
@include('online_trx.js.online_trx_js', ['uuid' => $uuid])
@include('template.footer')
