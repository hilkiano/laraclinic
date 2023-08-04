<div id="patientPotraitModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patientPotraitModalHead">Upload Potrait</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="patientPotraitModalBody">
                <div class="row gy-3">
                    <div class="col-12 d-grid">
                        <div class="form-check form-switch">
                            <input autocomplete="off" class="form-check-input" type="checkbox" role="switch"
                                id="startCameraSwitch">
                            <label class="form-check-label" for="startCameraSwitch">Start Camera</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-center">
                        <video class="d-none" id="video" autoplay></video>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                    id="patientPotraitCloseBtn">Cancel</button>
                <button id="takePotraitBtn" class="btn btn-primary disabled">(Enter) Take Potrait</button>
            </div>
        </div>
    </div>
</div>

<div id="cropperPotraitModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropperPotraitHead">Upload Potrait</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cropperPotraitBody">
                <div class="row gy-3">
                    <div class="col-12 d-flex justify-content-center">
                        <img id="capturedImg" src="" alt="Captured image">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="cropperPotraitCloseBtn">Retake
                    Photo</button>
                <button type="button" class="btn btn-primary" id="cropperPotraitSubmitBtn"
                    data-id="{{ array_key_exists('patient', $data) ? $data['patient']->id : '' }}">Crop and
                    Submit</button>
            </div>
        </div>
    </div>
</div>
