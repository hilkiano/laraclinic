<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Apotek') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="vh-100">
    <div class="container-fluid vh-100">
        <div class="row vh-100 d-flex justify-content-center align-items-center">
            <div class="col-md-5 col-sm-12 col-xs-12" style="max-width: 500px;">
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
                                    <input type="password" class="form-control" id="password">
                                    <div id="passwordFeedback" class="invalid-feedback">Please fill this field.</div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item d-grid gap-2 p-4">
                                <button type="submit" id="loginBtn" class="btn btn-primary">
                                    Login
                                </button>
                            </li>
                        </form>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</body>

</html>