@extends('layouts-main.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses - Pengeluaran Operasional')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/expenses.css') }}">
<div class="row">
    <!-- Summary Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Total Pengeluaran
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                            Bulan ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['this_month'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                            Jumlah
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($stats['total_expenses']) }} items
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
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
                            Hari ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['today'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Bar -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary-white">Aksi</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Catat Pengeluaran
            </a>

            <a href="#"
               class="btn btn-success btn-sm"
               onclick="startExport(event)">
                <i class="fas fa-file-excel"></i> Export CSV
            </a>
        </div>
    </div>
</div>


<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary-white">Filter</h6>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3">

            <div class="col-md-3">
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small">Bulan</label>
                <select name="month" class="form-control form-control-sm">
                    <option value="">All</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small">Year</label>
                <select name="year" class="form-control form-control-sm">
                    <option value="">All</option>
                    @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>

            <div class="col-md-6">
                <label class="form-label small">Jenis Pengeluaran</label>
                <select name="expense_account_id" class="form-control form-control-sm">
                    <option value="">Semua jenis pengeluaran</option>
                    @foreach($expenseAccounts as $account)
                        <option value="{{ $account->id }}"
                            {{ request('expense_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_code }} - {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small">Cash/Bank Account</label>
                <select name="cash_account_id" class="form-control form-control-sm">
                    <option value="">All Cash/Bank Accounts</option>
                    @foreach($cashAccounts as $account)
                        <option value="{{ $account->id }}"
                            {{ request('cash_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_code }} - {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </form>
    </div>
</div>
<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary-white">Riwayat pengeluaran</h6>
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

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-dark"  width="100%" cellspacing="0">
                <thead >
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Jenis pengeluaran</th>
                        <th>Dibayar dari</th>
                        <th>Jumlah Rp</th>
                        <th>Deskripsi</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->id }}</td>
                            <td>
                                <i class="far fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $expense->expenseAccount->account_code }}</small><br>
                                <strong>{{ $expense->expenseAccount->account_name }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $expense->cashAccount->account_code }}</small><br>
                                {{ $expense->cashAccount->account_name }}
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">Rp {{ number_format($expense->amount, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <small>{{ Str::limit($expense->description, 50) }}</small>
                            </td>
                            <td>
                                <a href="{{ route('expenses.show', $expense->id) }}" 
                                    class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('expenses.edit', $expense->id) }}" 
                                    class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="deleteExpense({{ $expense->id }}, {{ $expense->amount }})" 
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No expense records found.</p>
                                <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add First Expense
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($expenses->count() > 0)
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="4" class="text-end">Total (This Page):</th>
                            <th class="text-end">
                                Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $expenses->firstItem() ?? 0 }} to {{ $expenses->lastItem() ?? 0 }} 
                of {{ $expenses->total() }} entries
            </div>
            <div>
                {{ $expenses->links() }}
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
function deleteExpense(id, amount) {
    Swal.fire({
        title: 'Delete Expense?',
        html: `Are you sure you want to delete this expense?<br><br><strong>Amount: Rp ${new Intl.NumberFormat('id-ID').format(amount)}</strong><br><br>This will also delete the related journal entry.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/expenses/${id}`;
            
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
    window.location.href = "{{ route('expenses.export') }}";

    setTimeout(()=>{
        modal.hide();
    },1200);

},2000);

}

</script>
@endpush