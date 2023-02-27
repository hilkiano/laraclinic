<p class="fs-4"><i class="bi bi-calendar-range me-1"></i> My Schedule</p>
<div class="row gy-3">
    <div class="col-sm-12 col-md-6">
        <div class="row">
            <div class="col-sm-12 col-md-12 mb-2">
                <label for="day-select" class="form-label">Choose Day</label>
                <select id="day-select" autocomplete="off">
                    <option value="sunday">Sunday</option>
                    <option value="monday">Monday</option>
                    <option value="tuesday">Tuesday</option>
                    <option value="wednesday">Wednesday</option>
                    <option value="thursday">Thursday</option>
                    <option value="friday">Friday</option>
                    <option value="saturday">Saturday</option>
                </select>
            </div>
            <div class="col-sm-12 col-md-6">
                <label for="start-time" class="form-label">Start Time</label>
                <div class="input-group" id="start-time-group" data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                    <input autocomplete="off" id="start-time" type="text" class="form-control" data-td-target="#start-time" placeholder="Choose start time..." required readonly>
                    <span class="input-group-text" data-td-target="#start-time" data-td-toggle="datetimepicker" required>
                        <i class="bi bi-clock"></i>
                    </span>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <label for="end-time" class="form-label">End Time</label>
                <div class="input-group" id="end-time-group" data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                    <input autocomplete="off" id="end-time" type="text" class="form-control" data-td-target="#end-time" placeholder="Choose end time..." required readonly>
                    <span class="input-group-text" data-td-target="#end-time" data-td-toggle="datetimepicker" required>
                        <i class="bi bi-clock"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <i class="bi bi-calendar-range me-1"></i>
                My Schedule
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Sunday: <span id="sunday"></span></p>
                </li>
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Monday: <span id="monday"></span></p>
                </li>
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Tuesday: <span id="tuesday"></span></p>
                </li>
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Wednesday: <span id="wednesday"></span></p>
                </li>
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Thursday: <span id="thursday"></span></p>
                </li>
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Friday: <span id="friday"></span></p>
                </li>
                <li class="list-group-item">
                    <p class="d-flex gap-2 mb-0">Saturday: <span id="saturday"></span></p>
                </li>
            </ul>
            <div class="card-footer d-flex justify-content-end p-2">
                <button class="btn btn-sm btn-danger" id='clear-schedule-btn'><i class="bi bi-x-lg me-1"></i>Clear Schedule</button>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-sm-12">
        <button class="btn btn-sm btn-success" id="add-schedule-btn" disabled><i class="bi bi-plus-lg me-1"></i>Add Schedule</button>
    </div>
</div>
<hr class="my-4" />

@include('admin.js.user-configs-doctor_js', ['user' => $user])