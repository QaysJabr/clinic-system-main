<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@yield('title', config('app.name'))</title>
    <style>
        @include('partials.pdf-document-styles', ['forDompdf' => true])
        @stack('pdf-styles')
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
