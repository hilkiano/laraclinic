// Elements
const liveToast = document.getElementById("liveToast");
const patientForm = document.getElementById("patientForm");
const birthDatePicker = document.getElementById("birth_date_picker");
const birthDateField = document.getElementById("birth_date");
const phoneNumberField = document.getElementById("phone_number");
const weightField = document.getElementById("weight");
const heightField = document.getElementById("height");
const startCameraSwitch = document.getElementById("startCameraSwitch");
const takePotraitBtn = document.getElementById("takePotraitBtn");
const _patientPotraitModal = document.getElementById("patientPotraitModal");
const _cropperPotraitModal = document.getElementById("cropperPotraitModal");
const cropperPotraitCloseBtn = document.getElementById(
    "cropperPotraitCloseBtn"
);
const cropperPotraitSubmitBtn = document.getElementById(
    "cropperPotraitSubmitBtn"
);
let patientPotraitModal;
if (_patientPotraitModal) {
    patientPotraitModal = new bootstrap.Modal(_patientPotraitModal, {});
}
let cropperPotraitModal;
if (_cropperPotraitModal) {
    cropperPotraitModal = new bootstrap.Modal(_cropperPotraitModal, {});
}
const btnGenerateCode = document.getElementById("btnGenerateCode");
const capturedImg = document.getElementById("capturedImg");
const video = document.getElementById("video");
const url = new URL(window.location.href);
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
let mediaStream;
let cropper;

/**
 * Handle form submit to backend
 * @param {*} evt
 * @returns
 */
const handleSubmit = async (evt) => {
    evt.preventDefault();
    resetFormClass();
    const submitBtn = document.getElementById("patientFormSubmitBtn");
    submitBtn.classList.add("disabled");
    submitBtn.insertAdjacentHTML(
        "afterbegin",
        '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
    );
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const requestBody = new FormData(patientForm);
    if (requestBody.get("birth_date") === "") {
        document.getElementById("birth_date").classList.add("is-invalid");
        submitBtn.classList.remove("disabled");
        document.getElementById("submitLoading").remove();
        return false;
    }
    const req = await fetch("/api/v1/patient/save-info", {
        headers: {
            Accept: "application/json, text-plain, */*",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
        },
        method: "post",
        credentials: "same-origin",
        body: requestBody,
    });
    const response = await req.json();
    if (response) {
        const toast = new bootstrap.Toast(liveToast);
        submitBtn.classList.remove("disabled");
        document.getElementById("submitLoading").remove();
        const errorToastHeader = document.getElementById("errorToastHeader");
        const successToastHeader =
            document.getElementById("successToastHeader");
        if (response.status) {
            errorToastHeader.classList.remove("d-block");
            errorToastHeader.classList.add("d-none");
            successToastHeader.classList.remove("d-none");
            successToastHeader.classList.add("d-block");
            let dataUrl = new URL("/patient/list", url.origin);
            dataUrl.searchParams.set("filter_by", "name");
            dataUrl.searchParams.set("filter_field", response.data.name);
            let bodyHtml = `<p class="mb-2">${response.message}</p><a class="btn btn-sm btn-light" href="${dataUrl.href}">View Patient</a>`;
            document.getElementById("toastBody").innerHTML = bodyHtml;
            toast.show();
        } else {
            errorToastHeader.classList.remove("d-none");
            errorToastHeader.classList.add("d-block");
            successToastHeader.classList.remove("d-block");
            successToastHeader.classList.add("d-none");
            if (typeof response.message === "string") {
                document.getElementById("toastBody").innerHTML =
                    response.message;
                toast.show();
            } else if (response.message instanceof Object) {
                handleError(response.message);
            }
        }
    }
};
/**
 * Handle validation errors from backend
 * @param {*} errors
 */
const handleError = (errors) => {
    if (typeof errors == "string") {
        const errorToastHeader = document.getElementById("errorToastHeader");
        const successToastHeader =
            document.getElementById("successToastHeader");
        const toast = new bootstrap.Toast(liveToast);
        errorToastHeader.classList.remove("d-none");
        errorToastHeader.classList.add("d-block");
        successToastHeader.classList.remove("d-block");
        successToastHeader.classList.add("d-none");
        document.getElementById("toastBody").innerHTML = errors;
        toast.show();
    } else {
        for (let error in errors) {
            document.getElementById(error).classList.add("is-invalid");
        }
    }
};
/**
 * Remove invalid class
 */
const resetFormClass = () => {
    document.getElementById("name").classList.remove("is-invalid");
    document.getElementById("birth_date").classList.remove("is-invalid");
};

const handleAddPotrait = async (evt) => {
    const patientId = evt.target.getAttribute("data-id");
    const croppedCanvas = cropper.getCroppedCanvas({
        maxWidth: 4096,
        maxHeight: 4096,
    });
    const blobImg = croppedCanvas.toDataURL();
    const param = {
        id: patientId,
        image: blobImg,
    };
    const formData = new FormData();
    for (let key in param) {
        formData.append(key, param[key]);
    }
    await fetch(`/api/v1/patient/add-potrait`, {
        headers: {
            Accept: "application/json, text-plain, */*",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
        },
        method: "post",
        credentials: "same-origin",
        body: formData,
    })
        .then((response) => {
            if (!response.ok) {
                return response
                    .json()
                    .catch(() => {
                        throw new Error(response.status);
                    })
                    .then(({ message }) => {
                        throw new Error(message || response.status);
                    });
            }

            return response.json();
        })
        .then((response) => {
            showResponse(true, response.message);
            cropperPotraitModal.hide();
            getPatientPotraits(patientId);
        })
        .catch((error) => {
            showResponse(false, error);
            return null;
        });
};

