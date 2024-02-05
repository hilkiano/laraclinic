<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="No description.">
    @if (App::environment())
        <title>{{ $title }}</title>
    @else
        <title>{{ $title }} ~ TEST ~</title>
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.css" crossorigin="anonymous">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
