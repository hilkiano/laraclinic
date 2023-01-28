<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Dashboard'])

<body>
    @include('template.navbar', ['title' => 'Dashboard'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <p class="fs-3 fw-bolder mb-0">Hi, {{ $user->name }}!</p>
                                    <p class="text-muted">{{ $user->group->name }}</p>
                                </div>
                                <div class="card-footer">
                                    <p class="text-muted mb-0">Last login: </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

@include('template.footer')