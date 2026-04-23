@extends('layouts-main.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi DHS Dipanet Hotspot Solution')

@section('content')

<style>
    :root {
        --navy-dark: #0f172a;
        --navy-main: #1e293b;
        --navy-soft: #334155;
        --navy-light: #243449;
        --blue-accent: #3b82f6;
        --soft-white: #f8fafc;
    }

    body {
        background-color: #0f172a;
    }

    .modern-card {
        background: var(--navy-main);
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        color: #e2e8f0;
    }

    .modern-light-box {
        background: var(--navy-light);
        border-radius: 16px;
        padding: 25px;
        transition: 0.3s ease;
        height: 100%;
    }

    .modern-light-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    }

    .section-title {
        color: #f8fafc;
        font-weight: 600;
    }

    .label-soft {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .total-highlight {
        font-size: 1.8rem;
        font-weight: 700;
        color: #4ade80;
    }

    .btn-modern-primary {
        background: var(--blue-accent);
        border: none;
        border-radius: 30px;
        padding: 10px 30px;
        font-weight: 600;
    }

    .btn-modern-primary:hover {
        background: #2563eb;
    }

    .btn-modern-outline {
        border: 1px solid var(--blue-accent);
        color: var(--blue-accent);
        border-radius: 30px;
        padding: 10px 30px;
        font-weight: 600;
    }

    .btn-modern-outline:hover {
        background: var(--blue-accent);
        color: #fff;
    }

    .badge-modern {
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-paid {
        background: #16a34a;
    }

    .badge-unpaid {
        background: #dc2626;
    }

    .info-row {
        border-bottom: 1px solid #334155;
        padding: 10px 0;
    }

    .info-row:last-child {
        border-bottom: none;
    }
    /* SWEETALERT DARK THEME */
.swal2-popup {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-radius: 16px !important;
}

.swal2-title {
    color: #e2e8f0 !important;
}

.swal2-html-container {
    color: #94a3b8 !important;
}

.swal2-icon.swal2-success {
    border-color: #22c55e !important;
}

.swal2-timer-progress-bar {
    background: #3b82f6 !important;
}
</style>

<div class="container py-5">

@php
    $jatuhTempo = $transaksi->jatuh_tempo;
    $isOverdue = $transaksi->isOverdue();
    $canPay = $transaksi->status === 'unpaid';
@endphp

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('finance.transaksi.index') }}"
           class="btn btn-modern-outline">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>

        <span class="badge-modern {{ $transaksi->status == 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
            {{ strtoupper($transaksi->status) }}
        </span>
    </div>

    <!-- MAIN CARD -->
    <div class="card modern-card">
        <div class="card-body p-5">

            <!-- CUSTOMER HEADER -->
            <div class="text-center mb-5">
                <h2 class="fw-bold text-white">{{ $transaksi->nama_customer }}</h2>
                <div class="label-soft">
                    Kode Transaksi: {{ $transaksi->kode_transaksi }}
                </div>
            </div>

            <!-- INFO GRID -->
            <div class="row g-4 mb-5">

                <div class="col-md-4">
                    <div class="modern-light-box text-center">
                        <div class="label-soft mb-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Tanggal Transaksi
                        </div>
                        <div class="fw-semibold fs-5 text-white">
                            {{ $transaksi->tanggal->format('d M Y') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="modern-light-box text-center">
                        <div class="label-soft mb-2">
                            <i class="fas fa-clock me-1"></i>
                            Jatuh Tempo
                        </div>
                        <div class="fw-semibold fs-5 {{ $isOverdue ? 'text-danger' : 'text-white' }}">
                            {{ $jatuhTempo->format('d M Y') }}
                        </div>
                        @if($isOverdue)
                            <div class="mt-2">
                                <span class="badge badge-unpaid">OVERDUE</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="modern-light-box text-center">
                        <div class="label-soft mb-2">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            Total Pembayaran
                        </div>
                        <div class="total-highlight">
                            Rp {{ number_format($transaksi->total,0,',','.') }}
                        </div>
                    </div>
                </div>

            </div>

            <!-- DETAIL CUSTOMER -->
            @if($transaksi->deskripsi)
                <div class="mb-5">
                    <h5 class="section-title text-center mb-4">
                        <i class="fas fa-user-circle me-2"></i>
                        Detail Informasi Customer
                    </h5>

                    <div class="modern-light-box">
                        @foreach($transaksi->deskripsi as $key => $value)
                            <div class="row info-row">
                                <div class="col-md-6 label-soft text-capitalize">
                                    {{ str_replace('_', ' ', $key) }}
                                </div>
                                <div class="col-md-6 fw-semibold text-md-end text-white">
                                    {{ $value }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ACTION -->
            <div class="text-center mt-4">

                @if($canPay)
                    <a href="{{ route('finance.transaksi.payment.form', $transaksi->id) }}"
                       class="btn btn-modern-primary me-2">
                        <i class="fas fa-credit-card me-1"></i>
                        Proses Pembayaran
                    </a>
                @endif

                @if($transaksi->status === 'paid')
                    <a href="{{ route('finance.transaksi.receipt', $transaksi->id) }}"
                       class="btn btn-modern-outline"
                       target="_blank">
                        <i class="fas fa-print me-1"></i>
                        Print / Download Receipt
                    </a>
                @endif

            </div>

        </div>
    </div>

</div>
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function(){

@if(session('success'))

Swal.fire({
    icon: 'success',
    title: 'Pembayaran Berhasil',
    text: "{{ session('success') }}",
    background: '#1e293b',
    color: '#e2e8f0',
    iconColor: '#22c55e',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    backdrop: `
        rgba(15,23,42,0.85)
    `
});

@endif


@if(session('error'))

Swal.fire({
    icon: 'error',
    title: 'Terjadi Kesalahan',
    text: "{{ session('error') }}",
    background: '#1e293b',
    color: '#e2e8f0',
    iconColor: '#ef4444',
    showConfirmButton: false,
    timer: 2500,
    backdrop: `
        rgba(15,23,42,0.85)
    `
});

@endif

});
</script>
@endpush
@endsection