<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @hasSection('title')
            @yield('title') | Epic Store
        @else
            Epic Store
        @endif
    </title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    @include('navbar')

    <div class="content">
        @yield('content')
    </div>

    @include('footer')
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
