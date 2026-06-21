@extends('layouts-main.app')

@section('title', 'Payment Detail')
@section('page-title', 'Payment Detail')

@section('content')
<!-- Back Button & Actions -->
<div class="mb-3">
    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
    <a href="{{ route('payments.receipt', $payment->id) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-pdf"></i> Download Receipt
    </a>
    <button type="button" class="btn btn-danger btn-sm" 
        onclick="voidPayment({{ $payment->id }}, {{ $payment->amount }})">
        <i class="fas fa-ban"></i> Void Payment
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Payment Information -->
    <div class="col-lg-8">
        <!-- Main Payment Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-money-bill-wave"></i> Payment Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Payment ID:</strong></td>
                                <td><span class="badge bg-primary">#{{ $payment->id }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Date:</strong></td>
                                <td>
                                    <i class="far fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('l, d F Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td>
                                    @if($payment->method == 'cash')
                                        <span class="badge bg-success">💵 Cash (Tunai)</span>
                                    @else
                                        <span class="badge bg-info">🏦 Bank Transfer</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Reference No:</strong></td>
                                <td>
                                    @if($payment->reference)
                                        <code>{{ $payment->reference }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 bg-light rounded">
                            <small class="text-muted d-block mb-2">Payment Amount</small>
                            <h2 class="text-success mb-0">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </h2>
                        </div>
                    </div>
                </div>

                @if($payment->note)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-weight-bold">Note:</h6>
                        <div class="alert alert-light border">
                            {{ $payment->note }}
                        </div>
                    </div>
                </div>
                @endif

                <hr>

                <!-- Timestamps -->
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="far fa-clock"></i> Recorded: 
                            {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i:s') }}
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <i class="far fa-clock"></i> Last Updated: 
                            {{ \Carbon\Carbon::parse($payment->updated_at)->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Invoice -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice"></i> Related Invoice
                </h6>
                <a href="{{ route('beat-invoices.show', $payment->invoice->id) }}" 
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> View Full Invoice
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Customer Name:</strong></td>
                                <td>{{ $payment->invoice->customer_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>PPPoE:</strong></td>
                                <td><code>{{ $payment->invoice->pppoe }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Package:</strong></td>
                                <td>{{ $payment->invoice->package_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Billing Period:</strong></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $payment->invoice->period_month }}/{{ $payment->invoice->period_year }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td width="150"><strong>Invoice Amount:</strong></td>
                                <td class="text-end">Rp {{ number_format($payment->invoice->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Total Paid:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr class="{{ $outstanding > 0 ? 'table-danger' : 'table-light' }}">
                                <td><strong>Outstanding:</strong></td>
                                <td class="text-end">
                                    <strong class="{{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($outstanding, 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td class="text-end">
                                    @if($outstanding <= 0)
                                        <span class="badge bg-success">PAID</span>
                                    @elseif($totalPaid > 0)
                                        <span class="badge bg-warning">PARTIAL</span>
                                    @else
                                        <span class="badge bg-danger">UNPAID</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Progress Bar -->
                @php
                    $percentage = $payment->invoice->total_amount > 0 
                        ? ($totalPaid / $payment->invoice->total_amount) * 100 
                        : 0;
                @endphp
                <div class="progress mt-3" style="height: 25px;">
                    <div class="progress-bar {{ $percentage >= 100 ? 'bg-success' : 'bg-warning' }}" 
                        role="progressbar" 
                        style="width: {{ min(100, $percentage) }}%"
                        aria-valuenow="{{ $percentage }}" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                        {{ number_format($percentage, 1) }}% Paid
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History for This Invoice -->
        @if($payment->invoice->payments->count() > 1)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history"></i> Payment History (This Invoice)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->invoice->payments->sortByDesc('payment_date') as $p)
                                <tr class="{{ $p->id == $payment->id ? 'table-primary' : '' }}">
                                    <td>
                                        {{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}
                                        @if($p->id == $payment->id)
                                            <span class="badge bg-primary">Current</span>
                                        @endif
                                    </td>
                                    <td class="text-end">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($p->method == 'cash')
                                            <span class="badge bg-success">Cash</span>
                                        @else
                                            <span class="badge bg-info">Bank</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $p->reference ?? '-' }}</small></td>
                                    <td>
                                        @if($p->id == $payment->id)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <a href="{{ route('payments.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total:</th>
                                <th class="text-end">Rp {{ number_format($totalPaid, 0, ',', '.') }}</th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('payments.receipt', $payment->id) }}" class="btn btn-success">
                        <i class="fas fa-file-pdf"></i> Download Receipt
                    </a>
                    <a href="{{ route('beat-invoices.show', $payment->invoice->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice"></i> View Full Invoice
                    </a>
                    @if($journalEntry)
                    <a href="{{ route('journal-entries.show', $journalEntry->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-book"></i> View Journal Entry
                    </a>
                    @endif
                    <button type="button" class="btn btn-outline-danger" 
                        onclick="voidPayment({{ $payment->id }}, {{ $payment->amount }})">
                        <i class="fas fa-ban"></i> Void This Payment
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Journal Entry -->
        @if($journalEntry)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-book"></i> Journal Entry
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Date:</strong> {{ $journalEntry->journal_date }}</p>
                <p class="mb-2"><strong>Description:</strong></p>
                <p class="small text-muted">{{ $journalEntry->description }}</p>

                <hr>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journalEntry->lines as $line)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $line->account_code }}</small><br>
                                        <strong>{{ $line->account_name }}</strong>
                                    </td>
                                    <td class="text-end">
                                        @if($line->debit > 0)
                                            <span class="text-success">
                                                Rp {{ number_format($line->debit, 0, ',', '.') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($line->credit > 0)
                                            <span class="text-danger">
                                                Rp {{ number_format($line->credit, 0, ',', '.') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total:</th>
                                <th class="text-end">{{ number_format($journalEntry->total_debit, 0, ',', '.') }}</th>
                                <th class="text-end">{{ number_format($journalEntry->total_credit, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-center mt-2">
                    @if(abs($journalEntry->total_debit - $journalEntry->total_credit) < 0.01)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> Balanced
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-triangle"></i> Unbalanced
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="card shadow mb-4">
            <div class="card-body text-center text-muted">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p class="mb-0">No journal entry found.</p>
            </div>
        </div>
        @endif

        <!-- Payment Impact -->
        <div class="card shadow mb-4 border-left-info">
            <div class="card-body">
                <h6 class="font-weight-bold text-info">
                    <i class="fas fa-lightbulb"></i> Accounting Impact
                </h6>
                <p class="mb-2 small">This payment transaction:</p>
                <ul class="mb-0 small">
                    <li>
                        <strong>Increases</strong> cash/bank account by 
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </li>
                    <li>
                        <strong>Decreases</strong> accounts receivable by 
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </li>
                    <li>
                        <strong>Invoice status:</strong> 
                        @if($outstanding <= 0)
                            Changed to PAID ✅
                        @elseif($totalPaid > 0)
                            Changed to PARTIAL 🟡
                        @endif
                    </li>
                </ul>
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