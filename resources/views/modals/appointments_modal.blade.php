<div id="appointmentsModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentsModalHead">Add Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appointmentsModalBody">
                <form id="appointmentForm">
                    <div class="row gy-2">
                        <div class="col-sm-12 col-md-6">
                            <label for="patient_id" class="form-label">Patient</label>
                            <select placeholder="Select patient..." id="patient_id" name="patient_id"></select>
                            <div class="invalid-feedback">Select patient.</div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label for="visit_time" class="form-label">Choose Datetime</label>
                            <div class="input-group" id="visit_time_group" data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                                <input id="visit_time" name="visit_time" type="text" class="form-control" data-td-target="#visit_time" placeholder="Click here to select date..." required readonly>
                                <span class="input-group-text" data-td-target="#visit_time" data-td-toggle="datetimepicker" required>
                                    <i class="bi bi-calendar"></i>
                                </span>
                                <div class="invalid-feedback">Select date.</div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <label for="reason" class="form-label">Assignment</label>
                            <select placeholder="Choose assignment type..." id="reason" name="reason">
                                <option value="doctor">Doctor Assignment</option>
                                <option value="pharmacy">Pharmacy Assignment</option>
                            </select>
                        </div>
                        <div class="col-sm-12">
                            <label for="additional_note" class="form-label">Notes</label>
                            <textarea class="form-control" id="additional_note" name="additional_note" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="appointmentsModalCloseBtn">Cancel</button>
                <button type="submit" form="groupsForm" class="btn btn-primary" id="appointmentsModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>