const getPatientPotraits = async (patientId) => {
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
                    .then(({ message }) => {
                        throw new Error(message || response.status);
                    });
            }

            return response.json();
        })
        .then((response) => {
            if (response.data.length > 0) {
                if (response.data.length > 0) {
                    let html = "";
                    response.data.map((d) => {
                        html += `<div class="position-relative" style="width: 150px;">`;
                        html += `<img id="patientPotrait" class="img-thumbnail me-2" src="${d}" alt="potrait placeholder" style="width: 150px;">`;
                        html += `<button class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="window.removeImg('${d}')">Delete</button>`;
                        html += `</div>`;
                    });
                    $("#potraits").html(html);
                } else {
                    $("#potraits").html(
                        '<p class="text-muted">No potrait saved.</p>'
                    );
                }
            } else {
                $("#potraits").html(
                    '<p class="text-muted">No potrait saved.</p>'
                );
            }
        })
        .catch((error) => {
            showResponse(false, error);
            return null;
        });
};

const handleGenerateCode = async () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const req = await fetch("/api/v1/patient/get-code", {
        headers: {
            Accept: "application/json, text-plain, */*",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
        },
        method: "get",
        credentials: "same-origin",
    });
    const toast = new bootstrap.Toast(liveToast);
    const errorToastHeader = document.getElementById("errorToastHeader");
    const successToastHeader = document.getElementById("successToastHeader");
    try {
        const response = await req.json();
        if (response) {
            if (response.status) {
                $("#code").val(response.data);

                errorToastHeader.classList.remove("d-block");
                errorToastHeader.classList.add("d-none");
                successToastHeader.classList.remove("d-none");
                successToastHeader.classList.add("d-block");
                document.getElementById("toastBody").innerHTML =
                    "Code generated";
                toast.show();
            } else {
                errorToastHeader.classList.remove("d-none");
                errorToastHeader.classList.add("d-block");
                successToastHeader.classList.remove("d-block");
                successToastHeader.classList.add("d-none");
                document.getElementById("toastBody").innerHTML =
                    "Failed generating code.";
                toast.show();
            }
        }
    } catch (error) {
        errorToastHeader.classList.remove("d-none");
        errorToastHeader.classList.add("d-block");
        successToastHeader.classList.remove("d-block");
        successToastHeader.classList.add("d-none");
        document.getElementById("toastBody").innerHTML =
            "Internal server error.";
        toast.show();
    }
};

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

const handleRetakePotrait = (e) => {
    capturedImg.src = "";
    cropperPotraitModal.hide();
    patientPotraitModal.show();

    startCameraSwitch.checked = true;
    startCameraSwitch.dispatchEvent(new Event("change"));
};

// Elements events
if (btnGenerateCode) {
    btnGenerateCode.addEventListener("click", handleGenerateCode);
}
if (patientForm) {
    patientForm.addEventListener("submit", handleSubmit);
}
if (birthDateField) {
    birthDateField.addEventListener("change", function (evt) {
        birthDateField.classList.remove("is-invalid");
    });
}
if (_patientPotraitModal) {
    _patientPotraitModal.addEventListener("hide.bs.modal", function (evt) {
        startCameraSwitch.checked = false;
        startCameraSwitch.dispatchEvent(new Event("change"));
    });
}
if (_cropperPotraitModal) {
    _cropperPotraitModal.addEventListener("hide.bs.modal", function (e) {
        // do your thing
    });
}
if (startCameraSwitch) {
    startCameraSwitch.addEventListener("change", async function (evt) {
        if (startCameraSwitch.checked) {
            video.classList.remove("d-none");
            takePotraitBtn.classList.remove("disabled");
            let stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: true,
            });
            video.srcObject = stream;
            mediaStream = stream.getTracks()[0];
        } else {
            if (mediaStream) {
                mediaStream.stop();
            }
            video.classList.add("d-none");
            takePotraitBtn.classList.add("disabled");
        }
    });
}
if (takePotraitBtn) {
    takePotraitBtn.addEventListener("click", function () {
        // Create canvas element
        const canvas = document.createElement("canvas");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw the current frame of the video onto the canvas
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Get the data URL representing the image data
        const dataUrl = canvas.toDataURL();

        // Set the src attribute of the img element to the data URL
        capturedImg.src = dataUrl;
        if (cropper) {
            cropper.replace(dataUrl);
        } else {
            // Initialize cropper js
            const imgInstance = $("#capturedImg");
            imgInstance.cropper({
                viewMode: 2,
                minContainerHeight: 480,
                minContainerWidth: 640,
                aspectRatio: 2 / 3,
            });

            cropper = imgInstance.data("cropper");
        }

        // Close camera modal and open cropper modal
        patientPotraitModal.hide();
        cropperPotraitModal.show();
    });
}
if (cropperPotraitCloseBtn) {
    cropperPotraitCloseBtn.addEventListener("click", handleRetakePotrait);
}
if (cropperPotraitSubmitBtn) {
    cropperPotraitSubmitBtn.addEventListener("click", handleAddPotrait);
}
