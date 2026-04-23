@extends('layouts-main.app')
@section('title', __('Detail Income'))
@section('page-title', __('Detail Income'))
@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-eye text-info me-2"></i>Detail Income
            </h1>
        </div>
        <a href="{{ route('other-incomes.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Detail Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 text-black">
                        <i class="fas fa-info-circle text-info me-2"></i>Informasi Income
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>
                            <p class="h6 mb-0">
                                <i class="fas fa-calendar text-info me-2"></i>{{ $income->income_date->format('d F Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <p class="mb-0">
                                @if ($income->isPosted())
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Posted
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-pencil-alt me-1"></i>Recorded
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <p class="h6 mb-0">{{ $income->description }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <p class="h5 mb-0 text-success fw-bold">
                            <i class="fas fa-money-bill-wave me-2"></i>Rp{{ number_format($income->amount, 0, ',', '.') }}
                        </p>
                    </div>

                    @if ($income->notes)
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <p class="mb-0">{{ $income->notes }}</p>
                        </div>
                    @endif

                    <hr class="my-4">

                    <div class="row text-sm">
                        <div class="col-md-6">
                            <label class="form-label">Dibuat Oleh</label>
                            <p class="small mb-0">{{ $income->createdBy->name ?? 'Unknown' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ">Waktu Dibuat</label>
                            <p class="small mb-0">{{ $income->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    @if ($income->updated_at != $income->created_at)
                        <div class="row text-sm mt-3">
                            <div class="col-md-6">
                                <label class="form-label ">Diubah Pada</label>
                                <p class="small mb-0">{{ $income->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-cog text-secondary me-2"></i>Aksi
                    </h5>
                </div>
                <div class="card-body d-grid gap-2">
                    @if (!$income->isPosted())
                        <a href="{{ route('other-incomes.edit', $income) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete('{{ route('other-incomes.destroy', $income) }}')">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    @else
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-lock me-2"></i>Data sudah di-posting dan tidak bisa diubah
                        </div>
                    @endif

                    <hr class="my-3">

                    <a href="{{ route('other-incomes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(deleteUrl) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        document.getElementById('deleteForm').action = deleteUrl;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endsection
