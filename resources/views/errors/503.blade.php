@extends('errors.layout')
@section('title', 'Maintenance')
@section('code', '503')
@section('icon', 'fa-tools')
@section('heading', 'We Will Be Back Soon')
@section('message', 'ShopPilot is temporarily unavailable while we perform maintenance. Please check back shortly.')
@section('actions')<a href="javascript:location.reload()" class="error-btn error-btn-primary"><i class="fas fa-redo-alt"></i> Check Again</a><a href="/" class="error-btn error-btn-secondary"><i class="fas fa-home"></i> Go to Homepage</a>@endsection
