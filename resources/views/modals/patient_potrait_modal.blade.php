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
                            <input class="form-check-input" type="checkbox" role="switch" id="startCameraSwitch">
                            <label class="form-check-label" for="startCameraSwitch">Start Camera</label>
                        </div>
                    </div>
                    <div class="col-sm-6 d-grid">
                        <video class="d-none" width="370" height="240" id="video" autoplay></video>
                        <button id="takePotraitBtn" class="btn btn-primary btn-sm mt-3 d-none">Take Potrait</button>
                    </div>
                    <div class="col-sm-6" style="text-align: center;">
                        <canvas id="canvas" width="240" height="302.5"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="patientPotraitCloseBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="patientPotraitSubmitBtn" data-id="{{ array_key_exists('patient', $data) ? $data['patient']->id : '' }}">Submit</button>
            </div>
        </div>
    </div>
</div>