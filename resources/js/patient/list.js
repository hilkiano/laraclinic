// Elements
const url = new URL(window.location.href);
const liveToast = document.getElementById("liveToast");
const filterPatientSelect = document.getElementById("filterPatientSelect");
const filterPatientField = document.getElementById("filterPatientField");
const clearFilterPatientBtn = document.getElementById("clearFilterPatientBtn");
const filterForm = document.getElementById("filterForm");
const patientListModal = document.getElementById("patientListModal");
const assignPharmacyBtn = document.getElementById("assignPharmacyBtn");
const assignDoctorBtn = document.getElementById("assignDoctorBtn");
const clearFilter = (evt) => {
    evt.preventDefault();
    if (
        url.searchParams.has("filter_by") &&
        url.searchParams.has("filter_field")
    ) {
        url.searchParams.delete("filter_by");
        url.searchParams.delete("filter_field");
        window.location = url.href;
    } else {
        console.error("Filter value is not exist in URL.");
    }
};
const updateModalContent = (evt) => {
    const rowData = JSON.parse(evt.relatedTarget.getAttribute("data-row"));
    if (rowData) {
        document.getElementById("patientPotrait").src =
            rowData.patient_potrait.length > 0
                ? `${url.origin}${rowData.patient_potrait[0].url}`
                : `${url.origin}/images/potrait-placeholder.png`;
        document.getElementById("patientListModalHead").innerHTML =
            '<i class="bi bi-person me-2"></i>' + rowData.name;
        document.getElementById("address").innerHTML = rowData.address
            ? rowData.address
            : "-";
        document.getElementById("birth_date").innerHTML =
            rowData.birth_date_formatted;
        document.getElementById("age").innerHTML = rowData.age;
        document.getElementById("phone_number").innerHTML = rowData.phone_number
            ? "+62 " + rowData.phone_number
            : "-";
        document.getElementById("weight").innerHTML = rowData.weight
            ? rowData.weight.toString() + " kg"
            : "-";
        document.getElementById("height").innerHTML = rowData.height
            ? rowData.height.toString() + " cm"
            : "-";
        document.getElementById("additional_note").innerHTML =
            rowData.additional_note ? rowData.additional_note : "-";
        document.getElementById(
            "patientUpdateBtn"
        ).href = `${window.location.origin}/patient/update/${rowData.id}`;
        document
            .getElementById("assignPharmacyBtn")
            .setAttribute("data-row", JSON.stringify(rowData));
        document
            .getElementById("assignDoctorBtn")
            .setAttribute("data-row", JSON.stringify(rowData));
    } else {
        console.error("No row data.");
    }
};
const redirectAssignment = (e, reason) => {
    const data = JSON.parse(e.target.getAttribute("data-row"));
    const newUrl = new URL(window.location.origin);
    newUrl.pathname = '/appointments';
    newUrl.searchParams.set("make", "true");
    newUrl.searchParams.set("makeReason", reason);
    newUrl.searchParams.set("patient", data.id);

    window.location = newUrl.href;
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
// Elements events
if (clearFilterPatientBtn) {
    clearFilterPatientBtn.addEventListener("click", clearFilter);
}
if (filterForm) {
    filterForm.addEventListener("submit", function (evt) {
        if (filterPatientField.value === "") {
            evt.preventDefault();
            return false;
        }
    });
}
if (patientListModal) {
    patientListModal.addEventListener("show.bs.modal", updateModalContent);
}
if (assignPharmacyBtn) {
    assignPharmacyBtn.addEventListener("click", function (e) {
        redirectAssignment(e, "pharmacy")
    });
}
if (assignDoctorBtn) {
    assignDoctorBtn.addEventListener("click", function (e) {
        redirectAssignment(e, "doctor")
    });
}

(function () {
    if (filterPatientField && filterPatientSelect) {
        if (url.searchParams.has("filter_by")) {
            filterPatientSelect.value = url.searchParams.get("filter_by");
        }
        if (url.searchParams.has("filter_field")) {
            filterPatientField.value = url.searchParams.get("filter_field");
        }
    }
})();
