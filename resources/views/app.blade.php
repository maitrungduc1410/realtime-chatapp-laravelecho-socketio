@extends('layouts.app-auth')

@section('content')
@if (env('APP_IS_DOCKER') == 'true')
<script src="/socket.io/socket.io.js"></script>
@else
<script src="http://localhost:6001/socket.io/socket.io.js"></script>
@endif

<script>window.__app__ = @json($data)</script>
@endsection