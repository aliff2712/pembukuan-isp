@extends('layouts-main.app')

@section('title', 'Voucher Sale Detail')
@section('page-title', 'Voucher Sale Detail')

@section('content')

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Inter', sans-serif;
}


/* optional: biar header tetap tegas */
.table thead th {
    color: #000 !important;
    font-weight: 600;
}


/* ===== NAVY THEME ===== */
.navy-card {
    background: #0f172a;
    color: #e2e8f0;
    border: none;
}

.navy-card .card-header {
    background: #1e293b;
    color: #ffffff;
    border-bottom: 1px solid #334155;
}

.navy-card table td,
.navy-card table th {
    color: #e2e8f0;
}

.navy-card .table-light {
    background: #1e293b !important;
    color: #ffffff !important;
}

.navy-card hr {
    border-color: #334155;
}

.navy-card small {
    color: #cbd5e1;
}
.text-primary-white{
    color: #ffffff;
}
</style>

<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('voucher-sales.index') }}" 
       class="btn btn-outline-light btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<!-- MAIN INFO CARD -->
<div class="card shadow mb-4 navy-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-semibold">
            <i class="fas fa-ticket-alt me-2"></i> Voucher Sale Information
        </h6>
        <span class="badge bg-secondary">{{ $sale->source }}</span>
    </div>

    <div class="card-body">

        <div class="row g-4">

            <div class="col-md-6">
                <table class="table table-bordered mb-0 table-dark">
                    <tr>
                        <td width="160"><strong>ID</strong></td>
                        <td>{{ $sale->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Sale Date</strong></td>
                        <td>
                            <i class="far fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('l, d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Source</strong></td>
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ $sale->source }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-bordered mb-0 table-dark">
                    <tr>
                        <td width="200"><strong>Total Transactions</strong></td>
                        <td>
                            <span class="badge bg-success">
                                {{ number_format($sale->total_transactions) }} transactions
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total Amount</strong></td>
                        <td>
                            <h4 class="mb-0 text-warning fw-semibold">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            </h4>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Average per Transaction</strong></td>
                        <td>
                            Rp {{ number_format($sale->total_transactions > 0 ? $sale->total_amount / $sale->total_transactions : 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        <hr class="my-4">

        <div class="row">
            <div class="col-md-6">
                <small>
                    <i class="far fa-clock me-1"></i>
                    Created:
                    {{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y H:i:s') }}
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small>
                    <i class="far fa-clock me-1"></i>
                    Updated:
                    {{ \Carbon\Carbon::parse($sale->updated_at)->format('d M Y H:i:s') }}
                    ({{ \Carbon\Carbon::parse($sale->updated_at)->diffForHumans() }})
                </small>
            </div>
        </div>

    </div>
</div>

<!-- RELATED JOURNAL ENTRY -->
@if($journalEntry)
<div class="card shadow mb-4 navy-card">
    <div class="card-header">
        <h6 class="m-0 fw-semibold">
            <i class="fas fa-book me-2"></i> Related Journal Entry
        </h6>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-6">
                <p class="mb-1"><strong>Journal Date:</strong> {{ $journalEntry->journal_date }}</p>
                <p class="mb-1"><strong>Description:</strong> {{ $journalEntry->description }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1"><strong>Reference No:</strong> {{ $journalEntry->reference_no ?? '-' }}</p>
                <a href="{{ route('journal-entries.show', $journalEntry->id) }}"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-eye me-1"></i> View Journal
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-dark ">
                <thead class="table-dark ">
                    <tr>
                        <th >Account Code</th>
                        <th>Account Name</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = \DB::table('journal_lines')
                            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'journal_lines.coa_id')
                            ->where('journal_entry_id', $journalEntry->id)
                            ->select('journal_lines.*', 'coa.account_code', 'coa.account_name')
                            ->get();
                    @endphp

                    @foreach($lines as $line)
                    <tr>
                        <td>{{ $line->account_code ?? $line->coa_id }}</td>
                        <td>{{ $line->account_name ?? '-' }}</td>
                        <td class="text-end">
                            {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end">
                            {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="2" class="text-end">Total:</th>
                        <th class="text-end">
                            Rp {{ number_format($journalEntry->total_debit, 0, ',', '.') }}
                        </th>
                        <th class="text-end">
                            Rp {{ number_format($journalEntry->total_credit, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>
@endif

<!-- ACTION CARD -->
<div class="card shadow mb-4 navy-card">
    <div class="card-header">
        <h6 class="m-0 fw-semibold">
            <i class="fas fa-cog me-2"></i> Actions
        </h6>
    </div>

    <div class="card-body d-flex flex-wrap gap-2">

        <button type="button"
                class="btn btn-danger"
                onclick="voidSale({{ $sale->id }}, '{{ $sale->sale_date }}')">
            <i class="fas fa-trash me-1"></i> hapus penjualan
        </button>

        <a href="{{ route('voucher-sales.index') }}"
           class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>

    </div>
</div>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function voidSale(id, date) {
    Swal.fire({
        title: 'HAPUS DATA?',
        html: `Apakah anda yakin ingin menghapus data ini for date: <strong>${date}</strong>?<br><br>This action cannot be undone and will delete the associated journal entry.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Yes, void it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/voucher-sales/${id}`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';

            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush