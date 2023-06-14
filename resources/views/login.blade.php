<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if (App::environment())
        <title>{{ config('app.name', 'Apotek') }}</title>
    @else
        <title>{{ $title }} ~ DEVELOPMENT ~</title>
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="vh-100">
    <div class="container-fluid vh-100">
        <div class="row vh-100 d-flex justify-content-center align-items-center">
            <div class="col-md-5 col-sm-12 col-xs-12" style="max-width: 500px;">
                <div class="card">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">
                                    <p class="mb-0 fs-4">Login</p>
                                </div>
                                <div class="col d-flex justify-content-end align-items-center">
                                    <button data-bs-toggle="modal" href="#helpModal" type="button"
                                        class="btn d-none btn-sm btn-outline-secondary border border-3 rounded-circle"><i
                                            class="bi bi-question-lg"></i></button>
                                </div>
                            </div>

                        </li>
                        <form id="loginForm">
                            <li class="list-group-item border-0 border-bottom">
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

<div id="helpModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupsModalHead">Demo User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="rounded p-3 bg-body-secondary">
                    <p class="mb-1 fw-bold">Receptionist</p>
                    <p class="mb-0">Username: <span class="fst-italic">receptionist</span></p>
                    <p class="mb-0">Password: <span class="fst-italic">12345</span></p>
                    <hr />
                    <p class="mb-1 fw-bold">Doctor</p>
                    <p class="mb-0">Username: <span class="fst-italic">doctor</span></p>
                    <p class="mb-0">Password: <span class="fst-italic">12345</span></p>
                    <hr />
                    <p class="mb-1 fw-bold">Pharmacist</p>
                    <p class="mb-0">Username: <span class="fst-italic">pharmacist</span></p>
                    <p class="mb-0">Password: <span class="fst-italic">12345</span></p>
                    <hr />
                    <p class="mb-1 fw-bold">Cashier</p>
                    <p class="mb-0">Username: <span class="fst-italic">cashier</span></p>
                    <p class="mb-0">Password: <span class="fst-italic">12345</span></p>
                    <hr />
                    <p class="mb-1 fw-bold">Online Shop Admin</p>
                    <p class="mb-0">Username: <span class="fst-italic">olshop</span></p>
                    <p class="mb-0">Password: <span class="fst-italic">12345</span></p>
                    <hr />
                    <p class="mb-1 fw-bold">Owner</p>
                    <p class="mb-0">Username: <span class="fst-italic">owner</span></p>
                    <p class="mb-0">Password: <span class="fst-italic">12345</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

</html>
