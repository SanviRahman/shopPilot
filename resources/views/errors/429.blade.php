@extends('errors.layout')
@section('title', 'Too Many Requests')
@section('code', '429')
@section('icon', 'fa-hourglass-half')
@section('heading', 'Too Many Requests')
@section('message', 'Too many requests were received in a short time. Please wait a moment and try again.')
@section('actions')<a href="javascript:location.reload()" class="error-btn error-btn-primary"><i class="fas fa-redo-alt"></i> Try Again</a><a href="/" class="error-btn error-btn-secondary"><i class="fas fa-home"></i> Go to Homepage</a>@endsection
