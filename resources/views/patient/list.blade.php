<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Patient List'])

<body>
    @include('template.navbar', ['title' => 'Patient List'])
    <div class="container-fluid d-flex flex-row">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-3">
                        @if (in_array('PATIENT_REGISTER', $privs))
                            <div class="col-sm-12 col-md-4 col-lg-3 d-grid">
                                <a href="/patient/register" class="btn btn-success"><i class="bi bi-plus-circle me-2"></i>
                                    Register New Patient</a>
                            </div>
                        @endif
                        @if (in_array('PATIENT_REGISTER', $privs) && in_array('PATIENT_SEARCH', $privs))
                            <div class="col-sm-12 col-md-8 col-lg-9">
                                <form id="filterForm">
                                    <div class="input-group">
                                        <select class="form-select" name="filter_by" style="max-width: 175px;"
                                            autocomplete="off" id="filterPatientSelect">
                                            <option value="">Filter by...</option>
                                            <option selected value="name">Name</option>
                                            <option value="code">Patient Code</option>
                                        </select>
                                        <input id="filterPatientField" placeholder="Search patient..." type="text"
                                            class="form-control" name="filter_field">
                                        @if ($data['hasFilter'])
                                            <button type="button" id="clearFilterPatientBtn" class="btn btn-secondary">
                                                <i class="bi bi-eraser"></i>
                                            </button>
                                        @endif
                                        <button type="submit" form="filterForm" class="btn btn-primary"><i
                                                class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>
                        @elseif (!in_array('PATIENT_REGISTER', $privs) && in_array('PATIENT_SEARCH', $privs))
                            <div class="col-sm-12 col-md-12 col-lg-12">
                                <form id="filterForm">
                                    <div class="input-group">
                                        <select class="form-select" name="filter_by" style="max-width: 130px;"
                                            id="filterPatientSelect">
                                            <option value="">Filter by...</option>
                                            <option selected value="name">Name</option>
                                            <option value="code">Patient Code</option>
                                        </select>
                                        <input id="filterPatientField" placeholder="Search patient..." type="text"
                                            class="form-control" name="filter_field">
                                        @if ($data['hasFilter'])
                                            <button type="button" id="clearFilterPatientBtn" class="btn btn-secondary">
                                                <i class="bi bi-eraser"></i>
                                            </button>
                                        @endif
                                        <button type="submit" form="filterForm" class="btn btn-primary"><i
                                                class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="row mt-2 gy-1">
                        <div class="col-sm-12 mt-4">
                            {!! $data['rows']->appends(request()->input())->links() !!}
                        </div>
                        <div class="col-sm-12">
                            @foreach ($data['rows']->chunk(5) as $chunk)
                                <div class="card-group mt-3">
                                    @foreach ($chunk as $row)
                                        <div class="card" id="patientCard">
                                            @if ($row->last_potrait)
                                                <div class="patient-img"
                                                    style="background-image: url('{{ $row->last_potrait }}');"></div>
                                            @else
                                                <div class="patient-img"
                                                    style="background-image: url('{{ $image_placeholder }}');"></div>
                                            @endif
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center gap-2">
                                                    <div class="d-flex flex-column">
                                                        <a class="card-title stretched-link text-reset text-decoration-none mb-0 fs-5"
                                                            data-bs-toggle="modal" href="#patientListModal"
                                                            data-row="{{ $row }}">{{ $row->name }}</a>
                                                        <p class="fs-sm mb-0 text-muted">
                                                            {{ $row->code ? $row->code : '-' }}</p>
                                                    </div>

                                                    @if ($row->deleted_at)
                                                        <button data-id="{{ $row->id }}"
                                                            data-name="{{ $row->name }}"
                                                            onclick="window.restorePatient(event)"
                                                            class="btn btn-success btn-sm"
                                                            style="z-index: 1">Restore</button>
                                                    @else
                                                        <button data-id="{{ $row->id }}"
                                                            data-name="{{ $row->name }}"
                                                            onclick="window.removePatient(event)"
                                                            class="btn btn-danger btn-sm"
                                                            style="z-index: 1">Remove</button>
                                                    @endif
                                                </div>
                                            </div>
                                            <ol class="list-group list-group-flush">
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-start p-1">
                                                    <div class="ms-2 me-auto">
                                                        <div class="fw-bold">Address</div>
                                                        {{ $row->address ? $row->address : '-' }}
                                                    </div>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-start p-1">
                                                    <div class="ms-2 me-auto">
                                                        <div class="fw-bold">Joined at</div>
                                                        {{ $row->joined_at }}
                                                    </div>
                                                </li>
                                            </ol>
                                            <div class="card-footer">
                                                <p class="fs-6 text-muted mb-0">Last visited</p>
                                                <p class="fs-6 text-muted mb-0">
                                                    {{ $row->last_visited ? $row->last_visited : '-' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="col-sm-12 mt-4 mb-4">
                            {!! $data['rows']->appends(request()->input())->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('toasts.live_toast')
    @include('modals.patient_list_modal', ['privs' => $privs])
    @include('modals.patient_list_appointment_modal')
</body>

</html>
@include('template.footer')
