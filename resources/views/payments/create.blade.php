@extends('layouts-main.app')

@section('title', 'Add Payment')
@section('page-title', 'Record New Payment')

@section('content')
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
</div>

<!-- Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-money-bill-wave"></i> Payment Information
        </h6>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Validation Error!</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
            @csrf

            <div class="row">
                <!-- Select Invoice -->
                <div class="col-12 mb-4">
                    <label for="invoice_id" class="form-label">
                        <i class="fas fa-file-invoice"></i> Select Invoice <span class="text-danger">*</span>
                    </label>
                    <select class="form-control form-control-lg @error('invoice_id') is-invalid @enderror" 
                        id="invoice_id" 
                        name="invoice_id" 
                        required>
                        <option value="">-- Pilih Invoice yang Belum Lunas --</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}" 
                                data-customer="{{ $invoice->customer_name }}"
                                data-pppoe="{{ $invoice->pppoe }}"
                                data-package="{{ $invoice->package_name }}"
                                data-period="{{ $invoice->period_month }}/{{ $invoice->period_year }}"
                                data-total="{{ $invoice->total_amount }}"
                                data-paid="{{ $invoice->paid_amount }}"
                                data-outstanding="{{ $invoice->outstanding }}"
                                {{ old('invoice_id', $selectedInvoiceId) == $invoice->id ? 'selected' : '' }}>
                                [{{ $invoice->pppoe }}] {{ $invoice->customer_name }} - 
                                {{ $invoice->period_month }}/{{ $invoice->period_year }} - 
                                Outstanding: Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('invoice_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($invoices->isEmpty())
                        <div class="form-text text-danger">
                            <i class="fas fa-info-circle"></i> Tidak ada invoice yang perlu dibayar. Semua invoice sudah lunas.
                        </div>
                    @endif
                </div>

                <!-- Invoice Detail Card (Hidden by default) -->
                <div class="col-12 mb-4" id="invoiceDetailCard" style="display: none;">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-info mb-3">
                                <i class="fas fa-info-circle"></i> Invoice Details
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="120"><strong>Customer:</strong></td>
                                            <td id="detail-customer">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>PPPoE:</strong></td>
                                            <td id="detail-pppoe">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Package:</strong></td>
                                            <td id="detail-package">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Period:</strong></td>
                                            <td id="detail-period">-</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="150"><strong>Total Invoice:</strong></td>
                                            <td class="text-end"><span id="detail-total">Rp 0</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Already Paid:</strong></td>
                                            <td class="text-end text-success"><span id="detail-paid">Rp 0</span></td>
                                        </tr>
                                        <tr class="border-top">
                                            <td><strong>Outstanding:</strong></td>
                                            <td class="text-end">
                                                <h5 class="mb-0 text-danger" id="detail-outstanding">Rp 0</h5>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Date -->
                <div class="col-md-6 mb-3">
                    <label for="payment_date" class="form-label">
                        <i class="far fa-calendar"></i> Payment Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" 
                        class="form-control @error('payment_date') is-invalid @enderror" 
                        id="payment_date" 
                        name="payment_date" 
                        value="{{ old('payment_date', date('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}"
                        required>
                    @error('payment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div class="col-md-6 mb-3">
                    <label for="method" class="form-label">
                        <i class="fas fa-credit-card"></i> Payment Method <span class="text-danger">*</span>
                    </label>
                    <select class="form-control @error('method') is-invalid @enderror" 
                        id="method" 
                        name="method" 
                        required>
                        <option value="">-- Select Method --</option>
                        <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>
                            💵 Cash (Tunai)
                        </option>
                        <option value="bank" {{ old('method') == 'bank' ? 'selected' : '' }}>
                            🏦 Bank Transfer
                        </option>
                    </select>
                    @error('method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Cash/Bank Account -->
                <div class="col-md-6 mb-3">
                    <label for="cash_account_id" class="form-label">
                        <i class="fas fa-university"></i> Deposit To (Account) <span class="text-danger">*</span>
                    </label>
                    <select class="form-control @error('cash_account_id') is-invalid @enderror" 
                        id="cash_account_id" 
                        name="cash_account_id" 
                        required>
                        <option value="">-- Select Cash/Bank Account --</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('cash_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('cash_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">
                        <i class="fas fa-money-bill-wave"></i> Payment Amount <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" 
                            class="form-control @error('amount') is-invalid @enderror" 
                            id="amount" 
                            name="amount" 
                            value="{{ old('amount') }}"
                            placeholder="0"
                            min="1"
                            step="1"
                            required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">
                        <span id="amountText" class="text-muted"></span>
                        <span id="amountWarning" class="text-danger" style="display: none;"></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="payFullBtn" style="display: none;">
                        <i class="fas fa-check"></i> Pay Full Outstanding
                    </button>
                </div>

                <!-- Reference -->
                <div class="col-md-6 mb-3">
                    <label for="reference" class="form-label">
                        <i class="fas fa-hashtag"></i> Reference No (Optional)
                    </label>
                    <input type="text" 
                        class="form-control @error('reference') is-invalid @enderror" 
                        id="reference" 
                        name="reference" 
                        value="{{ old('reference') }}"
                        placeholder="e.g., TRX20260131001">
                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Transaction reference or bank transfer code</div>
                </div>

                <!-- Note -->
                <div class="col-md-6 mb-3">
                    <label for="note" class="form-label">
                        <i class="fas fa-sticky-note"></i> Note (Optional)
                    </label>
                    <textarea class="form-control @error('note') is-invalid @enderror" 
                        id="note" 
                        name="note" 
                        rows="2" 
                        placeholder="Additional notes...">{{ old('note') }}</textarea>
                    @error('note')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <div>
                    <span id="paymentSummary" class="me-3 text-muted"></span>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> Record Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedInvoice = null;
let outstandingAmount = 0;

// Format currency
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Update invoice details
function updateInvoiceDetails() {
    const select = document.getElementById('invoice_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        selectedInvoice = {
            customer: selectedOption.dataset.customer,
            pppoe: selectedOption.dataset.pppoe,
            package: selectedOption.dataset.package,
            period: selectedOption.dataset.period,
            total: parseFloat(selectedOption.dataset.total),
            paid: parseFloat(selectedOption.dataset.paid),
            outstanding: parseFloat(selectedOption.dataset.outstanding)
        };
        
        outstandingAmount = selectedInvoice.outstanding;
        
        // Show detail card
        document.getElementById('invoiceDetailCard').style.display = 'block';
        
        // Fill details
        document.getElementById('detail-customer').textContent = selectedInvoice.customer;
        document.getElementById('detail-pppoe').textContent = selectedInvoice.pppoe;
        document.getElementById('detail-package').textContent = selectedInvoice.package;
        document.getElementById('detail-period').textContent = selectedInvoice.period;
        document.getElementById('detail-total').textContent = formatRupiah(selectedInvoice.total);
        document.getElementById('detail-paid').textContent = formatRupiah(selectedInvoice.paid);
        document.getElementById('detail-outstanding').textContent = formatRupiah(selectedInvoice.outstanding);
        
        // Show pay full button
        document.getElementById('payFullBtn').style.display = 'inline-block';
        
        // Enable submit if amount is set
        validateForm();
    } else {
        // Hide detail card
        document.getElementById('invoiceDetailCard').style.display = 'none';
        document.getElementById('payFullBtn').style.display = 'none';
        selectedInvoice = null;
        outstandingAmount = 0;
        
        // Disable submit
        document.getElementById('submitBtn').disabled = true;
    }
}

// Validate amount
function validateAmount() {
    const amountInput = document.getElementById('amount');
    const amount = parseFloat(amountInput.value) || 0;
    const amountText = document.getElementById('amountText');
    const amountWarning = document.getElementById('amountWarning');
    
    if (amount > 0) {
        amountText.textContent = formatRupiah(amount);
        
        if (selectedInvoice && amount > outstandingAmount) {
            amountWarning.textContent = '⚠️ Amount exceeds outstanding!';
            amountWarning.style.display = 'inline';
            amountText.style.display = 'none';
        } else {
            amountWarning.style.display = 'none';
            amountText.style.display = 'inline';
        }
    } else {
        amountText.textContent = '';
        amountWarning.style.display = 'none';
    }
    
    validateForm();
}

// Validate form
function validateForm() {
    const invoiceId = document.getElementById('invoice_id').value;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const method = document.getElementById('method').value;
    const cashAccountId = document.getElementById('cash_account_id').value;
    const submitBtn = document.getElementById('submitBtn');
    
    if (invoiceId && amount > 0 && amount <= outstandingAmount && method && cashAccountId) {
        submitBtn.disabled = false;
        
        // Update summary
        const remaining = outstandingAmount - amount;
        let summaryText = `Paying ${formatRupiah(amount)}`;
        if (remaining > 0) {
            summaryText += ` | Remaining: ${formatRupiah(remaining)}`;
        } else {
            summaryText += ` | <span class="text-success"><strong>FULL PAYMENT</strong></span>`;
        }
        document.getElementById('paymentSummary').innerHTML = summaryText;
    } else {
        submitBtn.disabled = true;
        document.getElementById('paymentSummary').innerHTML = '';
    }
}

// Pay full button
document.getElementById('payFullBtn').addEventListener('click', function() {
    if (selectedInvoice) {
        document.getElementById('amount').value = selectedInvoice.outstanding;
        validateAmount();
    }
});

// Event listeners
document.getElementById('invoice_id').addEventListener('change', updateInvoiceDetails);
document.getElementById('amount').addEventListener('input', validateAmount);
document.getElementById('method').addEventListener('change', validateForm);
document.getElementById('cash_account_id').addEventListener('change', validateForm);

// Initialize on page load
window.addEventListener('load', function() {
    @if($selectedInvoiceId)
        updateInvoiceDetails();
    @endif
});
</script>
@endpush