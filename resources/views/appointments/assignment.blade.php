<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'My Assignment'])

<body>
    @include('template.navbar', ['title' => 'My Assignment'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4 px-0">
                    <div class="row gy-2">
                        <div class="col-12 px-4">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <p class="fs-4 mb-0">My Assignment</p>
                                    <p class="text-muted fs-5">{{ $today }}</p>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <form id="filterForm">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="filterName"
                                                placeholder="Search by patient name">
                                            <button class="btn btn-outline-secondary" type="submit" form="filterForm"
                                                id="filterBtn"><i class="bi bi-search"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                        <div class="container-fluid p-0">
                            <div id="loadingList" class="d-flex flex-row flex-nowrap overflow-scroll pb-4">
                                @foreach ([0, 1, 2, 3, 4, 5, 6, 7, 8, 9] as $idx => $card)
                                    <div class="card ms-4 {{ $idx === 9 ? 'me-4' : 'ms-4' }} flex-shrink-0 border-0"
                                        style="width: 360px">
                                        <div class="card-body p-0">
                                            <span class="placeholder-glow">
                                                <span class="placeholder border-0 bg-dark-subtle col-12 rounded"
                                                    style="height: 247.733px"></span>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div id="myList" class="d-none flex-row flex-nowrap overflow-scroll pb-4">

                            </div>
                        </div>
                        <div id="loadingIndicator" class="mt-4 d-none">
                            <div class="text-center">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"
                                    role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div id="selectedAssignment" class="p-0 m-0 mb-5 d-none">
                            <div id="assignmentAction" style="z-index: 1"
                                class="col-12 px-4 py-4 d-flex justify-content-end sticky-top">
                                <div class="row w-100 gx-0">
                                    <div class="col d-flex">
                                        <button id="markAsCancelBtn" type="button"
                                            class="btn btn-light rounded-pill text-danger-emphasis"><i
                                                class="bi bi-x-lg me-2"></i>Mark As Cancelled</button>
                                    </div>
                                    <div class="col d-flex justify-content-end align-items-center gap-2">
                                        <button id="cancelBtn" type="button" class="btn btn-danger rounded-pill"><i
                                                class="bi bi-x-lg me-2"></i>Cancel</button>
                                        <button id="submitBtn" type="button"
                                            class="btn btn-success rounded-pill disabled"><i
                                                class="bi bi-check-lg me-2"></i>Submit</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mx-2 mt-1 gy-4">
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
                                                @if ($group === 3)
                                                    <div class="col-12">
                                                        <p class="mb-1 fw-bold">Medical Records</p>
                                                        <div class="table-responsive">
                                                            <table
                                                                class="table table-bordered table-striped table-hover caption-top"
                                                                style="min-width: 500px;">
                                                                <thead class="table-primary">
                                                                    <tr>
                                                                        <th scope="col">#</th>
                                                                        <th scope="col">Record No.</th>
                                                                        <th scope="col">Created At</th>
                                                                        <th scope="col" style="width: 150px"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="medicalRecordsRow"></tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                @elseif ($group === 4)
                                                    <div class="col-12">
                                                        <p class="mb-1 fw-bold">Prescriptions</p>
                                                        <div class="table-responsive">
                                                            <table
                                                                class="table table-bordered table-striped table-hover caption-top"
                                                                style="min-width: 500px;">
                                                                <thead class="table-primary">
                                                                    <tr>
                                                                        <th scope="col">#</th>
                                                                        <th scope="col">Created At</th>
                                                                        <th scope="col" style="width: 150px"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="prescriptionsRow"></tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                @endif
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
                                                        class="bi bi-plus-lg me-2"></i> Add Medicine/Service</button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <p class="fs-5"><i class="bi bi-sticky me-2 text-primary"></i> Notes</p>
                                            @if ($group === 3)
                                                <textarea id="medicalNotes" class="form-control" rows="3" autocomplete="off"></textarea>
                                            @else
                                                <textarea id="medicalNotes" class="form-control" rows="3" autocomplete="off" readonly></textarea>
                                            @endif
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
@include('modals.meds_selector_modal')
@include('appointments.js.assignment_js', ['group' => $group])
@include('toasts.live_toast')
@include('template.footer')
