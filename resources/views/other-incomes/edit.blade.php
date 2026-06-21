@extends('layouts-main.app')
@section('title', __('Edit Income'))
@section('page-title', __('Edit Pendapatan Lain'))
@section('content')
<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('other-incomes.show', $income->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-edit"></i> Edit Pendapatan Lain
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

            <form method="POST" action="{{ route('other-incomes.update', $income->id) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Income Date -->
                    <div class="col-md-6 mb-3">
                        <label for="income_date" class="form-label">
                            <i class="far fa-calendar"></i> Tanggal <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                            class="form-control @error('income_date') is-invalid @enderror"
                            id="income_date"
                            name="income_date"
                            value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}"
                            required>
                        @error('income_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Amount -->
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">
                            <i class="fas fa-money-bill-wave"></i> Jumlah <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number"
                                class="form-control @error('amount') is-invalid @enderror"
                                id="amount"
                                name="amount"
                                value="{{ old('amount', $income->amount) }}"
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
                        </div>
                    </div>

                    <!-- Income Account (Revenue COA) -->
                    <div class="col-md-6 mb-3">
                        <label for="income_coa_id" class="form-label">
                            <i class="fas fa-tags"></i> Akun Pendapatan <span class="text-danger">*</span>
                        </label>
                        <select class="form-control @error('income_coa_id') is-invalid @enderror"
                            id="income_coa_id"
                            name="income_coa_id"
                            required>
                            <option value="">-- Pilih Akun Pendapatan --</option>
                            @foreach($incomeAccounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('income_coa_id', $income->income_coa_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('income_coa_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Akun kategori pendapatan lain (4xxx - Revenue)</div>
                    </div>

                    <!-- Cash/Bank Account -->
                    <div class="col-md-6 mb-3">
                        <label for="cash_coa_id" class="form-label">
                            <i class="fas fa-university"></i> Diterima Di (Kas/Bank) <span class="text-danger">*</span>
                        </label>
                        <select class="form-control @error('cash_coa_id') is-invalid @enderror"
                            id="cash_coa_id"
                            name="cash_coa_id"
                            required>
                            <option value="">-- Pilih Akun Kas/Bank --</option>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('cash_coa_id', $income->cash_coa_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('cash_coa_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Akun kas atau bank tempat pendapatan diterima</div>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i> Deskripsi <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            value="{{ old('description', $income->description) }}"
                            placeholder="Contoh: Pendapatan bunga bank"
                            maxlength="255"
                            required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12 mb-3">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note"></i> Catatan <span class="text-muted">(Opsional)</span>
                        </label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                            id="notes"
                            name="notes"
                            rows="3"
                            placeholder="Catatan tambahan (opsional)">{{ old('notes', $income->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <!-- Journal Preview -->
                <div id="journalPreview" class="alert alert-light border mb-3">
                    <h6 class="mb-2">
                        <i class="fas fa-book"></i> Preview Jurnal Otomatis
                    </h6>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Akun</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span id="previewCashAccount">-</span></td>
                                <td class="text-end text-success fw-semibold"><span id="previewDebit">Rp 0</span></td>
                                <td class="text-end">-</td>
                            </tr>
                            <tr>
                                <td><span id="previewIncomeAccount">-</span></td>
                                <td class="text-end">-</td>
                                <td class="text-end text-primary fw-semibold"><span id="previewCredit">Rp 0</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Warning -->
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i> Perhatian
                    </h6>
                    <p class="mb-0 small">
                        Perubahan akan <strong>memperbarui jurnal akuntansi</strong> terkait secara otomatis.
                        Jurnal lama akan dihapus dan diganti dengan jurnal baru sesuai data yang diubah.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('other-incomes.show', $income->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatRupiah(value) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
}

function updatePreview() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const cashSelect = document.getElementById('cash_coa_id');
    const incomeSelect = document.getElementById('income_coa_id');

    const cashText = cashSelect.options[cashSelect.selectedIndex]?.text || '-';
    const incomeText = incomeSelect.options[incomeSelect.selectedIndex]?.text || '-';

    document.getElementById('amountText').textContent = amount > 0 ? formatRupiah(amount) : '';
    document.getElementById('previewDebit').textContent = formatRupiah(amount);
    document.getElementById('previewCredit').textContent = formatRupiah(amount);
    document.getElementById('previewCashAccount').textContent = cashSelect.value ? cashText : '-';
    document.getElementById('previewIncomeAccount').textContent = incomeSelect.value ? incomeText : '-';
}

document.getElementById('amount').addEventListener('input', updatePreview);
document.getElementById('cash_coa_id').addEventListener('change', updatePreview);
document.getElementById('income_coa_id').addEventListener('change', updatePreview);

window.addEventListener('load', updatePreview);
</script>
@endpush