<script type="module">
    const nameField = document.getElementById("name");
    const emailField = document.getElementById("email");
    const phoneField = document.getElementById("phone_number");
    const newPassword = document.getElementById("new_password");
    const confirmPassword = document.getElementById("confirm_password");
    const liveToast = document.getElementById("liveToast");
    let userData;

    const handleSave = async () => {
        // Remove invalid class
        nameField.classList.remove("is-invalid");
        emailField.classList.remove("is-invalid");
        phoneField.classList.remove("is-invalid");
        newPassword.classList.remove("is-invalid");
        confirmPassword.classList.remove("is-invalid");

        const submitBtn = document.getElementById("save-btn");
        submitBtn.classList.add("disabled");
        submitBtn.insertAdjacentHTML(
            "afterbegin",
            '<div id="submitLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
        );
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const requestBody = {
            name: nameField.value,
            email: emailField.value,
            phone_number: phoneField.value,
            new_password: newPassword.value,
            confirm_password: confirmPassword.value,
            schedule: localStorage.getItem('schedule') ? JSON.parse(localStorage.getItem('schedule')) : null
        };

        const req = await fetch("/api/v1/user/save-configs", {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            method: "post",
            credentials: "same-origin",
            body: JSON.stringify(requestBody),
        });
        const response = await req.json();
        if (response) {
            const toast = new bootstrap.Toast(liveToast);
            submitBtn.classList.remove("disabled");
            document.getElementById("submitLoading").remove();

            if (response.status) {
                document
                    .getElementById("errorToastHeader")
                    .classList.add("d-none");
                document
                    .getElementById("errorToastHeader")
                    .classList.remove("d-block");
                document
                    .getElementById("successToastHeader")
                    .classList.add("d-block");
                document
                    .getElementById("successToastHeader")
                    .classList.remove("d-none");
                document.getElementById("toastBody").innerHTML =
                    response.message;
                toast.show();

                $("#newPassword").val("");
                $("#confirmPassword").val("");
            } else {
                document
                    .getElementById("errorToastHeader")
                    .classList.add("d-block");
                document
                    .getElementById("errorToastHeader")
                    .classList.remove("d-none");
                document
                    .getElementById("successToastHeader")
                    .classList.add("d-none");
                document
                    .getElementById("successToastHeader")
                    .classList.remove("d-block");
                if (typeof response.message === "string") {
                    document.getElementById("toastBody").innerHTML =
                        response.message;
                    toast.show();
                } else if (response.message instanceof Object) {
                    handleError(response.message, true);
                }
            }
        }
    }

    /**
     * Iterate errors and add invalid class to matching form fields
     */
    const handleError = (errors, validation = false) => {
        if (validation) {
            for (let error in errors) {
                $(`#${error}_feedback`).html(errors[error][0]);
                document.getElementById(error).classList.add("is-invalid");
            }
        }
    };

    $(document).ready(function() {
        IMask(phoneField, {
            mask: Number,
            signed: false,
        });

        userData = JSON.parse('{!! $user !!}');
        nameField.value = userData.name;
        emailField.value = userData.email;
        phoneField.value = userData.phone_number;

        $("#save-btn").on('click', handleSave);
    });
</script>
