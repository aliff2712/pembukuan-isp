@extends('layouts-main.app')

@section('title', 'Add Chart of Account')
@section('page-title', 'Add Chart of Account')

@section('content')
<style>
    .swal2-popup {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-radius: 16px !important;
    border: 1px solid rgba(255,255,255,0.05);
}

.swal2-title {
    color: #ffffff !important;
    font-weight: 600;
}

.swal2-html-container {
    color: #cbd5e1 !important;
}

/* BUTTON CONFIRM */
.swal2-confirm {
    background: #2563eb !important;
    border-radius: 10px !important;
    padding: 8px 20px !important;
}

/* BUTTON CANCEL */
.swal2-cancel {
    background: #334155 !important;
    border-radius: 10px !important;
}

/* MOBILE SIZE */
@media (max-width: 576px) {
    .swal2-popup {
        width: 85% !important;
        font-size: 14px;
    }
}

</style>
<div class="container-fluid">

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Error!</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Add Account') }}</h6>
            <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('chart-of-accounts.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="account_code" class="form-label">{{ __('Account Code') }}</label>
                    <input type="text" name="account_code" id="account_code" 
                           class="form-control @error('account_code') is-invalid @enderror" 
                           value="{{ old('account_code') }}" maxlength="20" required>
                    @error('account_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="account_name" class="form-label">{{ __('Account Name') }}</label>
                    <input type="text" name="account_name" id="account_name" 
                           class="form-control @error('account_name') is-invalid @enderror" 
                           value="{{ old('account_name') }}" maxlength="255" required>
                    @error('account_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="account_type" class="form-label">{{ __('Account Type') }}</label>
                    <select name="account_type" id="account_type" 
                            class="form-select @error('account_type') is-invalid @enderror" required>
                        <option value="">-- Select Type --</option>
                        @foreach($accountTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('account_type') == $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="is_cash" name="is_cash" 
                           @checked(old('is_cash'))>
                    <label class="form-check-label" for="is_cash">{{ __('Is Cash / Bank Account') }}</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('Save') }}
                    </button>
                    <a href="{{ route('chart-of-accounts.index') }}" 
   class="btn btn-secondary" 
   id="cancelBtn">
   
{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

const cancelBtn = document.getElementById('cancelBtn');

if (cancelBtn) {

cancelBtn.addEventListener('click', function(e){

    e.preventDefault();

    const targetUrl = this.href;

    Swal.fire({
        title: "Batalkan Proses Pembuatan Akun?",
    text: "Perubahan yang belum disimpan akan hilang. Apakah Anda yakin ingin kembali?",
        icon: "warning",
        width: window.innerWidth < 576 ? '85%' : '420px',
        showCancelButton: true,
        confirmButtonText: "Ya, Batalkan",
        cancelButtonText: "Tetap di Halaman",
        confirmButtonColor: "#2563eb",
        cancelButtonColor: "#475569",
        background: "#1e293b",
        color: "#e2e8f0"
    }).then((result) => {

        if (result.isConfirmed) {
            window.location.href = targetUrl;
        }

    });

});

}

</script>

@endsection
