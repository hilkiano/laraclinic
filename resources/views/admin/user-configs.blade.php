<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Configuration'])

<body>
    @include('template.navbar', ['title' => 'Configuration'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <p class="fs-1">Configuration</p>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label for="name" class="form-label">Display Name</label>
                            <input autocomplete="off" type="text" class="form-control" id="name" name="name"
                                value="{{ $user->id }}" />
                            <div id="name_feedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row gy-2">
                        <div class="col-sm-12 col-md-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope"></i></span>
                                <input autocomplete="off" placeholder="john_doe@mail.com" type="text"
                                    class="form-control" id="email" name="email">
                                <div id="email_feedback" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1">+62</span>
                                <input autocomplete="off" placeholder="81212121212" type="text" class="form-control"
                                    id="phone_number" name="phone_number" maxlength="15">
                                <div id="phone_number_feedback" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mt-4">Change Password</h5>
                    <div class="row gy-2">
                        <div class="col-sm-12 col-md-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                <input autocomplete="off" type="password" class="form-control" id="new_password"
                                    name="new_password">
                                <div id="new_password_feedback" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                <input autocomplete="off" type="password" class="form-control" id="confirm_password"
                                    name="confirm_password">
                                <div id="confirm_password_feedback" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4" />
                    {{-- @if ($user->group_id === 3)
                    @include('admin.user-configs-doctor', ['user' => $user])
                    @endif --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-lg btn-primary" id="save-btn"><i
                                    class="bi bi-save me-2"></i>Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
@include('toasts.live_toast')
@include('admin.js.user-configs_js', ['user' => $user])
@include('template.footer')
