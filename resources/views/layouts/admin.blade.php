@extends('adminlte::page')

@section('title')
    @hasSection('meta_title')
        @yield('meta_title') |
    @endif
    {{ config('adminlte.title') }}
@stop

@section('meta_tags')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    @if(isset($title) || isset($breadcrumb))
        <div class="container-fluid">
            <div class="row mb-1 align-items-center">
                <div class="col-md-6 col-12 text-center text-md-left">
                    @if(isset($title))
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-layer-group text-primary mr-2"></i>{{ $title }}
                            @if(isset($sub_title))
                                <small class="text-muted font-weight-light ml-md-2">{{ $sub_title }}</small>
                            @endif
                        </h1>
                    @endif
                </div>

                <div class="col-md-6 col-12 mt-3 mt-md-0">
                    @if(isset($breadcrumb) && count($breadcrumb))
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb float-md-right shadow-sm border-0 px-3 py-2 bg-white rounded-pill">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}" class="text-primary">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>

                                @foreach($breadcrumb as $crumb)
                                    @if(isset($crumb['url']) && $crumb['url'])
                                        <li class="breadcrumb-item">
                                            <a href="{{ $crumb['url'] }}" class="text-muted font-weight-bold">{{ $crumb['text'] }}</a>
                                        </li>
                                    @else
                                        <li class="breadcrumb-item active text-secondary" aria-current="page">{{ $crumb['text'] }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    @endif
@stop

@section('content')
    @yield('content')
@stop

@section('footer')
    @include('admin.includes.footer')
@stop

@push('js')
    @include('admin.includes.custom_js')
@endpush

@push('css')
    @include('admin.includes.custom_css')
@endpush