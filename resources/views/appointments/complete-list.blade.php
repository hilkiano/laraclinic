<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Appointment'])

<body>
    @include('template.navbar', ['title' => 'Appointment'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-12">
                            <a href="list" class="btn btn-sm btn-outline-primary"><i class="me-2 bi bi-arrow-bar-left"></i>Go Back</a>
                        </div>
                        <div class="col-12">
                            <div class="accordion" id="settingsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button show" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTableSettings">
                                            Table Settings or Filters
                                        </button>
                                    </h2>
                                    <div id="collapseTableSettings" class="accordion-collapse collapse show" data-bs-parent="#settingsAccordion">
                                        <div class="accordion-body">
                                            <form id="tableForm">
                                                <div class="row gy-4">
                                                    <div class="col-sm-12 col-md-4">
                                                        <label for="patientName" class="form-label">Patient Name</label>
                                                        <input autocomplete="off" type="text" class="form-control" id="patientName" placeholder="Filter by patient name...">
                                                    </div>
                                                    <div class="col-sm-12 col-md-4">
                                                        <label for="reasonFilter" class="form-label">Filter by reason</label>
                                                        <select id="reasonFilter" class="form-select" style="width: 100%">
                                                            <option value="all">ALL</option>
                                                            <option value="PHARMACY">Pharmacy</option>
                                                            <option value="DOCTOR">Doctor</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 col-md-4">
                                                        <label for="statusFilter" class="form-label">Filter by status</label>
                                                        <select id="statusFilter" class="form-select" style="width: 100%">
                                                            <option value="all">ALL</option>
                                                            <option value="DOC_WAITING">Doctor: Waiting</option>
                                                            <option value="DOC_ASSIGNED">Doctor: Assigned</option>
                                                            <option value="PHAR_WAITING">Pharmacy: Waiting</option>
                                                            <option value="PHAR_ASSIGNED">Pharmacy: Assigned</option>
                                                            <option value="IN_PAYMENT">In Payment</option>
                                                            <option value="COMPLETED">Completed</option>
                                                            <option value="CANCELED">Canceled</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 col-md-6">
                                                        <div class="row gy-2">
                                                            <div class="col-sm-12 col-md-6">
                                                                <label for="fromDate" class="form-label">From</label>
                                                                <div class="input-group" id="fromDate_group" data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                                                                    <input autocomplete="off" id="fromDate" name="fromDate" type="text" class="form-control" data-td-target="#fromDate" placeholder="Click here to select date..." required readonly>
                                                                    <span class="input-group-text" data-td-target="#fromDate" data-td-toggle="datetimepicker" required>
                                                                        <i class="bi bi-calendar"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12 col-md-6">
                                                                <label for="toDate" class="form-label">To</label>
                                                                <div class="input-group" id="toDate_group" data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                                                                    <input autocomplete="off" id="toDate" name="toDate" type="text" class="form-control" data-td-target="#toDate" placeholder="Click here to select date..." required readonly>
                                                                    <span class="input-group-text" data-td-target="#toDate" data-td-toggle="datetimepicker" required>
                                                                        <i class="bi bi-calendar"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button id="resetFilterBtn" class="btn btn-light" type="button"><i class="me-2 bi bi-eraser"></i>Reset Filter</button>
                                                        <button id="applyFilterBtn" class="btn btn-primary ms-2" type="submit"><i class="me-2 bi bi-check-lg"></i>Apply Filter</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover caption-top" style="min-width: 1100px;">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col" style="width: 5px;">#</th>
                                            <th scope="col" style="width: 330px;">Patient</th>
                                            <th scope="col" style="width: 150px;">Visit Time</th>
                                            <th scope="col" style="width: 80px;">Visit Reason</th>
                                            <th scope="col" style="width: 80px;">Status</th>
                                            <th scope="col" style="width: 40px;">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody id="appointmentRows">
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 mb-5 d-flex row">
                                <div class="col">
                                    <p class="small text-muted">Total <span class="fw-semibold" id="allCount">0</span></p>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <div id="pagination"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('toasts.live_toast')
@include('appointments.js.complete-list_js')
@include('template.footer')