@extends('layouts-main.app')

@section('title', 'Transaksi')

@section('content')

<style>

/* ===============================
   TABLE NAVY FULL THEME
=================================*/

.table {
    margin-bottom: 0;
}

/* HEADER */
.table thead th {
    background: var(--navy-main);

    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .5px;
    border: none;
    padding: 14px 16px;
}

/* BODY */
.table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: var(--navy-dark);
    border-color: #000;
}

/* HOVER */
.table-hover tbody tr {
    transition: all .2s ease-in-out;
}

.table-hover tbody tr:hover {
    background: rgba(59, 130, 246, 0.06);
    transform: scale(1.002);
}

/* OVERDUE ROW */
.table tbody tr.overdue {
    background: rgba(220, 38, 38, 0.05);
}

/* TOTAL COLUMN */
.table .text-primary {
    color: black !important;
    font-weight: 700;
}

/* BADGE */
.badge-paid {
    background: #22c55e; /* hijau terang */
    color: #ffffff;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
}

.badge-unpaid {
    background: rgba(0,0,0,0.25);
    color: #dc2626;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
}

/* ACTION BUTTONS */
.btn-outline-primary {
    border-color: #grey;
    color: #ffffff;
    background: #1d4ed8;
    border-radius: 8px;
}

.btn-outline-primary:hover {
    background: #2563eb;
    color: #fff;
}

.btn-success {
    border-radius: 8px;
}

.btn-outline-danger {
    border-radius: 8px;
}


/* Pastikan header tetap putih */
.modern-card .table thead th {
    color: #fff !important;
}

/* Pastikan link dalam table juga hitam */
.modern-card .table a {
    color: #000 !important;
}

/* Icon dalam table */
.modern-card .table i {
    color: inherit !important;
}
/* ===============================
   BACKGROUND NAVY SOFT
=================================*/

body {
    background: #0f172a; /* navy dark */
}

/* ===============================
   SUMMARY CARD (TIMBUL)
=================================*/

.summary-card {
    background: #1e293b; /* beda dikit dari bg */
    border-radius: 18px;
    padding: 24px;
    border: 1px solid rgba(255,255,255,0.06);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.35),   /* drop shadow bawah */
        0 2px 6px rgba(0,0,0,0.25),    /* depth kecil */
        inset 0 1px 0 rgba(255,255,255,0.05); /* highlight atas */

    transition: all .25s ease;
}

.summary-card:hover {
    transform: translateY(-4px);
    box-shadow:
        0 18px 35px rgba(0,0,0,0.45),
        0 4px 10px rgba(0,0,0,0.35),
        inset 0 1px 0 rgba(255,255,255,0.08);
}

/* TEXT */
.summary-card small {
    color: #94a3b8;
    font-size: 13px;
}

.summary-number {
    font-size: 30px;
    font-weight: 700;
    color: #ffffff;
}

.summary-amount {
    margin-top: 4px;
    font-weight: 600;
    font-size: 15px;
    color: #60a5fa;
}

/* ===============================
   FILTER CARD (TIMBUL JUGA)
=================================*/

.filter-card {
    background: #1e293b;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.06);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.35),
        inset 0 1px 0 rgba(255,255,255,0.05);
}

/* LABEL */
.filter-card .form-label {
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 500;
}

/* INPUT */
/* INPUT PUTIH CLEAN */
.filter-card .form-control {
    background: #0f172a; /* navy */
    border: 1px solid #0f172a;
    color: #ffffff; /* biar teks kelihatan */
    border-radius: 12px;
    padding: 9px 14px;
    transition: all .2s ease;
}

.filter-card .form-control::placeholder {
    color: #94a3b8;
}

.filter-card .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
}

.filter-card .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
}

/* BUTTON */
.btn-navy {
    background: #2563eb;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
}

.btn-navy:hover {
    background: #1d4ed8;
}
/* ===============================
   SWEETALERT NAVY THEME
=================================*/

.swal2-popup {
    background: #1e293b !important; /* navy */
    color: #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 24px !important;
}

.swal2-title {
    color: #ffffff !important;
    font-weight: 600;
}

.swal2-html-container {
    color: #cbd5e1 !important;
}

/* BUTTON */
.swal2-confirm {
    background: #dc3545 !important;
    border-radius: 10px !important;
    padding: 10px 20px !important;
    font-weight: 600;
}

.swal2-cancel {
    background: #334155 !important;
    border-radius: 10px !important;
    padding: 10px 20px !important;
}

/* MOBILE FIX */
@media (max-width: 576px) {
    .swal2-popup {
        width: 90% !important;
        font-size: 14px;
    }

    .swal2-title {
        font-size: 18px;
    }
}
.love-animation {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 50px;
    pointer-events: none;
    animation: lovePop 1s ease forwards;
    z-index: 9999;
}

@keyframes lovePop {
    0% {
        opacity: 0;
        transform: translate(-50%, -40%) scale(0.5);
    }
    30% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.2);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -70%) scale(1);
    }
}
.heart-filter {
    position: fixed;
    font-size: 22px;
    pointer-events: none;
    animation: floatHeart 1s linear forwards;
    z-index: 9999;
}

