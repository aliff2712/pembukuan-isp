@extends('layouts-main.app')

@section('title', 'Expense Detail')
@section('page-title', 'Expense Detail')

@section('content')
<style>
    .table-custom-dark {
    background-color: #1e293b;
    color: #f8f9fa;
}
.table-custom-dark thead {
    background-color: #374151;
}
.text-primary-white{
    color:# ffffff !important;
}
</style>
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Expenses
    </a>
    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit"></i> Edit
    </a>
    <button type="button" class="btn btn-danger btn-sm" onclick="deleteExpense({{ $expense->id }}, {{ $expense->amount }})">
        <i class="fas fa-trash"></i> Delete
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Main Info Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-receipt"></i> Expense Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered table-dark ">
                    <tr>
                        <td width="180"><strong>Expense ID:</strong></td>
                        <td>{{ $expense->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Expense Date:</strong></td>
                        <td>
                            <i class="far fa-calendar-alt"></i>
                            {{ \Carbon\Carbon::parse($expense->expense_date)->format('l, d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Amount:</strong></td>
                        <td>
                            <h4 class="text-danger mb-0">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </h4>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
            <table class="table table-sm table-bordered table-dark">
                    <tr>
                        <td width="180"><strong>Expense Account:</strong></td>
                        <td>
                            <span class="badge bg-danger">{{ $expense->expenseAccount->account_code }}</span><br>
                            <strong>{{ $expense->expenseAccount->account_name }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Paid From:</strong></td>
                        <td>
                            <span class="badge bg-primary">{{ $expense->cashAccount->account_code }}</span><br>
                            <strong>{{ $expense->cashAccount->account_name }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <hr>

        <!-- Description -->
        <div class="row">
            <div class="col-12 ">
                <h6 class="font-weight-bold">Description:</h6>
                <div class="alert alert-light border bg-dark ">
                  <h6 class="text-white">  {{ $expense->description }}</h6>
                </div>
            </div>
        </div>

        <hr>

        <!-- Timestamps -->
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="far fa-clock"></i> Created: 
                    {{ \Carbon\Carbon::parse($expense->created_at)->format('d M Y H:i:s') }}
                </small>
            </div>
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="far fa-clock"></i> Last Updated: 
                    {{ \Carbon\Carbon::parse($expense->updated_at)->format('d M Y H:i:s') }}
                    ({{ \Carbon\Carbon::parse($expense->updated_at)->diffForHumans() }})
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Related Journal Entry -->
@if($journalEntry)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-book"></i> Related Journal Entry
        </h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p class="mb-1"><strong>Journal Date:</strong> {{ $journalEntry->journal_date }}</p>
                <p class="mb-1"><strong>Description:</strong> {{ $journalEntry->description }}</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-1"><strong>Reference No:</strong> {{ $journalEntry->reference_no ?? '-' }}</p>
                <a href="{{ route('journal-entries.show', $journalEntry->id) }}" 
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> View Journal Entry
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-dark" >
                <thead class="table-light table-dark">
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journalEntry->lines as $line)
                        <tr>
                            <td>
                                <span class="badge {{ $line->debit > 0 ? 'bg-danger' : 'bg-primary' }}">
                                    {{ $line->account_code }}
                                </span>
                            </td>
                            <td>{{ $line->account_name }}</td>
                            <td class="text-end">
                                {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end">
                                {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light table-dark">
                    <tr>
                        <th colspan="2" class="text-end">Total:</th>
                        <th class="text-end">Rp {{ number_format($journalEntry->total_debit, 0, ',', '.') }}</th>
                        <th class="text-end">Rp {{ number_format($journalEntry->total_credit, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-center">
                            @if(abs($journalEntry->total_debit - $journalEntry->total_credit) < 0.01)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Balanced
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Unbalanced
                                </span>
                            @endif
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="card shadow mb-4">
    <div class="card-body text-center text-muted">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <p>No journal entry found for this expense.</p>
        <small>Journal entries are created automatically when expense is saved.</small>
    </div>
</div>
@endif

<!-- Info Box -->
<div class="card shadow mb-4 border-left-info">
    <div class="card-body">
        <h6 class="font-weight-bold text-info">
            <i class="fas fa-lightbulb"></i> Accounting Impact
        </h6>
        <p class="mb-2 small">
            This expense transaction affects your financial statements as follows:
        </p>
        <ul class="mb-0 small">
            <li>
                <strong>Income Statement:</strong> Increases {{ $expense->expenseAccount->account_name }} 
                by Rp {{ number_format($expense->amount, 0, ',', '.') }}
            </li>
            <li>
                <strong>Balance Sheet:</strong> Decreases {{ $expense->cashAccount->account_name }} 
                by Rp {{ number_format($expense->amount, 0, ',', '.') }}
            </li>
            <li>
                <strong>Cash Flow:</strong> Outflow of Rp {{ number_format($expense->amount, 0, ',', '.') }} 
                from {{ $expense->cashAccount->account_name }}
            </li>
        </ul>
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
</script>
@endpush