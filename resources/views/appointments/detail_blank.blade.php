<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Detail Assignment'])

<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div id="mainContent" class="d-flex flex-column w-100">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-12">
                            <div class="row gy-4">
                                <div class="col-sm-12 col-md-12 col-lg-5">
                                    <div id="mainCard" class="card w-100 shadow border border-opacity-50">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item p-3">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        @if ($data['patient']['patientPotrait'])
                                                        <img src="{{ Arr::last($data['patient']['patientPotrait']->url) }}" class="img-thumbnail w-50 text-center" alt="...">
                                                        @else
                                                        <img src="{{ asset('images/potrait-placeholder.png') }}" class="img-thumbnail w-25 text-center" alt="...">
                                                        @endif
                                                    </div>
                                                    <div class="col-6 d-flex justify-content-end align-self-start">
                                                        <h4 id="loadingBadge">
                                                            <div class="badge rounded-pill text-bg-secondary d-flex gap-2">
                                                                <div class="spinner-border spinner-border-sm" role="status">
                                                                </div>
                                                                Loading
                                                            </div>
                                                        </h4>
                                                        <h4 id="currentBadge" class="d-none">
                                                            <div class="badge rounded-pill text-bg-primary d-flex gap-2">
                                                                <span id="currentBadgeText"></span>
                                                            </div>
                                                        </h4>
                                                    </div>
                                                </div>
                                                <p class="text-muted mb-0">Patient</p>
                                                <p class="fs-3 mb-1">{{ $data['patient']->name }}</p>
                                                <p class="text-muted mb-0">Visit Time</p>
                                                <p class="fs-5 mb-1">{{ $data->visit_time }}</p>
                                                <p class="text-muted mb-0">Visit Reason</p>
                                                <p class="fs-5 mb-1">{{ $data->visit_reason === 'DOCTOR' ? 'Doctor Consultation' : 'Pharmacy' }}</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-12 col md-12 col-lg-7">
                                    <p class="fs-2">Status Log</p>
                                    <hr>
                                    <div id="loadingCards">
                                        <div class="card border-0">
                                            <div class="card-body p-0">
                                                <span class="placeholder-glow">
                                                    <span class="placeholder border-0 bg-dark-subtle col-12 rounded" style="height: 200px"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="statusCards" class="d-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<div id="appointmentsDetail" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentsDetailHead">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appointmentsDetailBody">
                <form id="detailForm">
                    <input type="hidden" id="uuid" name="uuid" value="{{ $data->uuid }}" />
                    <input type="hidden" id="status" name="status" />
                    <div class="row gy-2">
                        <div class="col-12">
                            <label for="additionalNote" class="form-label">Add Notes</label>
                            <textarea autocomplete="off" class="form-control" name="additional_note" id="additionalNote" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="appointmentsDetailCloseBtn">Cancel</button>
                <button type="submit" form="detailForm" class="btn btn-primary" id="appointmentsDetailSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

</html>
@include('appointments.js.detail_js')
@include('template.footer')