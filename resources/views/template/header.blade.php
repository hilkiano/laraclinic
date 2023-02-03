<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if (App::environment())
    <title>{{ $title }}</title>
    @else
    <title>{{ $title }} ~ TEST ~</title>
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>