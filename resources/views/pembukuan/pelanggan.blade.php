@extends('layouts-main.app')
@section('title', 'Daftar Pelanggan Billing')

@section('content')

<style>
body { background: #0f172a; }

.summary-card {
    background: #1e293b;
    border-radius: 18px;
    padding: 24px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow:
        0 10px 25px rgba(0,0,0,0.35),
        0 2px 6px rgba(0,0,0,0.25),
        inset 0 1px 0 rgba(255,255,255,0.05);
    transition: all .25s ease;
}

.summary-card:hover {
    transform: translateY(-4px);
    box-shadow:
        0 18px 35px rgba(0,0,0,0.45),
        0 4px 10px rgba(0,0,0,0.35),
        inset 0 1px 0 rgba(255,255,255,0.08);
}

.summary-card small { color: #94a3b8; font-size: 13px; }
.summary-number { font-size: 30px; font-weight: 700; color: #ffffff; }
.summary-amount { margin-top: 4px; font-weight: 600; font-size: 15px; color: #60a5fa; }

.filter-card {
    background: #1e293b;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 10px 25px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
}

.filter-card .form-label { color: #cbd5e1; font-size: 13px; font-weight: 500; }

.filter-card .form-control {
    background: #0f172a;
    border: 1px solid #0f172a;
    color: #ffffff;
    border-radius: 12px;
    padding: 9px 14px;
    transition: all .2s ease;
}

.filter-card .form-control::placeholder { color: #94a3b8; }
.filter-card .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
}

.filter-card .form-select {
    background: #0f172a;
    border: 1px solid #0f172a;
    color: #ffffff;
    border-radius: 12px;
    padding: 9px 14px;
}

.filter-card .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
}

.btn-navy {
    background: #2563eb;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
}
.btn-navy:hover { background: #1d4ed8; color: #fff; }

.btn-export {
    background: #16a34a;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
}
.btn-export:hover { background: #15803d; color: #fff; }

.table thead th {
    background: #1e3a5f;
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .5px;
    border: none;
    padding: 14px 16px;
}

.table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #e2e8f0;
    border-color: rgba(255,255,255,0.06);
}

.table-hover tbody tr { transition: all .2s ease-in-out; }
.table-hover tbody tr:hover {
    background: rgba(59, 130, 246, 0.06);
    transform: scale(1.002);
}

.badge-aktif {
    background: #22c55e;
    color: #fff;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
}

.badge-nonaktif {
    background: rgba(220,38,38,0.3);
    color: #fca5a5;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
}

.area-badge {
    background: #1e3a5f;
    border: 1px solid rgba(59,130,246,0.3);
    border-radius: 14px;
    padding: 10px 16px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all .2s ease;
}

.area-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.3);
}

.area-name { color: #ffffff; font-weight: 600; font-size: 13px; }
.area-count { color: #94a3b8; font-size: 12px; }
.area-nominal {
    color: #60a5fa;
    font-weight: 700;
    font-size: 13px;
    border-left: 1px solid rgba(255,255,255,0.1);
    padding-left: 10px;
    margin-left: 4px;
}

.alert-success-navy {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
    border-radius: 12px;
    padding: 12px 16px;
}

.alert-error-navy {
    background: rgba(220, 38, 38, 0.15);
    border: 1px solid rgba(220, 38, 38, 0.3);
    color: #fca5a5;
    border-radius: 12px;
    padding: 12px 16px;
}
</style>

{{-- HEADER --}}
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="m-0 font-weight-bold text-white">
            Daftar Pelanggan Billing
        </h3>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end gap-2">
            {{-- Import dari API --}}
            <form method="POST" action="{{ route('sinkron.pelanggan.import') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-navy px-4">
                    <i class="fas fa-sync me-1"></i> Sinkron Pelanggan
                </button>
            </form>

            {{-- Delete All --}}
            <form method="POST" action="{{ route('sinkron.deletePelanggan') }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA data pelanggan? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm" value="DELETE_ALL">
                <button type="submit" class="btn btn-danger px-4">
                    <i class="fas fa-trash me-1"></i> Hapus Semua
                </button>
            </form>

            {{-- Export CSV --}}
            <a href="{{ route('sinkron.pelanggan.export') }}" class="btn btn-export px-4">
                <i class="fas fa-file-csv me-1"></i> Export 
            </a>
        </div>
    </div>
</div>

{{-- NOTIFIKASI --}}
@if(session('success'))
    <div class="alert-success-navy mb-4">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error-navy mb-4">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    </div>
@endif

{{-- SUMMARY --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="summary-card text-center">
            <small>Total Pelanggan</small>
            <div class="summary-number">{{ $totalPelanggan }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card text-center">
            <small>Total Tagihan Bulanan</small>
            <div class="summary-number" style="font-size: 22px;">
                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card text-center">
            <small>Total Area</small>
            <div class="summary-number">{{ $perArea->count() }}</div>
        </div>
    </div>
</div>

{{-- REKAP PER AREA --}}
@if($perArea->count() > 0)
<div class="mb-4">
    <p class="text-white fw-semibold mb-3">Rekap Per Area</p>
    <div class="d-flex flex-wrap gap-3">
        @foreach($perArea as $area)
        <div class="area-badge">
            <div class="d-flex flex-column">
                <span class="area-name">{{ $area->area ?? 'Unknown' }}</span>
                <span class="area-count">{{ $area->jumlah }} pelanggan</span>
            </div>
            <span class="area-nominal">Rp {{ number_format($area->total, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- FILTER --}}
<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><b>Cari Nama / Phone</b></label>
                    <input type="text" name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Cari pelanggan...">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><b>Area</b></label>
                    <select name="area" class="form-select">
                        <option value="">Semua Area</option>
                        @foreach($perArea as $a)
                            <option value="{{ $a->area }}"
                                {{ request('area') === $a->area ? 'selected' : '' }}>
                                {{ $a->area }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><b>Status</b></label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif"    {{ request('status') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-navy px-4">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('sinkron.pelanggan') }}" class="btn btn-outline-light px-4">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TABEL --}}
<div class="card modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle table-dark table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Phone</th>
                        <th>Area</th>
                        <th>Paket</th>
                        <th class="text-end">Total Tagihan</th>
                        <th>IP Address</th>
                        <th>Tgl Register</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan as $i => $p)
                    <tr>
                        <td class="text-white">{{ $pelanggan->firstItem() + $i }}</td>
                        <td class="fw-semibold text-white">{{ $p->nama }}</td>
                        <td>{{ $p->phone ?? '-' }}</td>
                        <td>{{ $p->area ?? '-' }}</td>
                        <td>{{ $p->paket ?? '-' }}</td>
                        <td class="text-end fw-bold text-white">
                            Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td>
                            <code style="color: #60a5fa; font-size: 12px;">
                                {{ $p->ip_address ?? '-' }}
                            </code>
                        </td>
                        <td>
                            {{ $p->tanggal_register
                                ? \Carbon\Carbon::parse($p->tanggal_register)->format('d M Y')
                                : '-' }}
                        </td>
                        <td class="text-center">
                            @if($p->status === 'aktif')
                                <span class="badge badge-aktif">AKTIF</span>
                            @else
                                <span class="badge badge-nonaktif">NONAKTIF</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('sinkron.deletePelangganById', $p->id) }}" class="d-inline" onsubmit="return confirm('Hapus pelanggan {{ $p->nama }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus pelanggan ini">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-white">
                            Belum ada data. Klik <strong>Sinkron Pelanggan</strong> untuk import.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pelanggan->hasPages())
    <div class="card-footer bg-dark">
        {{ $pelanggan->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection