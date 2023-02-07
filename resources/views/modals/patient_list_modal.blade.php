<div id="patientListModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patientListModalHead"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="patientListModalBody">
                <div class="row d-flex align-items-start align-content-start">
                    <div class="col-sm-4 d-grid">
                        <img id="patientPotrait" class="img-thumbnail mb-2" src="{{ asset('images/potrait-placeholder.png') }}" alt="potrait placeholder">
                        @if (in_array("PATIENT_ASSIGNMENT", $privs))
                        <a class="btn mt-2 btn-sm btn-pharmacy" id="assignPharmacyBtn"><i class="bi bi-capsule me-2"></i>Go To Pharmacy</a>
                        <a class="btn mt-2 btn-sm btn-doctor" id="assignDoctorBtn"><i class="bi bi-heart-pulse-fill me-2"></i>Make Doctor Appointment</a>
                        @endif
                        <a href="" id="patientUpdateBtn" class="btn mt-2 btn-sm btn-secondary"><i class="bi bi-pencil-fill me-2"></i>Update Information</a>
                    </div>
                    <div class="col-sm-8">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <p class=" text-muted fs-5 mb-0">Address</p>
                                <p class="mb-0" id="address"></p>
                            </li>
                            <li class="list-group-item">
                                <p class="text-muted fs-5 mb-0">Birth date</p>
                                <p class="mb-0" id="birth_date"></p>
                            </li>
                            <li class="list-group-item">
                                <p class="text-muted fs-5 mb-0">Age</p>
                                <p class="mb-0" id="age"></p>
                            </li>
                            <li class="list-group-item">
                                <p class="text-muted fs-5 mb-0">Weight</p>
                                <p class="mb-0" id="weight"></p>
                            </li>
                            <li class="list-group-item">
                                <p class="text-muted fs-5 mb-0">Height</p>
                                <p class="mb-0" id="height"></p>
                            </li>
                            <li class="list-group-item">
                                <p class="text-muted fs-5 mb-0">Phone number</p>
                                <p class="mb-0" id="phone_number"></p>
                            </li>
                            <li class="list-group-item">
                                <p class="text-muted fs-5 mb-0">Additional Details</p>
                                <p class="mb-0" id="additional_note"></p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>