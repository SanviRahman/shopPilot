@extends('errors.layout')
@section('title', 'Server Error')
@section('code', '500')
@section('icon', 'fa-tools')
@section('heading', 'Something Went Wrong')
@section('message', 'We hit an unexpected server error while processing your request. Please try again shortly. Your shopping data remains safe.')
@section('actions')<a href="javascript:location.reload()" class="error-btn error-btn-primary"><i class="fas fa-redo-alt"></i> Try Again</a><a href="/" class="error-btn error-btn-secondary"><i class="fas fa-home"></i> Go to Homepage</a>@endsection
