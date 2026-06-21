@extends('layouts-main.app')

@section('title', 'Edit Payment Staging')
@section('page-title', 'Edit Payment Staging - ' . $paymentStaging->nama_pelanggan)

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Edit Data Flagged
                    </h5>
                </div>

                <div class="card-body">
                    @if($paymentStaging->flag_reason)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Original Flag Reason:</strong>
                            <p class="mb-0">{{ $paymentStaging->flag_reason }}</p>
                        </div>
                    @endif

                    <form action="{{ route('pembukuan.staging.update', $paymentStaging->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="kode_transaksi" class="form-label fw-bold">Kode Transaksi</label>
                            <input type="text" class="form-control @error('kode_transaksi') is-invalid @enderror"
                                   id="kode_transaksi" name="kode_transaksi"
                                   value="{{ old('kode_transaksi', $paymentStaging->kode_transaksi) }}"
                                   placeholder="Optional">
                            @error('kode_transaksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama_pelanggan" class="form-label fw-bold">Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_pelanggan') is-invalid @enderror"
                                   id="nama_pelanggan" name="nama_pelanggan"
                                   value="{{ old('nama_pelanggan', $paymentStaging->nama_pelanggan) }}"
                                   required>
                            @error('nama_pelanggan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jumlah" class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01"
                                           class="form-control @error('jumlah') is-invalid @enderror"
                                           id="jumlah" name="jumlah"
                                           value="{{ old('jumlah', $paymentStaging->jumlah) }}"
                                           required>
                                </div>
                                <small class="text-muted d-block mt-1">Max: Rp 500.000</small>
                                @error('jumlah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_bayar" class="form-label fw-bold">Tanggal Bayar <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_bayar') is-invalid @enderror"
                                       id="tanggal_bayar" name="tanggal_bayar"
                                       value="{{ old('tanggal_bayar', $paymentStaging->tanggal_bayar ? $paymentStaging->tanggal_bayar->format('Y-m-d') : '') }}"
                                       required>
                                @error('tanggal_bayar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label fw-bold">Area</label>
                                <input type="text" class="form-control @error('area') is-invalid @enderror"
                                       id="area" name="area"
                                       value="{{ old('area', $paymentStaging->area) }}"
                                       placeholder="Optional">
                                @error('area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="paket" class="form-label fw-bold">Paket</label>
                                <input type="text" class="form-control @error('paket') is-invalid @enderror"
                                       id="paket" name="paket"
                                       value="{{ old('paket', $paymentStaging->paket) }}"
                                       placeholder="Optional">
                                @error('paket')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="metode" class="form-label fw-bold">Metode</label>
                                <input type="text" class="form-control @error('metode') is-invalid @enderror"
                                       id="metode" name="metode"
                                       value="{{ old('metode', $paymentStaging->metode) }}"
                                       placeholder="Cash/Transfer/Online/QRIS">
                                @error('metode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dibayar_oleh" class="form-label fw-bold">Dibayar Oleh</label>
                                <input type="text" class="form-control @error('dibayar_oleh') is-invalid @enderror"
                                       id="dibayar_oleh" name="dibayar_oleh"
                                       value="{{ old('dibayar_oleh', $paymentStaging->dibayar_oleh) }}"
                                       placeholder="Nama admin/collector">
                                @error('dibayar_oleh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bulan_tagihan" class="form-label fw-bold">Bulan Tagihan</label>
                            <input type="text" class="form-control @error('bulan_tagihan') is-invalid @enderror"
                                   id="bulan_tagihan" name="bulan_tagihan"
                                   value="{{ old('bulan_tagihan', $paymentStaging->bulan_tagihan) }}"
                                   placeholder="YYYY-MM">
                            @error('bulan_tagihan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('pembukuan.staging.show', $paymentStaging->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- INFO PANEL --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Info</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Koreksi data yang di-flag, kemudian approve untuk melanjutkan ke jurnal.
                    </p>

                    <div class="alert alert-light border">
                        <strong>Source Ref:</strong> {{ $paymentStaging->source_ref }}
                        <br>
                        <strong>Created:</strong> {{ $paymentStaging->created_at->format('d M Y H:i') }}
                        <br>
                        <strong>Status:</strong>
                        <span class="badge bg-warning">{{ ucfirst($paymentStaging->status) }}</span>
                    </div>
                </div>
            </div>
{{-- 
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Raw Data (API)</h5>
                </div>

                <div class="card-body">
                    <pre class="bg-light p-2 rounded" style="max-height: 250px; overflow-y: auto;"><code>{{ json_encode($paymentStaging->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            </div>
        </div> --}}
    </div>

</div>
@endsection
