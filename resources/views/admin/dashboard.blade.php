<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Dashboard'])

<body>
    @include('template.navbar', ['title' => 'Dashboard'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
        </div>
    </div>
</body>

</html>

@include('template.footer')