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
                    <div class="row">
                        <div class="col-sm-12">
                            <p class="fs-4 mb-0">Today Appointment</p>
                            <p class="text-muted fs-5">{{ $data['today'] }}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="input-group">
                                        @if (in_array("PATIENT_ASSIGNMENT", $privs))
                                        <button data-bs-toggle="modal" href="#appointmentsModal" class="btn btn-success">
                                            <i class="bi bi-plus"></i>
                                            Create Appointment
                                        </button>
                                        @endif
                                        <input id="filterField" placeholder="Filter by patient name..." type="text" class="form-control">
                                        @if ($data["hasFilter"])
                                        <button id="clearFilterBtn" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                        @endif
                                        <button id="filterBtn" class="btn btn-primary">
                                            <i class="bi bi-funnel"></i>
                                        </button>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-sm-12 col-md-6">
                                            <label for="reasonFilter" class="form-label">Filter by reason</label>
                                            <select id="reasonFilter" class="form-select" style="width: 100%">
                                                <option value="all">ALL</option>
                                                <option value="pharmacy">Pharmacy</option>
                                                <option value="doctor">Doctor</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <label for="statusFilter" class="form-label">Filter by status</label>
                                            <select id="statusFilter" class="form-select" style="width: 100%">
                                                <option value="all">ALL</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="handled">Handled</option>
                                                <option value="completed">Completed</option>
                                                <option value="canceled">Canceled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-sm-12 d-flex justify-content-end">
                                            <button id="resetFilterBtn" class="btn btn-light"><i class="me-2 bi bi-eraser"></i>Reset Filter</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
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
                                    <tbody>
                                        @if (count($data['rows']) > 0)
                                        @php
                                        $i = 1;
                                        @endphp
                                        @foreach ($data['rows'] as $row)
                                        <tr>
                                            <td scope="row">{{ $i }}</td>
                                            <td>{{ $row->patient->name }}</td>
                                            <td>{{ $row->visit_time }}</td>
                                            <td>
                                                @if ($row->visit_reason === "pharmacy")
                                                <p class="fs-5 mb-0"><span class="badge bg-secondary">Pharmacy</span></p>
                                                @elseif ($row->visit_reason === "doctor")
                                                <p class="fs-5 mb-0"><span class="badge bg-secondary">Doctor</span></p>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->status === "waiting")
                                                <p class="fs-5 mb-0"><span class="badge bg-info">Waiting</span></p>
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="/appointments/detail/{{ $row->uuid }}" class="btn btn-sm btn-primary">See Details</a>
                                            </td>
                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="6">No Data.</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            {!! $data['rows']->appends(request()->input())->links() !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="complete-list" class="btn btn-primary"><i class="bi bi-clock-history me-2"></i>See Complete List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
@include('modals.appointments_modal', ['data' => $data]);
@include('toasts.live_toast')
@include('appointments.js.list_js', ['data' => $data])
@include('template.footer')