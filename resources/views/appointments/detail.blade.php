<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Detail Assignment'])

<body>
    @include('template.navbar', ['title' => 'Detail Assignment'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-12">
                            <a href="{{ url('appointments/list') }}" class="btn btn-sm btn-outline-primary"><i class="me-2 bi bi-arrow-bar-left"></i>Go Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
@include('appointments.js.detail_js')
@include('toasts.live_toast')
@include('template.footer')