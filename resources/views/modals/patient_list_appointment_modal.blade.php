<div id="patientListAppointmentModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-light bg-gradient">
            <div class="modal-header">
                <h5 class="modal-title" id="patientListAppointmentModalHead">Add Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="patientListAppointmentModalBody">
                <form id="patientAppointmentForm">
                    <div class="row">
                        <input type="hidden" autocomplete="off" name="patient_id">
                        <input type="hidden" autocomplete="off" name="reason">
                        <div class="col-12">
                            <p class="mb-0">Patient</p>
                            <p class="fw-bold" id="patientAppointmentName"></p>
                        </div>
                        <div class="col-12">
                            <p class="mb-0">Appointment</p>
                            <p class="fw-bold" id="patientAppointmentType"></p>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea autocomplete="off" placeholder="Add some notes..." class="form-control" id="patientAppointmentNotes" name="additional_note" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="patientListAppointmentModalCloseBtn">Cancel</button>
                <button type="submit" form="patientAppointmentForm" class="btn btn-primary" id="patientListAppointmentModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>