@extends('layouts-main.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')

<!-- Alert Success -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user me-2"></i>Informasi Profile
                </h6>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #f6c23e 0%, #f4b619 100%); color: white;">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-lock me-2"></i>Ubah Password
                </h6>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Section -->
<div class="row">
    <div class="col-12">
        <div class="card shadow border-danger">
            <div class="card-header bg-danger text-white py-3">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>Zona Berbahaya
                </h6>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
@endpush

@endsection