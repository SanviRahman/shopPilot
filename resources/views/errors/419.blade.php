@extends('errors.layout')
@section('title', 'Page Expired')
@section('code', '419')
@section('icon', 'fa-clock')
@section('heading', 'Your Session Has Expired')
@section('message', 'For your security, this form session has expired. Refresh the page and submit your request again.')
@section('actions')<a href="javascript:location.reload()" class="error-btn error-btn-primary"><i class="fas fa-redo-alt"></i> Refresh Page</a><a href="/" class="error-btn error-btn-secondary"><i class="fas fa-home"></i> Go to Homepage</a>@endsection
