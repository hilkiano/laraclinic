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
const patientPotraitModal = document.getElementById("patientPotraitModal");
const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const patientPotraitSubmitBtn = document.getElementById(
    "patientPotraitSubmitBtn"
);
const url = new URL(window.location.href);
let mediaStream;

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
        if (response.status) {
            document.getElementById("errorToastHeader").classList.add("d-none");
            document
                .getElementById("successToastHeader")
                .classList.add("d-block");
            let dataUrl = new URL("/patient/list", url.origin);
            dataUrl.searchParams.set("filter_by", "name");
            dataUrl.searchParams.set("filter_field", response.data.name);
            let bodyHtml = `<p class="mb-2">${response.message}</p><a class="btn btn-sm btn-light" href="${dataUrl.href}">View Patient</a>`;
            document.getElementById("toastBody").innerHTML = bodyHtml;
            toast.show();
        } else {
            document
                .getElementById("errorToastHeader")
                .classList.add("d-block");
            document
                .getElementById("successToastHeader")
                .classList.add("d-none");
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
        const toast = new bootstrap.Toast(liveToast);
        document.getElementById("errorToastHeader").classList.add("d-block");
        document.getElementById("successToastHeader").classList.add("d-none");
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
    if (isCanvasBlank(canvas)) {
        handleError("You haven't take a potrait.");
        return false;
    }
    const dataImg = canvas.toDataURL("image/jpeg");
    const submitBtn = document.getElementById("patientPotraitSubmitBtn");
    submitBtn.classList.add("disabled");
    submitBtn.insertAdjacentHTML(
        "afterbegin",
        '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
    );
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const param = {
        id: patientId,
        img: dataImg,
    };
    const formData = new FormData();
    for (var key in param) {
        formData.append(key, param[key]);
    }
    const req = await fetch("/api/v1/patient/add-potrait", {
        headers: {
            Accept: "application/json, text-plain, */*",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
        },
        method: "post",
        credentials: "same-origin",
        body: formData,
    });
    const response = await req.json();
    if (response) {
        submitBtn.classList.remove("disabled");
        document.getElementById("submitLoading").remove();
        if (response.status) {
            document.getElementById("patientPotraitCloseBtn").click();
            showResponse(response.status, response.message);
        } else {
            showResponse(response.status, response.message);
        }
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
const isCanvasBlank = (canvas) => {
    return !canvas
        .getContext("2d")
        .getImageData(0, 0, canvas.width, canvas.height)
        .data.some((channel) => channel !== 0);
};

// Elements events
if (patientForm) {
    patientForm.addEventListener("submit", handleSubmit);
}
if (birthDateField) {
    birthDateField.addEventListener("change", function (evt) {
        birthDateField.classList.remove("is-invalid");
    });
}
if (patientPotraitModal) {
    patientPotraitModal.addEventListener("hide.bs.modal", function (evt) {
        const context = canvas.getContext("2d");
        context.clearRect(0, 0, canvas.width, canvas.height);
        startCameraSwitch.checked = false;
        startCameraSwitch.dispatchEvent(new Event("change"));
    });
}
if (startCameraSwitch) {
    startCameraSwitch.addEventListener("change", async function (evt) {
        if (startCameraSwitch.checked) {
            video.classList.remove("d-none");
            takePotraitBtn.classList.remove("d-none");
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
            takePotraitBtn.classList.add("d-none");
        }
    });
}
if (takePotraitBtn) {
    takePotraitBtn.addEventListener("click", function () {
        canvas
            .getContext("2d")
            .drawImage(video, 200, 20, 240, 302.5, 0, 0, 240, 302.5);
    });
}
if (patientPotraitSubmitBtn) {
    patientPotraitSubmitBtn.addEventListener("click", handleAddPotrait);
}
