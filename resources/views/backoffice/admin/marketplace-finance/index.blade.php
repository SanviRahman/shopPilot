@extends('layouts.admin')

@section('meta_title', 'Marketplace Finance')

@section('page_content')
    <div class="row">
        <div class="col-lg-4 col-md-6"><div class="small-box bg-info"><div class="inner"><h3>{{ $pendingPayouts }}</h3><p>Open payout requests</p></div><div class="icon"><i class="fas fa-wallet"></i></div><a href="{{ route('admin.marketplace-finance.payouts') }}" class="small-box-footer">Review payouts <i class="fas fa-arrow-circle-right"></i></a></div></div>
        <div class="col-lg-4 col-md-6"><div class="small-box bg-warning"><div class="inner"><h3>{{ $pendingRefunds }}</h3><p>Open refund requests</p></div><div class="icon"><i class="fas fa-undo"></i></div><a href="{{ route('admin.marketplace-finance.refunds') }}" class="small-box-footer">Review refunds <i class="fas fa-arrow-circle-right"></i></a></div></div>
        <div class="col-lg-4 col-md-6"><div class="small-box bg-success"><div class="inner"><h3>{{ $verifiedAccounts }}</h3><p>Verified payout accounts</p></div><div class="icon"><i class="fas fa-university"></i></div><a href="{{ route('admin.marketplace-finance.payouts') }}" class="small-box-footer">Manage transfers <i class="fas fa-arrow-circle-right"></i></a></div></div>
    </div>

    @if((float) $rule->rate_value === 0.0)
        <div class="alert alert-warning">The current commission rate is 0%. No marketplace commission will be charged until an administrator sets a rate.</div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Marketplace commission</h3></div>
        <form method="POST" action="{{ route('admin.marketplace-finance.commission.update') }}">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="rate_value">Commission rate (%)</label>
                        <input id="rate_value" type="number" name="rate_value" class="form-control" min="0" max="100" step="0.0001" value="{{ old('rate_value', $rule->rate_value) }}" required>
                        @error('rate_value')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-8 text-muted small">This rate applies to net merchandise value after allocated discounts. Existing orders retain their original commission snapshot; only new orders use the updated rate.</div>
                </div>
                <div class="mt-3 text-muted small">Current rule started {{ $rule->effective_from?->format('d M Y, h:i A') ?? '—' }}. Commission is not calculated on shipping.</div>
            </div>
            <div class="card-footer bg-white"><button class="btn btn-primary">Save commission rate</button></div>
        </form>
    </div>

    <div class="alert alert-info">Payment collection uses the existing manual bKash, Nagad and Rocket submission flow. Payouts and refunds are recorded only after an administrator confirms the external transfer reference.</div>
@endsection