@keyframes floatHeart {
    0%{
        transform: translateY(0) scale(0.8);
        opacity: 1;
    }
    100%{
        transform: translateY(-120px) scale(1.3);
        opacity: 0;
    }
}
</style>
<div class="row mb-4 align-items-center">

    <div class="col-md-6">
        <h3 class="m-0 font-weight-bold text-white">
            {{ __('Transaksi') }}
        </h3>
    </div>

    <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end gap-2">

            <a href="{{ route('finance.transaksi.import.form') }}" class="btn btn-navy">
                <i class="fas fa-file-import me-1"></i> Import Transaksi
            </a>

            <a href="{{ route('converter.index') }}" class="btn btn-success px-4">
                <i class="fas fa-external-link-alt me-1"></i>
                Buka Converter
            </a>

        </div>
    </div>

</div>
@php
$totalTransaksi = $transaksis->total();
$totalPaid = $transaksis->where('status', 'paid')->count();
$totalUnpaid = $transaksis->where('status', 'unpaid')->count();
$nominalPaid = $transaksis->where('status', 'paid')->sum('total');
$nominalUnpaid = $transaksis->where('status', 'unpaid')->sum('total');
@endphp

    <!-- SUMMARY -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="summary-card text-center">
                <small>Total Transaksi</small>
                <div class="summary-number">{{ $totalTransaksi }}</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-card text-center">
                <small>Sudah Dibayar</small>
                <div class="summary-number">{{ $totalPaid }}</div>
                <div class="summary-amount">
                    Rp {{ number_format($nominalPaid,0,',','.') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-card text-center">
                <small>Belum Dibayar</small>
                <div class="summary-number">{{ $totalUnpaid }}</div>
                <div class="summary-amount text-warning">
                    Rp {{ number_format($nominalUnpaid,0,',','.') }}
                </div>
            </div>
        </div>

    </div>

    <!-- FILTER -->
    <div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label"><b>Customer</b></label>
                    <input type="text" name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Cari nama customer">
                </div>

                <div class="col-md-2">
                    <label class="form-label"><b>Dari</b></label>
                    <input type="date" name="from"
                           value="{{ request('from') }}"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label"><b>Sampai</b></label>
                    <input type="date" name="to"
                           value="{{ request('to') }}"
                           class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-navy px-4">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('finance.transaksi.index') }}" class="btn btn-outline-light px-4">
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

    <!-- TABLE -->
    <div class="card modern-card">
        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover mb-0 align-middle table-dark table-bordered  ">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Customer</th>
                            <th>Tanggal</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th class="text-center" width="200">Action</th>
                        </tr>
                    </thead>

                    <tbody>
@forelse($transaksis as $trx)
@php
$jatuhTempo = $trx->jatuh_tempo;
$isOverdue = $jatuhTempo && now()->greaterThan($jatuhTempo);
$canPay = $trx->status === 'unpaid' && (!$jatuhTempo || now()->lessThanOrEqualTo($jatuhTempo));
@endphp
                        <tr>
                            <td class="fw-semibold text-white">{{ $trx->kode_transaksi }}</td>
                            <td>{{ $trx->nama_customer }}</td>
                            <td>{{ $trx->tanggal->format('d M Y') }}</td>
                            <td>
                                @if($jatuhTempo)
                                    <span class="{{ $isOverdue && $trx->status === 'unpaid' ? 'text-danger fw-bold' : '' }}">
                                        {{ $jatuhTempo->format('d M Y') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-bold text-white">
                                Rp {{ number_format($trx->total,0,',','.') }}
                            </td>
                            <td>
                                @if($trx->status == 'paid')
                                    <span class="badge badge-paid"><i class="far fa-smile "></i>PAID</span>
                                @else
                                    <span class="badge badge-unpaid">UNPAID</span>
                                @endif
                            </td>
                            <td class="text-center text-white">
    <a href="{{ route('finance.transaksi.show', $trx->id) }}" 
       class="btn btn-sm btn-outline-primary ">
        <i class="fas fa-eye"></i>
    </a>

    @if(strtolower(trim($trx->status)) === 'unpaid')
        <a href="{{ route('finance.transaksi.payment.form', $trx->id) }}" 
           class="btn btn-sm btn-success btn-payment">
            <i class="fas fa-credit-card"></i>
        </a>
    @endif

    <form action="{{ route('finance.transaksi.destroy', $trx->id) }}" 
          method="POST" 
          class="d-inline form-delete">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</td>
                            </td>
                        </tr>
@empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-white">
                                Belum ada transaksi
                            </td>
                        </tr>
@endforelse
                    </tbody>
                </table>

            </div>
        </div>

        <div class="card-footer bg-dark">
            {{ $transaksis->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.form-delete').forEach(function(form) {

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
    title: "Yakin hapus transaksi ini?",
    text: "Data yang sudah dihapus tidak bisa dikembalikan.",
    icon: "warning",
    width: 420,
    padding: "2em",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#475569",
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal"
}).then((result) => {
    if (result.isConfirmed) {
        form.submit();
    }
});

        });

    });

});
// LOVE animation when payment clicked

</script>
@endsection