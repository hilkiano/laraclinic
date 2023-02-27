<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'My Assignment'])

<body>
    @include('template.navbar', ['title' => 'My Assignment'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                </div>
            </div>
        </div>
    </div>
</body>

</html>
@include('appointments.js.assignment_js')
@include('toasts.live_toast')
@include('template.footer')