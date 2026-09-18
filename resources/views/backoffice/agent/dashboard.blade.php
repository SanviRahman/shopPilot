@extends('layouts.admin')

@section('meta_title', 'Agent Dashboard')

@section('content')
    <div class="card">
        <div class="card-body">
            Welcome, {{ auth()->user()->name }}.
        </div>
    </div>
@endsection