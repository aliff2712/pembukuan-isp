@extends('layouts-main.app')

@section('title', 'File Converter')
@section('page-title', 'Converter XLS / CSV ke XLSX')

@section('content')
<div class="container py-4">

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ERROR --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-success text-white rounded-top-4">
            <h5 class="mb-0">
                <i class="fas fa-file-excel me-2"></i>
                Konversi File ke XLSX
            </h5>
        </div>

        <div class="card-body p-4">

            <form method="POST" action="{{ route('converter.convert') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Upload File (.xls / .csv)
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control form-control-lg"
                           accept=".xls,.csv"
                           required>

                    <div class="form-text">
                        Maksimal ukuran file 5MB.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">

                    <a href="{{ route('finance.transaksi.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-sync-alt me-1"></i>
                        Convert & Download
                    </button>

                </div>
            </form>

        </div>
    </div>

</div>
@endsection