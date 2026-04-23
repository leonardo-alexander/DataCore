<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DataCore')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-background font-sans">

    @yield('content')

</body>

</html>
