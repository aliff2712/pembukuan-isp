@extends('layouts-main.app')

@section('title', 'Add Expense')
@section('page-title', 'Add New Expense')

@section('content')
<!-- Back Button -->

<style>
    /* ALERT NAVY STYLE */
.alert-success {
    background: rgba(22,163,74,.15);
    color: #4ade80;
    border: none;
}

.alert-danger {
    background: rgba(220,38,38,.15);
    color: #f87171;
    border: none;
}
.text-primary-white {
    color: #ffffff;
}
.swal2-popup {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-radius: 16px !important;
    border: 1px solid rgba(255,255,255,0.05);
}

.swal2-title {
    color: #ffffff !important;
    font-weight: 600;
}

.swal2-html-container {
    color: #cbd5e1 !important;
}

/* BUTTON CONFIRM */
.swal2-confirm {
    background: #2563eb !important;
    border-radius: 10px !important;
    padding: 8px 20px !important;
}

/* BUTTON CANCEL */
.swal2-cancel {
    background: #334155 !important;
    border-radius: 10px !important;
}

/* MOBILE SIZE */
@media (max-width: 576px) {
    .swal2-popup {
        width: 85% !important;
        font-size: 14px;
    }
}
</style>
<!-- Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary-white">
            <i class="fas fa-receipt"></i> Expense Information
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

        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf

            <div class="row">
                <!-- Expense Date -->
                <div class="col-md-6 mb-3">
                    <label for="expense_date" class="form-label">
                        <i class="far fa-calendar"></i> Expense Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" 
                        class="form-control @error('expense_date') is-invalid @enderror" 
                        id="expense_date" 
                        name="expense_date" 
                        value="{{ old('expense_date', date('Y-m-d')) }}"
                        required>
                    @error('expense_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">
                        <i class="fas fa-money-bill-wave"></i> Amount <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" 
                            class="form-control @error('amount') is-invalid @enderror" 
                            id="amount" 
                            name="amount" 
                            value="{{ old('amount') }}"
                            placeholder="0"
                            min="0"
                            step="1"
                            required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">
                        <span id="amountText" class="text-muted"></span>
                    </div>
                </div>

                <!-- Expense Account -->
                <div class="col-md-6 mb-3">
                    <label for="expense_coa_id" class="form-label">
                        <i class="fas fa-tags"></i> Expense Account (Beban) <span class="text-danger">*</span>
                    </label>
                    <select class="form-control @error('expense_coa_id') is-invalid @enderror" 
                        id="expense_coa_id" 
                        name="expense_coa_id" 
                        required>
                        <option value="">-- Select Expense Account --</option>
                        @foreach($expenseAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('expense_coa_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_coa_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Akun kategori pengeluaran (5xxx - Expense)
                    </div>
                </div>

                <!-- Cash/Bank Account -->
                <div class="col-md-6 mb-3">
                    <label for="cash_coa_id" class="form-label">
                        <i class="fas fa-university"></i> Paid From (Kas/Bank) <span class="text-danger">*</span>
                    </label>
                    <select class="form-control @error('cash_coa_id') is-invalid @enderror" 
                        id="cash_coa_id" 
                        name="cash_coa_id" 
                        required>
                        <option value="">-- Select Cash/Bank Account --</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('cash_coa_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('cash_coa_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Akun sumber pembayaran (kas atau bank)
                    </div>
                </div>

                <!-- Description -->
                <div class="col-12 mb-3">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i> Description <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        id="description" 
                        name="description" 
                        rows="4" 
                        placeholder="Enter expense description (e.g., Pembelian alat kantor, Bayar listrik bulan ini, dll)"
                        required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Maximum 1000 characters
                    </div>
                </div>
            </div>

            <hr>

            <!-- Info Box -->
            <div class="alert alert-info" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle"></i> Journal Entry Preview
                </h6>
                <p class="mb-0">
                    When you save this expense, the system will automatically create a journal entry:
                </p>
                <table class="table table-sm table-borderless mt-2 mb-0">
                    <tr>
                        <td width="100"><strong>Debit:</strong></td>
                        <td id="preview-debit" class="text-muted">
                            <em>Select expense account to see preview</em>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Credit:</strong></td>
                        <td id="preview-credit" class="text-muted">
                            <em>Select cash/bank account to see preview</em>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary" id="cancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Expense
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Format amount as currency
document.getElementById('amount').addEventListener('input', function(e) {
    const value = parseFloat(e.target.value) || 0;
    const formatted = new Intl.NumberFormat('id-ID').format(value);
    document.getElementById('amountText').textContent = 'Rp ' + formatted;
});

// Update journal entry preview
function updatePreview() {
    const expenseSelect = document.getElementById('expense_coa_id');
    const cashSelect = document.getElementById('cash_coa_id');
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    
    const expenseText = expenseSelect.options[expenseSelect.selectedIndex]?.text || '';
    const cashText = cashSelect.options[cashSelect.selectedIndex]?.text || '';
    
    const amountFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    
    if (expenseText && expenseText !== '-- Select Expense Account --') {
        document.getElementById('preview-debit').innerHTML = `<strong>${expenseText}</strong> - ${amountFormatted}`;
    } else {
        document.getElementById('preview-debit').innerHTML = '<em>Select expense account to see preview</em>';
    }
    
    if (cashText && cashText !== '-- Select Cash/Bank Account --') {
        document.getElementById('preview-credit').innerHTML = `<strong>${cashText}</strong> - ${amountFormatted}`;
    } else {
        document.getElementById('preview-credit').innerHTML = '<em>Select cash/bank account to see preview</em>';
    }
}

document.getElementById('expense_coa_id').addEventListener('change', updatePreview);
document.getElementById('cash_coa_id').addEventListener('change', updatePreview);
document.getElementById('amount').addEventListener('input', updatePreview);

// Trigger on page load if old values exist
window.addEventListener('load', updatePreview);
if (cancelBtn) {

cancelBtn.addEventListener('click', function(e){

    e.preventDefault();

    const targetUrl = this.href;

    Swal.fire({
        title: "Batalkan penambahan Pengeluaran baru?",
        text: "Data yang anda masukkan tidak akan tersimpan.",
        icon: "warning",
        width: window.innerWidth < 576 ? '85%' : '420px',
        showCancelButton: true,
        confirmButtonText: "Ya, Batalkan",
        cancelButtonText: "Tetap di Halaman",
        confirmButtonColor: "#2563eb",
        cancelButtonColor: "#475569",
        background: "#1e293b",
        color: "#e2e8f0"
    }).then((result) => {

        if (result.isConfirmed) {
            window.location.href = targetUrl;
        }

    });

});

}


</script>
@endpush