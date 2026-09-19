@extends('adminlte::page')

@section('plugins.Sweetalert2', true)

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
                                    <a href="{{ route('admin.redirect') }}" class="text-primary">
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
    @yield('page_content')
@stop

@section('footer')
    @include('backoffice.admin.includes.footer')
@stop

@push('js')
    @include('backoffice.admin.includes.custom_js')
@endpush

@push('css')
    @include('backoffice.admin.includes.custom_css')
@endpush

@push('js')
    <script>
        window.showAlert = function (message, type = 'success') {
            const normalizedType = type === 'danger' ? 'error' : type;
            const text = Array.isArray(message) ? message.join('\n') : String(message);
            const options = {
                toast: true,
                position: 'top-end',
                type: normalizedType,
                title: text,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                return window.Swal.fire(options);
            }

            if (typeof window.swal === 'function') {
                return window.swal(options);
            }
        };

        window.addEventListener('load', function () {
            const flashMessage = @json(session('success') ?? session('error'));
            const flashType = @json(session()->has('error') ? 'error' : 'success');
            const validationErrors = @json($errors->all());
            const query = new URLSearchParams(window.location.search);
            const queryMessage = query.get('toast_message');
            const queryType = query.get('toast_type') || 'success';

            if (flashMessage && typeof showAlert === 'function') {
                showAlert(flashMessage, flashType);
            } else if (queryMessage && typeof showAlert === 'function') {
                showAlert(queryMessage, queryType);
                query.delete('toast_message');
                query.delete('toast_type');
                const cleanQuery = query.toString();
                const cleanUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '') + window.location.hash;
                window.history.replaceState({}, document.title, cleanUrl);
            }

            if (validationErrors.length && typeof showAlert === 'function') {
                showAlert(validationErrors, 'error');
            }
        });
    </script>
@endpush
