<!DOCTYPE html>
<html>
@include('template.header', ['title' => $data['title']])

<body>
    @include('template.navbar', ['title' => $data['title']])
    <div class="container-fluid d-flex flex-row">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-sm-12 col-md-4 col-lg-3 d-grid d-md-flex">
                            <a href="/patient/list" class="btn btn-secondary"><i
                                    class="bi bi-chevron-double-left me-2"></i> Back to Patient List</a>
                        </div>
                        <div class="col-sm-12 col-md-8 col-lg-9 d-grid d-md-flex justify-content-md-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" href="#patientPotraitModal"><i
                                    class="bi bi-camera-fill me-2"></i>Upload Potrait</button>
                        </div>
                        <hr />
                        <div class="col-sm-12 col-md-12 col-lg-6 d-grid">
                            <form id="patientForm">
                                <input type="hidden" name="id"
                                    value="{{ array_key_exists('patient', $data) ? $data['patient']->id : '' }}"
                                    id="patient_id" />
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" placeholder="Patient name"
                                        name="name"
                                        value="{{ array_key_exists('patient', $data) ? $data['patient']->name : '' }}"
                                        required>
                                    <div class="invalid-feedback">Please fill this field.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="phone_number" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+62</span>
                                                <input type="text" class="form-control" id="phone_number"
                                                    placeholder="Phone number" name="phone_number" maxlength="15">
                                                <div class="invalid-feedback">Phone number not valid.</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="birth_date" class="form-label">Birth Date</label>
                                            <div class="input-group" id="birth_date_picker"
                                                data-td-target-input="nearest" data-td-target-toggle="nearest" required>
                                                <input id="birth_date" name="birth_date" type="text"
                                                    class="form-control" data-td-target="#birth_date_picker"
                                                    placeholder="Click here to select date..." required readonly>
                                                <span class="input-group-text" data-td-target="#birth_date_picker"
                                                    data-td-toggle="datetimepicker" required>
                                                    <i class="bi bi-calendar"></i>
                                                </span>
                                                <div class="invalid-feedback">Please fill this field.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Patient address">{{ array_key_exists('patient', $data) ? $data['patient']->address : '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="weight" class="form-label">Weight</label>
                                            <div class="input-group">
                                                <input type="text" name="weight" class="form-control"
                                                    id="weight">
                                                <span class="input-group-text">kg</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="height" class="form-label">Height</label>
                                            <div class="input-group">
                                                <input type="text" name="height" class="form-control"
                                                    id="height">
                                                <span class="input-group-text">cm</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="additional_note" class="form-label">Additional Note</label>
                                    <textarea class="form-control" id="additional_note" name="additional_note"
                                        placeholder="Type any detail about patient here..." rows="3">{{ array_key_exists('patient', $data) ? $data['patient']->additional_note : '' }}</textarea>
                                </div>
                                <button type="submit" form="patientForm" id="patientFormSubmitBtn"
                                    class="btn btn-lg btn-primary mt-2">Submit</button>
                            </form>
                        </div>
                        @if (array_key_exists('patient', $data))
                            <div class="col-sm-12 col-md-12 col-lg-6 d-grid mb-5">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <p class="fs-3">Patient Potraits</p>
                                                <div id="potraits">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('toasts.live_toast')
        @include('modals.patient_potrait_modal')
</body>

</html>
@include('template.footer')

<script type="module">
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let phoneNumberMask;
    let weightMask;
    let heightMask;
    let birthDatePicker;

    const getPatientPotraits = async () => {
        const patientId = parseInt("{{ array_key_exists('patient', $data) ? $data['patient']->id : 0 }}");
        await fetch(`/api/v1/patient/get-potraits/${patientId}`, {
                headers: {
                    Accept: "application/json, text-plain, */*",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                method: "get",
                credentials: "same-origin",
            })
            .then((response) => {
                if (!response.ok) {
                    return response
                        .json()
                        .catch(() => {
                            throw new Error(response.status);
                        })
                        .then(({
                            message
                        }) => {
                            throw new Error(message || response.status);
                        });
                }

                return response.json();
            })
            .then((response) => {
                if (response.data.length > 0) {
                    let html = '';
                    response.data.map(d => {
                        html += `<img id="patientPotrait" class="img-thumbnail me-2" src="${d}" alt="potrait placeholder" style="width: 150px;">`;
                    })
                    $("#potraits").html(html);
                } else {
                    $("#potraits").html('<p class="text-muted">No potrait saved.</p>')
                }
            })
            .catch((error) => {
                showResponse(false, error);
                return null;
            });
    }

    const showResponse = (status, message) => {
        const toast = new bootstrap.Toast(liveToast);
        const errorToastClasses =
            document.getElementById("errorToastHeader").classList;
        const successToastClasses =
            document.getElementById("successToastHeader").classList;
        if (status) {
            errorToastClasses.add("d-none");
            errorToastClasses.remove("d-block");
            successToastClasses.add("d-block");
            successToastClasses.remove("d-none");
        } else {
            errorToastClasses.add("d-block");
            errorToastClasses.remove("d-none");
            successToastClasses.add("d-none");
            successToastClasses.remove("d-block");
        }
        document.getElementById("toastBody").innerHTML = message;
        toast.show();
    };

    window.getPatientPotraits = getPatientPotraits;

    $(document).ready(function() {
        const path = "{{ request()->path() }}";
        const segments = path.split('/');
        const formType = segments[1];

        if (formType === "update") {
            getPatientPotraits();
        }

        phoneNumberMask = IMask(document.getElementById("phone_number"), {
            mask: Number,
            signed: false,
        });
        weightMask = IMask(document.getElementById("weight"), {
            mask: Number,
            signed: false,
        });
        heightMask = IMask(document.getElementById("height"), {
            mask: Number,
            signed: false,
        });
        birthDatePicker = new TempusDominus(document.getElementById("birth_date_picker"), tDConfigs);

        phoneNumberMask.value = "{{ array_key_exists('patient', $data) ? $data['patient']->phone_number : '' }}";
        weightMask.value = "{{ array_key_exists('patient', $data) ? $data['patient']->weight : '' }}";
        heightMask.value = "{{ array_key_exists('patient', $data) ? $data['patient']->height : '' }}";

        let birthDateValue = "{{ array_key_exists('patient', $data) ? $data['patient']->birth_date : null }}";
        if (birthDateValue) {
            let momentVal = moment(birthDateValue).toDate();
            birthDatePicker.dates.setFromInput(momentVal);
        }
    });
</script>
