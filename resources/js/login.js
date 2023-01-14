// Elements
const loginForm = document.getElementById("loginForm");
const loginBtn = document.getElementById("loginBtn");
const usernameField = document.getElementById("username");
const passwordField = document.getElementById("password");
const passwordFeedback = document.getElementById("passwordFeedback");

/**
 * Clear validation classname IF it has value
 */
const clearClass = (event) => {
    if (event.target.value !== "") {
        if (event.target.id === "password") {
            passwordFeedback.innerHTML = "Please fill this field.";
        }

        document
            .getElementById(event.target.id)
            .classList.remove("is-invalid", "is-valid");
    }
};

/**
 * Send login request with fetch API
 */
const loginHandler = (event) => {
    event.preventDefault();
    loginBtn.classList.add("disabled");
    loginBtn.insertAdjacentHTML(
        "afterbegin",
        '<div id="loginLoading" class="spinner-grow spinner-grow-sm me-2"></div>'
    );
    const url = "/api/v1/login";
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const requestBody = {
        username: document.getElementById("username").value,
        password: document.getElementById("password").value,
        rememberMe: document.getElementById("rememberMe").checked,
    };
    fetch(url, {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json, text-plain, */*",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
        },
        method: "post",
        credentials: "same-origin",
        body: JSON.stringify(requestBody),
    })
        .then((resp) => resp.json())
        .then((resp) => {
            loginBtn.classList.remove("disabled");
            document.getElementById("loginLoading").remove();
            if (typeof resp === "object") {
                if ("errors" in resp) {
                    handleError(resp.errors, true);
                } else {
                    if (resp.status) {
                        window.location = "/";
                    } else {
                        handleError(resp.message, false);
                    }
                }
            }
        });
};

/**
 * Iterate errors and add invalid class to matching form fields
 */
const handleError = (errors, validation = false) => {
    if (validation) {
        for (let error in errors) {
            document.getElementById(error).classList.add("is-invalid");
        }
    } else {
        passwordFeedback.innerHTML = "Username or password not matched.";
        passwordField.classList.add("is-invalid");
    }
};

// Elements events
if (loginForm) {
    loginForm.addEventListener("submit", loginHandler);
}
if (usernameField) {
    usernameField.addEventListener("blur", clearClass);
}
if (passwordField) {
    passwordField.addEventListener("blur", clearClass);
}
