@extends('layouts-main.app')

@section('title', 'Payments')
@section('page-title', 'Payment Records')

@section('content')
<div class="row">
        <!-- Summary Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                            Cash Payments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['cash_payments'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-coins fa-2x text-gray-300"></i>
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
                            Bank Transfers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($stats['bank_payments'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-university fa-2x text-gray-300"></i>
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
                            This Month
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
</div>

<!-- Filter & Action Bar -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Filter & Actions</h6>
        <div>
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Payment
            </a>
            <a href="{{ route('payments.export', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export CSV
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
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
                <label class="form-label small">Month</label>
                <select name="month" class="form-control form-control-sm">
                    <option value="">All</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
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
                <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>

            <div class="col-md-4">
                <label class="form-label small">Payment Method</label>
                <select name="method" class="form-control form-control-sm">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>💵 Cash</option>
                    <option value="bank" {{ request('method') == 'bank' ? 'selected' : '' }}>🏦 Bank</option>
                </select>
            </div>

            <div class="col-md-8">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" 
                    value="{{ request('search') }}"
                    placeholder="Search by customer, PPPoE, or reference...">
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Payment Records</h6>
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

        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Invoice</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>
                                <i class="far fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </td>
                            <td>
                                <strong>{{ $payment->invoice->customer_name }}</strong><br>
                                <small class="text-muted">{{ $payment->invoice->pppoe }}</small>
                            </td>
                            <td>
                                <small>Period: {{ $payment->invoice->period_month }}/{{ $payment->invoice->period_year }}</small><br>
                                @php
                                    $totalPaid = $payment->invoice->payments->sum('amount');
                                    $outstanding = max(0, $payment->invoice->total_amount - $totalPaid);
                                @endphp
                                @if($outstanding <= 0)
                                    <span class="badge bg-success">PAID</span>
                                @elseif($totalPaid > 0)
                                    <span class="badge bg-warning">PARTIAL</span>
                                @else
                                    <span class="badge bg-danger">UNPAID</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($payment->method == 'cash')
                                    <span class="badge bg-success">💵 Cash</span>
                                @else
                                    <span class="badge bg-info">🏦 Bank</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $payment->reference ?? '-' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('payments.show', $payment->id) }}" 
                                    class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('payments.receipt', $payment->id) }}" 
                                    class="btn btn-success btn-sm" title="Download Receipt">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="voidPayment({{ $payment->id }}, {{ $payment->amount }})" 
                                    title="Void">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No payment records found.</p>
                                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add First Payment
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($payments->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total (This Page):</th>
                            <th class="text-end">
                                Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} 
                of {{ $payments->total() }} entries
            </div>
            <div>
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function voidPayment(id, amount) {
    Swal.fire({
        title: 'Void Payment?',
        html: `Are you sure you want to void this payment?<br><br><strong>Amount: Rp ${new Intl.NumberFormat('id-ID').format(amount)}</strong><br><br>This will:
        <ul class="text-start">
            <li>Delete the payment record</li>
            <li>Delete the journal entry</li>
            <li>Update invoice status</li>
        </ul>
        This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Yes, void it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/payments/${id}`;
            
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