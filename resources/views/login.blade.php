<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Apotek') }}</title>
    <!-- Scripts -->
    <script src="{{ asset('build/assets/app2.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('build/assets/app.css') }}" rel="stylesheet">
</head>

<body class="vh-100">
    <div class="container vh-100">
        <div class="row vh-100 d-flex justify-content-center align-items-center">
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="card">
                    <ul class="list-group list-group-flush">
                        <form id="loginForm">
                            <li class="list-group-item">

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="username" class="form-control" id="username">
                                    <div class="invalid-feedback">Please fill this field.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" onblur="clearClass">
                                    <div class="invalid-feedback">Please fill this field.</div>
                                </div>
                            </li>
                            <li class="list-group-item d-grid gap-2 p-4">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </li>
                        </form>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</body>

</html>
<script type="text/javascript">
    /**
     * Clear validation classname IF it has value
     */
    const clearClass = (event) => {
        if (event.target.value !== "") {
            document.getElementById(event.target.id).classList.remove("is-invalid", "is-valid");
        }
    }

    /**
     * Send login request with fetch API
     */
    const loginHandler = (event) => {
        event.preventDefault();

        const url = "/api/v1/login";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const requestBody = {
            "username": document.getElementById("username").value,
            "password": document.getElementById("password").value,
        };
        fetch(url, {
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json, text-plain, */*",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken
                },
                method: 'post',
                credentials: "same-origin",
                body: JSON.stringify(requestBody)
            })
            .then((resp) => resp.json())
            .then((resp) => {
                if (typeof resp === 'object') {
                    if ('errors' in resp) {
                        handleError(resp.errors);
                    } else {
                        console.log("s", resp);
                    }
                }
            });
    }

    /**
     * Iterate errors and add invalid class to matching form fields
     */
    const handleError = (errors) => {
        for (let error in errors) {
            document.getElementById(error).classList.add("is-invalid");
        }
    }

    // Elements events
    document.getElementById("loginForm").addEventListener('submit', loginHandler);
    document.getElementById("username").addEventListener('blur', clearClass);
    document.getElementById("password").addEventListener('blur', clearClass);
</script>