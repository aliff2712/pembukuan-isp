@extends('layouts-main.app')

@section('title', 'Detail Payment Staging')
@section('page-title', 'Detail Payment Staging')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Data Transaksi</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pelanggan</label>
                            <p>{{ $paymentStaging->nama_pelanggan }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kode Transaksi</label>
                            <p>{{ $paymentStaging->kode_transaksi ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jumlah</label>
                            <p class="text-success fw-bold">Rp {{ number_format($paymentStaging->jumlah, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Bayar</label>
                            <p>{{ $paymentStaging->tanggal_bayar ? $paymentStaging->tanggal_bayar->format('d M Y') : '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Area</label>
                            <p>{{ $paymentStaging->area ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Paket</label>
                            <p>{{ $paymentStaging->paket ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Metode</label>
                            <p>{{ ucfirst($paymentStaging->metode ?? '-') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dibayar Oleh</label>
                            <p>{{ $paymentStaging->dibayar_oleh ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bulan Tagihan</label>
                            <p>{{ $paymentStaging->bulan_tagihan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STATUS CARD --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Status & Validation</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <p>
                                @if($paymentStaging->status === 'pending')
                                    <span class="badge bg-secondary">Pending</span>
                                @elseif($paymentStaging->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($paymentStaging->status === 'flagged')
                                    <span class="badge bg-danger">Flagged</span>
                                @elseif($paymentStaging->status === 'rejected')
                                    <span class="badge bg-dark">Rejected</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Locked?</label>
                            <p>
                                @if($paymentStaging->is_locked)
                                    <span class="badge bg-danger">Locked</span>
                                    <small class="text-muted">
                                        {{ $paymentStaging->locked_at ? $paymentStaging->locked_at->format('d M Y H:i') : '-' }}
                                    </small>
                                @else
                                    <span class="badge bg-success">Not Locked</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($paymentStaging->flag_reason)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Reason for Flagging:</strong>
                            <p class="mb-0">{{ $paymentStaging->flag_reason }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Journalized?</label>
                            <p>
                                @if($paymentStaging->is_journalized)
                                    <span class="badge bg-success">Yes</span>
                                    <small class="text-muted">
                                        {{ $paymentStaging->journalized_at ? $paymentStaging->journalized_at->format('d M Y H:i') : '-' }}
                                    </small>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SIDEBAR: AUDIT TRAIL --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Audit Trail</h5>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Created</label>
                        <p class="text-muted">
                            {{ $paymentStaging->created_at ? $paymentStaging->created_at->format('d M Y H:i') : '-' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Updated</label>
                        <p class="text-muted">
                            {{ $paymentStaging->updated_at ? $paymentStaging->updated_at->format('d M Y H:i') : '-' }}
                        </p>
                    </div>

                    @if($paymentStaging->reviewed_by)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reviewed By</label>
                            <p>
                                {{ $paymentStaging->reviewer?->name ?? 'Unknown' }}
                                <br>
                                <small class="text-muted">
                                    {{ $paymentStaging->reviewed_at ? $paymentStaging->reviewed_at->format('d M Y H:i') : '-' }}
                                </small>
                            </p>
                        </div>
                    @endif

                    @if($paymentStaging->approved_by)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Approved By</label>
                            <p>
                                {{ $paymentStaging->approver?->name ?? 'Unknown' }}
                                <br>
                                <small class="text-muted">
                                    {{ $paymentStaging->approved_at ? $paymentStaging->approved_at->format('d M Y H:i') : '-' }}
                                </small>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- RAW DATA
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Raw Data (API)</h5>
                </div>

                <div class="card-body">
                    <pre class="bg-light p-2 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($paymentStaging->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- ACTIONS --}}
    <div class="card shadow-sm border-0">
        <div class="card-footer bg-light">
            <a href="{{ route('pembukuan.staging.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

            @if(!$paymentStaging->is_locked && $paymentStaging->status === 'flagged')
                <a href="{{ route('pembukuan.staging.edit', $paymentStaging->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>

                <form action="{{ route('pembukuan.staging.approve', $paymentStaging->id) }}"
                      method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                </form>

                <form action="{{ route('pembukuan.staging.reject', $paymentStaging->id) }}"
                      method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Yakin reject data ini?')">
                        <i class="fas fa-times-circle me-1"></i> Reject
                    </button>
                </form>
            @elseif($paymentStaging->is_locked)
                <span class="badge bg-secondary ms-2">Data Locked - Cannot Edit</span>
            @endif
        </div>
    </div>

</div>
@endsection
