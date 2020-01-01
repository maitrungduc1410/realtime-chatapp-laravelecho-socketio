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
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.0/css/bootstrap.min.css" integrity="sha384-SI27wrMjH3ZZ89r4o+fGIJtnzkAnFs3E4qz9DIYioCQ5l9Rd/7UAa8DHcaL8jkWt" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('css/font_awesome/all.min.css') }}">
        <link rel="icon favicon" href="{{ asset('images/favicon.png') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,700,700i&display=swap" rel="stylesheet">
        
        <style>
            body {
                font-family: 'Open Sans', sans-serif !important;
            }
        </style>
    </head>
    <body>
        <div id="app" class="h-100"></div>

        <script>window.__app__ = @json($data)</script>
        <script src="{{ mix('js/manifest.js') }}"></script>
        <script src="{{ mix('js/vendor.js') }}"></script>
        <script src="{{ mix('js/app.js') }}"></script>

        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.0/js/bootstrap.min.js" integrity="sha384-3qaqj0lc6sV/qpzrc1N5DC6i1VRn/HyX4qdPaiEFbn54VjQBEU341pvjz7Dv3n6P" crossorigin="anonymous"></script>
    </body>
</html>
