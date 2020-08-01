<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="Mai Trung Duc">
        <meta name="description" content="Realtime chat app using Laravel, VueJS, Redis, Laravel Echo, SocketIO">
        <meta name="keywords" content="Realtime chat app, Laravel, VueJS, Laravel Echo, Redis, SocketIO">

        <title>Realtime Chat | Laravel, VueJS, Redis, Laravel Echo, SocketIO</title>

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('css/font_awesome/all.min.css') }}">
        <link rel="icon favicon" href="{{ asset('images/favicon.png') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>
        <div id="app" class="h-100"></div>

        <script>window.__app__ = @json($data)</script>
        <script src="{{ mix('js/manifest.js') }}"></script>
        <script src="{{ mix('js/vendor.js') }}"></script>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>