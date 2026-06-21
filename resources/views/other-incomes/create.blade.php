@extends('layouts-main.app')
@section('title', __('Tambah Income'))
@section('page-title', __('Tambah Pendapatan Lain'))
@section('content')
<style>
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
<div class="container-fluid py-4">
    <!-- Back Button -->
   

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-plus-circle"></i> Tambah Pendapatan Lain
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

            <form method="POST" action="{{ route('other-incomes.store') }}">
                @csrf

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
                            value="{{ old('income_date', date('Y-m-d')) }}"
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
                                @if($account->account_code != 4101)
                                   
                                    <option value="{{ $account->id }}"
                                        {{ old('income_coa_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_code }} - {{ $account->account_name }}
                                    </option>
                                @endif
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
                            {{-- ✅ BENAR — value pakai id --}}
                            <option value="{{ $account->id }}"
                                {{ old('income_coa_id') == $account->id ? 'selected' : '' }}>
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
                            value="{{ old('description') }}"
                            placeholder="Contoh: Pendapatan bunga bank, Pendapatan sewa gedung"
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
                            placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <!-- Journal Preview -->
                <div id="journalPreview" class="alert alert-light border d-none mb-3">
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

                <!-- Info -->
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                    <p class="mb-0 small">
                        Setelah disimpan, jurnal akuntansi akan <strong>otomatis dibuat</strong> dengan entri:
                        <br>Debit <strong>Kas/Bank</strong> dan Kredit <strong>Akun Pendapatan</strong> sejumlah yang diinput.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('other-incomes.index') }}" class="btn btn-secondary" id="cancelBtn">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function formatRupiah(value) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
}

function updatePreview() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const cashSelect = document.getElementById('cash_coa_id');
    const incomeSelect = document.getElementById('income_coa_id');
    const preview = document.getElementById('journalPreview');

    const cashText = cashSelect.options[cashSelect.selectedIndex]?.text || '-';
    const incomeText = incomeSelect.options[incomeSelect.selectedIndex]?.text || '-';

    document.getElementById('amountText').textContent = amount > 0 ? formatRupiah(amount) : '';
    document.getElementById('previewDebit').textContent = formatRupiah(amount);
    document.getElementById('previewCredit').textContent = formatRupiah(amount);
    document.getElementById('previewCashAccount').textContent = cashSelect.value ? cashText : '-';
    document.getElementById('previewIncomeAccount').textContent = incomeSelect.value ? incomeText : '-';

    // Tampilkan preview jika semua sudah diisi
    if (amount > 0 && cashSelect.value && incomeSelect.value) {
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
}

document.getElementById('amount').addEventListener('input', updatePreview);
document.getElementById('cash_coa_id').addEventListener('change', updatePreview);
document.getElementById('income_coa_id').addEventListener('change', updatePreview);

// Trigger on load
window.addEventListener('load', updatePreview);
if (cancelBtn) {

cancelBtn.addEventListener('click', function(e){

    e.preventDefault();

    const targetUrl = this.href;

    Swal.fire({
        title: "Batalkan Proses Input?",
        text: "Data Yang Di input Tidak Akan Tersimpan.",
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