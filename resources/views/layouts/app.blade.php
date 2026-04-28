<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DataCore')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap (kalau mau pakai) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-background">

    @yield('content')

</body>
</html>