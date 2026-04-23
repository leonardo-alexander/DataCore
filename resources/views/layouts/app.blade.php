<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DataCore')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-background font-sans">

    @yield('content')

</body>

</html>
