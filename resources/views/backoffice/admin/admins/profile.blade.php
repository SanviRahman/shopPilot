@extends('layouts.admin')

@section('meta_title', 'Admin Profile')

@section('page_content')
<form action="{{ route('admin.update_profile') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
    @csrf

    <div class="row">
        {{-- Profile Summary --}}
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card profile-card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="profile-photo-wrapper mx-auto mb-3">
                        <img src="{{ $admin->getFirstMediaUrl('avatar') ?: ($admin->getFirstMediaUrl('avatars') ?: ($admin->getFirstMediaUrl('profile_photo') ?: asset('vendor/adminlte/dist/img/user2-160x160.jpg'))) }}"
                            id="profile-photo-preview" class="profile-photo" alt="{{ $admin->name }}">
                    </div>

                    <h4 class="font-weight-bold text-dark mb-1">
                        {{ $admin->name ?: 'Admin User' }}
                    </h4>

                    <p class="text-muted mb-2">
                        {{ $admin->email ?: 'No email found' }}
                    </p>

                    <span class="badge badge-primary px-3 py-2 rounded-pill">
                        <i class="fas fa-user-shield mr-1"></i>
                        Admin Account
                    </span>

                    <hr class="my-4">

                    <div class="text-left profile-info-box">
                        <div class="d-flex align-items-center mb-3">
                            <div class="profile-info-icon bg-primary-soft text-primary">
                                <i class="fas fa-user"></i>
                            </div>

                            <div class="ml-3">
                                <small class="text-muted d-block">
                                    Name
                                </small>

                                <strong>
                                    {{ $admin->name ?: 'N/A' }}
                                </strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="profile-info-icon bg-success-soft text-success">
                                <i class="fas fa-envelope"></i>
                            </div>

                            <div class="ml-3">
                                <small class="text-muted d-block">
                                    Email
                                </small>

                                <strong class="text-break">
                                    {{ $admin->email ?: 'N/A' }}
                                </strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="profile-info-icon bg-warning-soft text-warning">
                                <i class="fas fa-camera"></i>
                            </div>

                            <div class="ml-3">
                                <small class="text-muted d-block">
                                    Photo
                                </small>

                                <strong>
                                    {{ $admin->hasProfilePhoto() ? 'Uploaded' : 'Not uploaded' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Form --}}
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card border-0 shadow-sm profile-form-card">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h5 class="card-title mb-1 font-weight-bold">
                        <i class="fas fa-edit text-primary mr-2"></i>
                        Profile Information
                    </h5>

                    <small class="text-muted">
                        Update your name, email address and profile photo.
                    </small>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        {{-- Name --}}
                        <div class="col-md-6 form-group">
                            <label for="name" class="profile-label">
                                Full Name
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                </div>

                                <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}"
                                    class="form-control shadow-none @error('name') is-invalid @enderror"
                                    placeholder="Enter your full name" maxlength="120" required>
                            </div>

                            @error('name')
                            <span class="text-danger small d-block mt-1">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6 form-group">
                            <label for="email" class="profile-label">
                                Email Address
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                </div>

                                <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}"
                                    class="form-control shadow-none @error('email') is-invalid @enderror"
                                    placeholder="Enter your email address" maxlength="191" required>
                            </div>

                            @error('email')
                            <span class="text-danger small d-block mt-1">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>

                        {{-- Profile Photo --}}
                        <div class="col-12 form-group">
                            <x-backoffice.media-picker name="photo" input-id="photo" label="Profile Photo"
                                choose-label="Select profile photo..." media-id-name="photo_media_id"
                                picker-url="{{ route('admin.media.picker') }}"
                                preview-url="{{ $admin->getFirstMediaUrl('avatars') ?: '' }}" />

                            @error('photo')
                            <span class="text-danger small d-block mt-1">
                                {{ $message }}
                            </span>
                            @enderror

                            @error('photo_media_id')
                            <span class="text-danger small d-block mt-1">
                                {{ $message }}
                            </span>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                Recommended: JPG, PNG or WEBP. Maximum size 2MB.
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="d-flex">
                            <div class="mr-3 text-primary">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>

                            <div>
                                <strong>Profile Tip</strong>

                                <p class="mb-0 text-muted small">
                                    Keep your name and email address updated.
                                    You can upload a new image or select an existing image from Media.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top px-4 py-3 text-right">
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <i class="fas fa-save mr-1"></i>
                        Update Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Shared Media Picker Modal --}}
@include('backoffice.admin.media.partials.picker-modal')
@endsection

@push('css')
<style>
.profile-card,
.profile-form-card {
    border-radius: 14px;
    overflow: hidden;
}

.profile-card {
    background: linear-gradient(180deg,
            #ffffff 0%,
            #f8fafc 100%);
}

.profile-photo-wrapper {
    width: 155px;
    height: 155px;
    border-radius: 50%;
    padding: 5px;
    background: linear-gradient(135deg,
            #007bff,
            #20c997);
    box-shadow: 0 12px 30px rgba(0, 123, 255, 0.18);
}

.profile-photo {
    width: 145px;
    height: 145px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #ffffff;
    background: #f8f9fa;
}

.profile-label {
    color: #6c757d;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 6px;
}

.profile-info-box {
    border: 1px solid #edf0f5;
    border-radius: 12px;
    padding: 16px;
    background: #ffffff;
}

.profile-info-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 38px;
}

.bg-primary-soft {
    background: rgba(0, 123, 255, 0.12);
}

.bg-success-soft {
    background: rgba(40, 167, 69, 0.12);
}

.bg-warning-soft {
    background: rgba(255, 193, 7, 0.18);
}

.input-group-text {
    border-color: #e9ecef;
}

.form-control,
.custom-file-label {
    border-color: #e9ecef;
    border-radius: 8px;
}

.input-group .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.input-group .input-group-text {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.btn {
    border-radius: 8px;
    font-weight: 600;
}

@media (max-width: 767px) {
    .profile-photo-wrapper {
        width: 135px;
        height: 135px;
    }

    .profile-photo {
        width: 125px;
        height: 125px;
    }
}
</style>
@endpush

@push('js')
<script>
$(function() {
    const profilePreview = document.getElementById(
        'profile-photo-preview'
    );

    const pickerPreview = document.getElementById(
        'photo-preview'
    );

    const fallbackImage = @json(
        asset('vendor/adminlte/dist/img/user2-160x160.jpg')
    );

    if (!profilePreview) {
        return;
    }

    const syncProfilePreview = function() {
        if (!pickerPreview) {
            return;
        }

        const selectedImage = pickerPreview.getAttribute('src');

        profilePreview.src = selectedImage || fallbackImage;
    };

    if (pickerPreview) {
        const observer = new MutationObserver(function() {
            syncProfilePreview();
        });

        observer.observe(pickerPreview, {
            attributes: true,
            attributeFilter: ['src']
        });

        syncProfilePreview();
    }

    $(document).on('change', '#photo', function() {
        const file = this.files && this.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function(event) {
            profilePreview.src = event.target.result;
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endpush