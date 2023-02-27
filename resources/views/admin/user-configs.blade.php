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
                            <input autocomplete="off" type="text" class="form-control" id="name" name="name" value="{{ $user->id }}" />
                        </div>
                    </div>
                    <div class="row gy-2">
                        <div class="col-sm-12 col-md-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope"></i></span>
                                <input autocomplete="off" placeholder="john_doe@mail.com" type="text" class="form-control" id="email" name="email">
                                <div class="invalid-feedback">Email you entered is not valid.</div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1">+62</span>
                                <input autocomplete="off" placeholder="81212121212" type="text" class="form-control" id="phone_number" name="phone_number" maxlength="15">
                                <div class="invalid-feedback">Phone number you entered is not valid.</div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4" />
                    @if ($user->group_id === 3)
                    @include('admin.user-configs-doctor', ['user' => $user])
                    @endif
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-lg btn-primary" id="save-btn"><i class="bi bi-save me-2"></i>Save</button>
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