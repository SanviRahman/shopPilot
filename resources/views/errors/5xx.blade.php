@extends('errors.layout')
@section('title', 'Server Error')
@section('code', '5xx')
@section('icon', 'fa-tools')
@section('heading', 'We Are Fixing This')
@section('message', 'A temporary server problem prevented the page from loading. Please try again in a moment.')
@section('actions')<a href="javascript:location.reload()" class="error-btn error-btn-primary"><i class="fas fa-redo-alt"></i> Try Again</a><a href="/" class="error-btn error-btn-secondary"><i class="fas fa-home"></i> Go to Homepage</a>@endsection
