@extends('layouts-main.app')

@section('title', 'Voucher Sales')
@section('page-title', 'Voucher Sales - Mikhmon')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/voucher.css') }}">
<div class="row">
    <!-- Summary Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Hari penjualan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($stats['total_days'], 0) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Transaksi
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($stats['total_transactions'], 0) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total pendapatan (keseluruhan)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Rata-rata Pendapatan/Hari
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['average_per_day'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!---actions---->
<div class="card shadow mb-4">
    <div class="card-header py-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <span class="fw-bold text-primary-white">
                Actions
            </span>

            <div class="d-flex gap-2 flex-wrap">

                <a href="{{ route('voucher-sales.import') }}"
                   class="btn btn-warning btn-sm">
                    <i class="fas fa-download"></i> Import
                </a>

                <a href="#"
   class="btn btn-success btn-sm"
   onclick="startExport(event)">
    <i class="fas fa-file-excel"></i> Export
</a>

            </div>

        </div>

</div>
</div>
<div class="card shadow mb-4">

<div class="card-header py-3 ">
    <h6 class="m-0 font-weight-bold text-primary-white">
        Filter Data
    </h6>
</div>

<div class="card-body filter-card">
    <form method="GET"
          action="{{ route('voucher-sales.index') }}"
          class="row g-3">

        <div class="col-12 col-md-3">
            <label class="form-label small">Date From</label>
            <input type="date"
                   name="date_from"
                   class="form-control form-control-sm"
                   value="{{ request('date_from') }}">
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small">Date To</label>
            <input type="date"
                   name="date_to"
                   class="form-control form-control-sm"
                   value="{{ request('date_to') }}">
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small">Month</label>
            <select name="month"
                    class="form-control form-control-sm">
                <option value="">All</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"
                        {{ request('month') == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small">Year</label>
            <select name="year"
                    class="form-control form-control-sm">
                <option value="">All</option>
                @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}"
                        {{ request('year') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-12 col-md-2 d-flex align-items-md-end gap-2">

            <button type="submit"
                    class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>

            <a href="{{ route('voucher-sales.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-redo"></i> Reset
            </a>

        </div>

    </form>
</div>

</div>
<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary-white">Voucher Sales Data</h6>
    </div>
    <div class="card-body p-0">
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

        <div class="table-responsive rounded-3 overflow-hidden">
            <table class="mb-0 table table-bordered table-hover table-dark" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Sale Date</th>
                        <th>Total Transactions</th>
                        <th>Total Amount</th>
                        <th>Source</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
@forelse($sales as $sale)
<tr>
    <td data-label="ID">{{ $sale->id }}</td>

    <td data-label="Sale Date">
        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
    </td>

    <td data-label="Transactions" class="text-center">
        <span class="badge bg-info">
            {{ number_format($sale->total_transactions) }}
        </span>
    </td>

    <td data-label="Total Amount" class="text-end">
        <strong>
            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
        </strong>
    </td>

    <td data-label="Source">
        <span class="badge bg-secondary">
            {{ $sale->source }}
        </span>
    </td>

    <td data-label="Updated">
        <small>
            {{ \Carbon\Carbon::parse($sale->updated_at)->format('d/m/Y H:i') }}
        </small>
    </td>

    <td data-label="Actions">
        <div class="d-flex gap-1 justify-content-end">
            <a href="{{ route('voucher-sales.show', $sale->id) }}" 
               class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i>
            </a>

            <button type="button"
                class="btn btn-danger btn-sm"
                onclick="voidSale({{ $sale->id }}, '{{ $sale->sale_date }}')">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-4">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <p>No voucher sales data found.</p>
    </td>
</tr>
@endforelse
</tbody >
                @if($sales->count() > 0)
                    <tfoot class="table-dark">
                        <tr>
                            {{-- FIX: colspan disesuaikan dengan jumlah kolom yang benar --}}
                            <th colspan="2" class="text-end">Total (This Page):</th>
                            <th class="text-center">
                                {{ number_format($sales->sum('total_transactions')) }}
                            </th>
                            <th class="text-end">
                                Rp {{ number_format($sales->sum('total_amount'), 0, ',', '.') }}
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination -->
        {{-- FIX: withQueryString() sudah di-handle di controller, links() otomatis membawa filter --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} 
                of {{ $sales->total() }} entries
            </div>
            <div>
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-info-circle"></i> Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold">About Voucher Sales</h6>
                <p class="">
                    Data penjualan voucher diimpor otomatis dari sistem Mikhmon. 
                    Setiap hari akan ada 1 record yang merangkum total transaksi dan total penjualan.
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Last Import</h6>
                <p class="">
                    <i class="far fa-clock"></i> 
                    {{ $stats['last_import'] ? \Carbon\Carbon::parse($stats['last_import'])->diffForHumans() : 'Never' }}
                </p>
                <p class="text-muted small">
                    <i class="fas fa-coins"></i> This Month Total: 
                    <strong>Rp {{ number_format($stats['this_month_total'], 0, ',', '.') }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">

            <h5 class="mb-3">
                <i class="fas fa-file-export me-2"></i> Exporting Data
            </h5>

            <div class="d-flex justify-content-center mb-3">
                <div class="spinner-border" role="status"></div>
            </div>

            <div class="progress mb-2" style="height:20px;">
    <div id="exportProgress"
         class="progress-bar progress-bar-striped progress-bar-animated"
         style="width:0%; background:#3b82f6;">
         0%
    </div>
</div>

            <div id="progressText">Menyiapkan export...</div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function startExport(e){

e.preventDefault();

const modal = new bootstrap.Modal(document.getElementById('exportModal'));
modal.show();

let progress = 0;

const bar = document.getElementById("exportProgress");
const text = document.getElementById("progressText");

function animate(){

    if(progress < 90){

        progress += Math.random() * 7;

        bar.style.width = progress + "%";
        bar.innerText = Math.floor(progress) + "%";

        requestAnimationFrame(animate);

    }

}

animate();

setTimeout(function(){

    bar.style.width = "100%";
    bar.innerText = "100%";
    text.innerText = "Export selesai";

    // DOWNLOAD FILE
    window.location.href = "{{ route('voucher-sales.export') }}";

    setTimeout(()=>{
        modal.hide();
    },1200);

},2000);

}

function voidSale(id, date) {
    Swal.fire({
        title: 'Hapus Data Ini?',
        html: `Anda akan menghapus data penjualan voucher tanggal <strong>${date}</strong>.`,
        icon: 'warning',
        width: 420,
        padding: '1.8em',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Ya,Hapus Data Ini',
        cancelButtonText: 'Cancel',
        reverseButtons: true
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