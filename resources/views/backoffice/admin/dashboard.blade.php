@extends('layouts.admin')

@section('meta_title', 'Admin Dashboard')

@section('page_content')
    <div class="card">
        <div class="card-body">
            Welcome, {{ auth()->user()->name }}.
        </div>
    </div>
@endsection