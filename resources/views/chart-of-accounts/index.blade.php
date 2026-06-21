@extends('layouts-main.app')
@section('title', __('Chart of Accounts'))
@section('page-title', __('Chart of Accounts'))
@section('content')
<link rel="stylesheet" href="{{ asset('assets/COA.css') }}">
<div class="container-fluid">

    <!-- Success Alert -->
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Error!</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
        <h3 class="m-0 font-weight-bold text-white">{{ __('Chart of Accounts') }}</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('Add Account') }}
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">

<!-- Total Accounts -->
<div class="col-xl-2 col-md-4 col-6">
    <div class="card summary-card border-primary h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="summary-label text-primary">
                    {{ __('Total Accounts') }}
                </div>
                <div class="summary-value">
                    {{ number_format($stats['total'], 0, ',', '.') }}
                </div>
            </div>
            <div class="summary-icon bg-primary-soft">
                <i class="fas fa-list text-primary"></i>
            </div>
        </div>
    </div>
</div>

<!-- Asset -->
<div class="col-xl-2 col-md-4 col-6">
    <div class="card summary-card border-info h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="summary-label text-info">Asset</div>
                <div class="summary-value">
                    {{ $stats['asset_count'] }}
                </div>
            </div>
            <div class="summary-icon bg-info-soft">
                <i class="fas fa-coins text-info"></i>
            </div>
        </div>
    </div>
</div>

<!-- Revenue -->
<div class="col-xl-2 col-md-4 col-6">
    <div class="card summary-card border-success h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="summary-label text-success">Revenue</div>
                <div class="summary-value">
                    {{ $stats['revenue_count'] }}
                </div>
            </div>
            <div class="summary-icon bg-success-soft">
                <i class="fas fa-arrow-up text-success"></i>
            </div>
        </div>
    </div>
</div>

<!-- Expense -->
<div class="col-xl-2 col-md-4 col-6">
    <div class="card summary-card border-danger h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="summary-label text-danger">Expense</div>
                <div class="summary-value">
                    {{ $stats['expense_count'] }}
                </div>
            </div>
            <div class="summary-icon bg-danger-soft">
                <i class="fas fa-arrow-down text-danger"></i>
            </div>
        </div>
    </div>
</div>

<!-- Liability -->
<div class="col-xl-2 col-md-4 col-6">
    <div class="card summary-card border-warning h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="summary-label text-warning">Liability</div>
                <div class="summary-value">
                    {{ $stats['liability_count'] }}
                </div>
            </div>
            <div class="summary-icon bg-warning-soft">
                <i class="fas fa-file-invoice-dollar text-warning"></i>
            </div>
        </div>
    </div>
</div>

<!-- Equity -->
<div class="col-xl-2 col-md-4 col-6">
    <div class="card summary-card border-primary h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="summary-label text-primary">Equity</div>
                <div class="summary-value">
                    {{ $stats['equity_count'] }}
                </div>
            </div>
            <div class="summary-icon bg-primary-soft">
                <i class="fas fa-piggy-bank text-primary"></i>
            </div>
        </div>
    </div>
</div>

</div>


    <!-- Filter Card -->
<div class="card filter-card mb-4 border-0">
    <div class="card-header bg-navy text-white d-flex align-items-center justify-content-between">
        <span class="fw-semibold">
            <i class="fas fa-filter me-2"></i> {{ __('Filter & Search') }}
        </span>
    </div>

    <div class="card-body ">
        <form action="{{ route('chart-of-accounts.index') }}" method="GET">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        {{ __('Search Code/Name') }}
                    </label>
                    <input type="text"
                           name="search"
                           class="form-control filter-input"
                           placeholder="Enter code or name..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        {{ __('Account Type') }}
                    </label>
                    <select name="account_type" class="form-select filter-input">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="asset" @selected(request('account_type')=='asset')>Asset</option>
                        <option value="revenue" @selected(request('account_type')=='revenue')>Revenue</option>
                        <option value="expense" @selected(request('account_type')=='expense')>Expense</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        {{ __('Cash Account') }}
                    </label>
                    <select name="is_cash" class="form-select filter-input">
                        <option value="">{{ __('All') }}</option>
                        <option value="1" @selected(request('is_cash')=='1')>Yes</option>
                        <option value="0" @selected(request('is_cash')=='0')>No</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">

                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('chart-of-accounts.index') }}"
                       class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                       style="width:38px;height:38px;"
                       title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>

                </div>

            </div>
        </form>
    </div>
</div>
    <!-- Accounts Table -->
    <div class="card shadow " >
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary-white">{{ __('Chart of Accounts') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive rounded-3 overflow-hidden">
                <table class="table table-hover table-striped table-dark mb-0">
                    <thead >
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th class="text-center">{{ __('Cash') }}</th>
                            <th class="text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>
                                    <strong>{{ $account->account_code }}</strong>
                                </td>
                                <td>{{ $account->account_name }}</td>
                                <td>
                                    @php
                                        $typeColors = [
                                            'asset' => 'info',
                                            'liability' => 'warning',
                                            'equity' => 'success',
                                            'revenue' => 'success',
                                            'expense' => 'danger',
                                        ];
                                        $typeLabels = [
                                            'asset' => 'Asset (Aset)',
                                            'liability' => 'Liability (Kewajiban)',
                                            'equity' => 'Equity (Modal)',
                                            'revenue' => 'Revenue (Pendapatan)',
                                            'expense' => 'Expense (Beban)',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $typeColors[$account->account_type] ?? 'secondary' }}">
                                        {{ $typeLabels[$account->account_type] ?? $account->account_type }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($account->is_cash)
                                        <span class="badge bg-success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('No') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
    <div class="d-flex justify-content-center gap-2 action-buttons">

        <a href="{{ route('chart-of-accounts.show', $account->id) }}"
           class="btn btn-sm btn-outline-primary"
           title="View">
            <i class="fas fa-eye"></i>
        </a>

        <a href="{{ route('chart-of-accounts.edit', $account->id) }}"
           class="btn btn-sm btn-outline-warning"
           title="Edit">
            <i class="fas fa-edit"></i>
        </a>

        <button type="button"
        class="btn btn-sm btn-outline-danger"
        onclick="deleteAccount({{ $account->id }}, '{{ $account->account_name }}')">
    <i class="fas fa-trash"></i>
</button>

    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3 d-block"></i>
                                    {{ __('No accounts found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                {{ $accounts->links() }}
            </nav>
        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

function deleteAccount(id, name) {

Swal.fire({
    title: "Hapus Account?",
    html: `Apakah kamu yakin ingin menghapus account berikut?<br><br>
           <strong>${name}</strong><br><br>
           <small>Data yang sudah dihapus tidak dapat dikembalikan.</small>`,
    icon: "warning",
    width: window.innerWidth < 576 ? '85%' : '420px',
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#475569",
    background: "#1e293b",
    color: "#e2e8f0"
}).then((result) => {

    if (result.isConfirmed) {

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/chart-of-accounts/${id}`;

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
@if(session('success'))
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: "{{ session('success') }}",
    width: window.innerWidth < 576 ? '85%' : '420px',
    background: "#1e293b",
    color: "#e2e8f0",
    confirmButtonColor: "#2563eb"
});
@endif

@if(session('error'))
Swal.fire({
    icon: 'error',
    title: 'Delete Failed',
    text: "{{ session('error') }}",
    width: window.innerWidth < 576 ? '85%' : '420px',
    background: "#1e293b",
    color: "#e2e8f0",
    confirmButtonColor: "#dc3545"
});
@endif

</script>
@endsection