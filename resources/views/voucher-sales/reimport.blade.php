@extends('layouts-main.app')

@section('title', 'Re-import Voucher Sales')
@section('page-title', 'Re-import Voucher Sales from Mikhmon')

@section('content')
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('voucher-sales.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Voucher Sales
    </a>
</div>

<!-- Instructions Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">
            <i class="fas fa-info-circle"></i> Instructions
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-lightbulb"></i> How Re-import Works
            </h5>
            <hr>
            <p class="mb-2">
                Re-import akan menjalankan kembali command import dari data Mikhmon. Gunakan fitur ini untuk:
            </p>
            <ul class="mb-2">
                <li>Memperbaiki data yang error saat import sebelumnya</li>
                <li>Mengimpor data periode tertentu yang terlewat</li>
                <li>Memperbarui data yang sudah ada (dengan flag --force)</li>
            </ul>
            <p class="mb-0">
                <strong>Note:</strong> Proses import mungkin memerlukan waktu beberapa menit tergantung jumlah data.
            </p>
        </div>
    </div>
</div>

<!-- Re-import Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-sync-alt"></i> Re-import Form
        </h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('voucher-sales.reimport') }}" id="reimportForm">
            @csrf

            <div class="row">
                <!-- Date From -->
                <div class="col-md-6 mb-3">
                    <label for="date_from" class="form-label">
                        <i class="far fa-calendar"></i> Date From
                        <small class="text-muted">(Optional)</small>
                    </label>
                    <input type="date" 
                        class="form-control @error('date_from') is-invalid @enderror" 
                        id="date_from" 
                        name="date_from" 
                        value="{{ old('date_from') }}">
                    <div class="form-text">
                        Leave empty to import all available dates
                    </div>
                    @error('date_from')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Date To -->
                <div class="col-md-6 mb-3">
                    <label for="date_to" class="form-label">
                        <i class="far fa-calendar"></i> Date To
                        <small class="text-muted">(Optional)</small>
                    </label>
                    <input type="date" 
                        class="form-control @error('date_to') is-invalid @enderror" 
                        id="date_to" 
                        name="date_to" 
                        value="{{ old('date_to') }}">
                    <div class="form-text">
                        Leave empty to import until latest date
                    </div>
                    @error('date_to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Force Option -->
                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" 
                            type="checkbox" 
                            id="force" 
                            name="force" 
                            value="1"
                            {{ old('force') ? 'checked' : '' }}>
                        <label class="form-check-label" for="force">
                            <strong>Force Re-import</strong>
                            <small class="text-muted d-block">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                This will overwrite existing data. Use with caution.
                            </small>
                        </label>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Quick Select Buttons -->
            <div class="mb-3">
                <label class="form-label">Quick Select:</label>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setToday()">
                        Today
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setThisWeek()">
                        This Week
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setThisMonth()">
                        This Month
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setLastMonth()">
                        Last Month
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearDates()">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('voucher-sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-warning" id="submitBtn">
                    <i class="fas fa-sync-alt"></i> Start Re-import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Warning Card -->
<div class="card shadow mb-4 border-left-warning">
    <div class="card-body">
        <h6 class="font-weight-bold text-warning">
            <i class="fas fa-exclamation-triangle"></i> Important Notes
        </h6>
        <ul class="mb-0 small">
            <li>Re-import process runs in background via artisan command</li>
            <li>Without date filters, all available data will be processed</li>
            <li>Force option will replace existing records - use only when necessary</li>
            <li>Make sure Mikhmon data source is accessible before importing</li>
            <li>Large date ranges may take several minutes to complete</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Quick select functions
function setToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date_from').value = today;
    document.getElementById('date_to').value = today;
}

function setThisWeek() {
    const today = new Date();
    const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));
    const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6));
    
    document.getElementById('date_from').value = firstDay.toISOString().split('T')[0];
    document.getElementById('date_to').value = lastDay.toISOString().split('T')[0];
}

function setThisMonth() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('date_from').value = firstDay.toISOString().split('T')[0];
    document.getElementById('date_to').value = lastDay.toISOString().split('T')[0];
}

function setLastMonth() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
    
    document.getElementById('date_from').value = firstDay.toISOString().split('T')[0];
    document.getElementById('date_to').value = lastDay.toISOString().split('T')[0];
}

function clearDates() {
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';
}

// Form submission confirmation
document.getElementById('reimportForm').addEventListener('submit', function(e) {
    const force = document.getElementById('force').checked;
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    
    let message = 'Are you sure you want to start re-import?';
    
    if (force) {
        message += '\n\nWARNING: Force option is enabled. This will overwrite existing data!';
    }
    
    if (!dateFrom && !dateTo) {
        message += '\n\nNo date range specified. All available data will be imported.';
    }
    
    if (!confirm(message)) {
        e.preventDefault();
    } else {
        // Disable submit button to prevent double submission
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
});
</script>
@endpush