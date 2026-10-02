@extends('layouts.admin')
@section('meta_title',$pixel->exists ? 'Edit Meta Pixel' : 'Add Meta Pixel')
@section('page_content')
<form method="POST" action="{{ $pixel->exists ? route('admin.meta-pixels.update',$pixel) : route('admin.meta-pixels.store') }}">
    @csrf @if($pixel->exists) @method('PUT') @endif
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm"><div class="card-header"><h3 class="card-title font-weight-bold">Pixel Configuration</h3></div><div class="card-body">
                <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$pixel->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-group"><label>Pixel ID(s) *</label><textarea name="pixel_ids_text" rows="3" class="form-control @error('pixel_ids') is-invalid @enderror" placeholder="123456789012345, 987654321098765" required>{{ old('pixel_ids_text', implode(', ', $pixel->pixel_ids ?? [])) }}</textarea><small class="form-text text-muted">Multiple IDs supported. Separate with comma, space or new line.</small>@error('pixel_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @error('pixel_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                <div class="form-group"><label>Full Meta Script (optional)</label><textarea name="full_script" rows="12" class="form-control font-monospace @error('full_script') is-invalid @enderror" placeholder="Paste the full Meta Pixel script here when you need custom/manual code...">{{ old('full_script',$pixel->full_script) }}</textarea><small class="form-text text-warning"><i class="fas fa-shield-alt mr-1"></i>This script is rendered only for active lifecycle entries. Access is restricted to authorized admins.</small>@error('full_script')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-info shadow-sm"><div class="card-header"><h3 class="card-title font-weight-bold">Lifecycle</h3></div><div class="card-body">
                <div class="form-group"><label>Status *</label><select name="lifecycle_status" class="custom-select">@foreach(\App\Models\MetaPixel::LIFECYCLE as $state)<option value="{{ $state }}" @selected(old('lifecycle_status',$pixel->lifecycle_status)===$state)>{{ ucfirst($state) }}</option>@endforeach</select></div>
                <div class="form-group"><label>Starts At</label><input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($pixel->starts_at)->format('Y-m-d\\TH:i')) }}"></div>
                <div class="form-group"><label>Ends At</label><input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($pixel->ends_at)->format('Y-m-d\\TH:i')) }}"></div>
                <div class="custom-control custom-switch mb-3"><input type="checkbox" class="custom-control-input" id="trackPageView" name="track_page_view" value="1" @checked(old('track_page_view',$pixel->track_page_view))><label class="custom-control-label" for="trackPageView">Track PageView</label></div>
                <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="trackEcommerce" name="track_ecommerce" value="1" @checked(old('track_ecommerce',$pixel->track_ecommerce))><label class="custom-control-label" for="trackEcommerce">Track Ecommerce Events</label></div>
            </div><div class="card-footer"><button class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Save Pixel</button><a href="{{ route('admin.meta-pixels.index') }}" class="btn btn-light border btn-block">Cancel</a></div></div>
        </div>
    </div>
</form>
@endsection
