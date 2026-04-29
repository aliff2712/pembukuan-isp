@extends('layouts-main.app')
@section('title', 'Sinkron Data Billing')

@section('content')

<style>
body { background: #0f172a; }

.summary-card {
    background: #1e293b;
    border-radius: 18px;
    padding: 24px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 10px 25px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
    transition: all .25s ease;
}
.summary-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 35px rgba(0,0,0,0.45), inset 0 1px 0 rgba(255,255,255,0.08);
}
.summary-card small { color: #94a3b8; font-size: 13px; }
.summary-number { font-size: 30px; font-weight: 700; color: #ffffff; }
.summary-number.text-success { color: #10b981; }
.summary-number.text-warning { color: #f59e0b; }

.filter-card {
    background: #1e293b;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 10px 25px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
}
.filter-card .form-label { color: #cbd5e1; font-size: 13px; font-weight: 500; }
.filter-card .form-control,
.filter-card .form-select {
    background: #0f172a;
    border: 1px solid #0f172a;
    color: #ffffff;
    border-radius: 12px;
    padding: 9px 14px;
    transition: all .2s ease;
}
.filter-card .form-control::placeholder { color: #94a3b8; }
.filter-card .form-control:focus,
.filter-card .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
    background: #0f172a;
    color: #fff;
}
.filter-card .form-select option { background: #1e293b; color: #fff; }

.btn-navy { background: #2563eb; border: none; border-radius: 12px; color: #fff; font-weight: 600; }
.btn-navy:hover { background: #1d4ed8; color: #fff; }

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
.table tbody td { padding: 14px 16px; font-size: 14px; color: #e2e8f0; border-color: rgba(255,255,255,0.06); }
.table-hover tbody tr { transition: all .2s ease-in-out; }
.table-hover tbody tr:hover { background: rgba(59,130,246,0.06); transform: scale(1.002); }

.badge-paid    { background: #22c55e; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 30px; font-size: 12px; }
.badge-cash    { background: #f59e0b; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 30px; font-size: 12px; }
.badge-online  { background: #3b82f6; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 30px; font-size: 12px; }
.badge-journalized     { background: #10b981; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 30px; font-size: 12px; }
.badge-not-journalized { background: #6b7280; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 30px; font-size: 12px; }

.btn-secondary:disabled { background: #6b7280; border-color: #6b7280; color: #fff; cursor: not-allowed; opacity: .7; }

.admin-badge {
    background: #1e3a5f;
    border: 1px solid rgba(59,130,246,0.3);
    border-radius: 14px;
    padding: 10px 16px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all .2s ease;
}
.admin-badge:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.3); }
.admin-avatar { width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
.admin-name   { color: #fff; font-weight: 600; font-size: 13px; }
.admin-count  { color: #94a3b8; font-size: 12px; }
.admin-nominal { color: #60a5fa; font-weight: 700; font-size: 13px; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 10px; margin-left: 4px; }

.alert-success-navy { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #86efac; border-radius: 12px; padding: 12px 16px; }
.alert-error-navy   { background: rgba(220,38,38,0.15);  border: 1px solid rgba(220,38,38,0.3);  color: #fca5a5; border-radius: 12px; padding: 12px 16px; }

.filter-active-badge {
    background: rgba(59,130,246,0.15);
    border: 1px solid rgba(59,130,246,0.4);
    color: #93c5fd;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.filter-active-badge a { color: #f87171; text-decoration: none; font-size: 14px; line-height: 1; }
.filter-active-badge a:hover { color: #ef4444; }
</style>

{{-- HEADER --}}
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="m-0 font-weight-bold text-white">Sinkron Data Billing</h3>
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
{{-- FIX: $sudahDijurnalkan dan $belumDijurnalkan sudah dihitung di controller, tidak perlu query di blade --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="summary-card text-center">
            <small>Total Transaksi Tersimpan</small>
            <div class="summary-number">{{ $totalTransaksi }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card text-center">
            <small>Total Nominal</small>
            <div class="summary-number">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card text-center">
            <small>Sudah Dijurnalkan</small>
            <div class="summary-number text-success">{{ $sudahDijurnalkan }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card text-center">
            <small>Belum Dijurnalkan</small>
            <div class="summary-number text-warning">{{ $belumDijurnalkan }}</div>
        </div>
    </div>
</div>

{{-- REKAP PER ADMIN --}}
@if(isset($perAdmin) && $perAdmin->count() > 0)
<div class="mb-4">
    <p class="text-white fw-semibold mb-3">Rekap Per Admin</p>
    <div class="d-flex flex-wrap gap-3">
        @foreach($perAdmin as $admin)
            @php $inisial = strtoupper(substr($admin->dibayar_oleh ?? 'U', 0, 1)); @endphp
            <div class="admin-badge">
                <div class="admin-avatar">{{ $inisial }}</div>
                <div class="d-flex flex-column">
                    <span class="admin-name">{{ $admin->dibayar_oleh ?? 'Unknown' }}</span>
                    <span class="admin-count">{{ $admin->jumlah_transaksi }} transaksi</span>
                </div>
                <span class="admin-nominal">Rp {{ number_format($admin->total_nominal, 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- FORM FILTER --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('sinkron.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><b>Cari Nama / Kode</b></label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control" placeholder="Nama pelanggan / kode...">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><b>Bulan Tagihan</b></label>
                    <input type="month" name="bulan_filter" value="{{ request('bulan_filter') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><b>Area</b></label>
                    <select name="area" class="form-control">
                        <option value="">Semua Area</option>
                        @foreach($areaList as $area)
                            <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><b>Metode</b></label>
                    <select name="metode" class="form-control">
                        <option value="">Semua Metode</option>
                        @foreach($metodeList as $metode)
                            <option value="{{ $metode }}" {{ request('metode') == $metode ? 'selected' : '' }}>{{ strtoupper($metode) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><b>Admin</b></label>
                    <select name="dibayar_oleh" class="form-control">
                        <option value="">Semua Admin</option>
                        @foreach($adminList as $adm)
                            <option value="{{ $adm }}" {{ request('dibayar_oleh') == $adm ? 'selected' : '' }}>{{ $adm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-navy px-3 w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        {{-- Filter Aktif --}}
        @php
            $activeFilters = array_filter([
                'search'       => request('search'),
                'bulan_filter' => request('bulan_filter'),
                'area'         => request('area'),
                'metode'       => request('metode'),
                'dibayar_oleh' => request('dibayar_oleh'),
            ]);
        @endphp

        @if(count($activeFilters) > 0)
        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
            <small class="text-muted me-1">Filter aktif:</small>
            @foreach($activeFilters as $key => $val)
                @php
                    $label = match($key) {
                        'search'       => 'Cari',
                        'bulan_filter' => 'Bulan',
                        'area'         => 'Area',
                        'metode'       => 'Metode',
                        'dibayar_oleh' => 'Admin',
                        default        => $key,
                    };
                    $removeUrl = request()->fullUrlWithQuery([$key => null]);
                @endphp
                <span class="filter-active-badge">
                    {{ $label }}: <b>{{ strtoupper($val) }}</b>
                    <a href="{{ $removeUrl }}" title="Hapus filter ini">&times;</a>
                </span>
            @endforeach
            <a href="{{ route('sinkron.index') }}" class="filter-active-badge"
               style="background:rgba(220,38,38,0.1);border-color:rgba(220,38,38,0.3);color:#fca5a5;">
                <i class="fas fa-times me-1"></i> Reset Semua
            </a>
        </div>
        @endif
    </div>
</div>

{{-- BULK ACTIONS CARD --}}
<div class="card filter-card mb-4" style="border-left: 4px solid #3b82f6;">
    <div class="card-body">
        <div class="row g-3">

            {{-- IMPORT --}}
            <div class="col-md-4">
                <form method="POST" action="{{ route('sinkron.import') }}" class="h-100">
                    @csrf
                    <div class="d-flex flex-column h-100">
                        <label class="form-label mb-2"><b><i class="fas fa-download me-2"></i>Import Data</b></label>
                        <div class="mb-3 flex-grow-1">
                            <input type="month" name="bulan" value="{{ now()->format('Y-m') }}" class="form-control form-control-sm">
                            <small class="text-muted d-block mt-1">Import data dari sistem billing</small>
                        </div>
                        <button type="submit" class="btn btn-navy w-100 btn-sm">
                            <i class="fas fa-file-import me-1"></i> Import Sekarang
                        </button>
                    </div>
                </form>
            </div>

            {{-- EXPORT --}}
            {{-- FIX: Gunakan $exportUrl agar filter aktif ikut terbawa --}}
            <div class="col-md-4">
                <div class="d-flex flex-column h-100" style="gap: 10px;">
                    <label class="form-label mb-2"><b><i class="fas fa-upload me-2"></i>Export Data</b></label>
                    <div class="flex-grow-1">
                        <small class="text-muted d-block mb-2">Export transaksi ke Excel</small>
                        <div class="btn-group-vertical w-100" role="group">
                            <a href="{{ $exportUrl }}"
                               class="btn btn-outline-success btn-sm"
                               title="Export dengan filter saat ini">
                                <i class="fas fa-filter me-1"></i> Filter Aktif
                            </a>
                            <a href="{{ route('sinkron.export', ['all' => 1]) }}"
                               class="btn btn-outline-success btn-sm"
                               title="Export semua data">
                                <i class="fas fa-list me-1"></i> Semua Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DELETE --}}
            <div class="col-md-4">
                <form method="POST" action="{{ route('sinkron.deleteTransaksi') }}"
                      id="form-delete-bulk"
                      class="h-100">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex flex-column h-100">
                        <label class="form-label mb-2"><b><i class="fas fa-trash me-2"></i>Hapus Data</b></label>
                        <div class="mb-3 flex-grow-1">
                            <input type="month" name="bulan" value="{{ now()->format('Y-m') }}"
                                class="form-control form-control-sm" required>
                            <small class="text-muted d-block mt-1">Hapus per bulan tagihan (otomatis amankan yang sudah dijurnal)</small>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 btn-sm">
                            <i class="fas fa-trash-alt me-1"></i> Hapus Bulan Ini
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- INFO BAR HASIL FILTER --}}
@if($isFiltered)
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div class="d-flex align-items-center gap-3">
        <span style="color:#94a3b8;font-size:13px;">
            <i class="fas fa-filter me-1" style="color:#3b82f6;"></i>
            Menampilkan hasil filter:
        </span>
        <span style="background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.35);color:#93c5fd;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:600;">
            {{ number_format($totalFiltered, 0, ',', '.') }} transaksi
        </span>
        <span style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:600;">
            Rp {{ number_format($nominalFiltered, 0, ',', '.') }}
        </span>
    </div>
   
</div>
@endif

{{-- TABEL --}}
<div class="card modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle table-dark table-bordered">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Area</th>
                        <th>Paket</th>
                        <th class="text-end">Jumlah</th>
                        <th>Metode</th>
                        <th>Dibayar Oleh</th>
                        <th>Bulan Tagihan</th>
                        <th>Tanggal Bayar</th>
                        <th class="text-center">Status Bayar</th>
                        <th class="text-center">Status Jurnal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $trx)
                    <tr>
                        <td class="fw-semibold text-white">{{ $trx->nama_pelanggan }}</td>
                        <td>{{ $trx->area ?? '-' }}</td>
                        <td>{{ $trx->paket ?? '-' }}</td>
                        <td class="text-end fw-bold text-white">Rp {{ number_format($trx->jumlah, 0, ',', '.') }}</td>
                        <td>
                            @if(strtolower($trx->metode) === 'cash')
                                <span class="badge badge-cash">CASH</span>
                            @else
                                <span class="badge badge-online">{{ strtoupper($trx->metode ?? '-') }}</span>
                            @endif
                        </td>
                        <td>{{ $trx->dibayar_oleh ?? '-' }}</td>
                        <td>
                            {{ $trx->bulan_tagihan
                                ? \Carbon\Carbon::parse($trx->bulan_tagihan)->translatedFormat('F Y')
                                : '-' }}
                        </td>
                        <td>
                            {{ $trx->tanggal_bayar
                                ? \Carbon\Carbon::parse($trx->tanggal_bayar)->format('d M Y H:i')
                                : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-paid"><i class="far fa-smile me-1"></i> LUNAS</span>
                        </td>
                        <td class="text-center">
                            @if($trx->is_journalized)
                                <span class="badge badge-journalized"
                                      title="Dijurnalkan: {{ $trx->journalized_at ? $trx->journalized_at->format('d M Y H:i') : 'N/A' }}">
                                    <i class="fas fa-check me-1"></i> JURNAL
                                </span>
                            @else
                                <span class="badge badge-not-journalized" title="Belum dijurnalkan">
                                    <i class="fas fa-clock me-1"></i> PENDING
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($trx->is_journalized)
                                <button type="button" class="btn btn-sm btn-secondary" disabled
                                        title="Tidak dapat menghapus transaksi yang sudah dijurnalkan">
                                    <i class="fas fa-lock"></i>
                                </button>
                            @else
                                {{-- FIX: confirm() dipindah ke JS dengan data-attribute agar aman dari XSS --}}
                                <form method="POST" action="{{ route('sinkron.deleteTransaksiById', $trx->id) }}"
                                      class="d-inline form-delete-row"
                                      data-nama="{{ $trx->nama_pelanggan }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus record ini">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-white">
                            Belum ada data. Silakan import atau ubah filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($transaksi->hasPages())
    <div class="card-footer bg-dark">
        {{ $transaksi->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- FIX: confirm() dipindah ke JS, tidak lagi inline di HTML agar aman dari XSS --}}
<script>
document.querySelectorAll('.form-delete-row').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        var nama = form.getAttribute('data-nama') || 'data ini';
        if (!confirm('Hapus transaksi ' + nama + '?')) {
            e.preventDefault();
        }
    });
});

document.getElementById('form-delete-bulk').addEventListener('submit', function(e) {
    if (!confirm('Apakah Anda yakin ingin menghapus semua data transaksi untuk bulan ini?\n\nData yang sudah dijurnal TIDAK akan dihapus (terlindungi).')) {
        e.preventDefault();
    }
});
</script>

@endsection