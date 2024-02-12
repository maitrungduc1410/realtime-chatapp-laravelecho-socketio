@extends('layouts.base')

@section('scripts')
@vite(['resources/sass/app.scss', 'resources/js/app.js'])
@endsection

@section('body')
    <div id="app" class="h-100">
    </div>
    @yield('content')
@endsection