@extends('layouts-main.app')
@section('title', 'Sinkron Data Billing')

@section('content')
<style>
:root {
    --surface: #1a2744;
    --surface2: #0f172a;
    --border: rgba(255,255,255,0.07);
    --blue: #3b82f6;
    --green: #10b981;
    --amber: #f59e0b;
    --red: #ef4444;
    --text-sub: #94a3b8;
    --text-muted: #64748b;
}

body { background: #0f172a; }

/* ─── Cards ─── */
.surface-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 20px;
}

/* ─── Stats ─── */
.stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 20px; }
.stat-label { font-size: 12px; color: var(--text-sub); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.stat-val { font-size: 28px; font-weight: 700; color: #fff; }
.stat-val.green { color: var(--green); }
.stat-val.amber { color: var(--amber); }

/* ─── Filter toolbar ─── */
.filter-row { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
.filter-field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 140px; }
.filter-field label { font-size: 11px; color: var(--text-sub); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.filter-field .form-control,
.filter-field .form-select {
    background: #0c1629;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 13px;
}
.filter-field .form-control:focus,
.filter-field .form-select:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(59,130,246,.2);
    background: #0c1629;
    color: #fff;
}
.filter-field .form-control::placeholder { color: var(--text-muted); }
.filter-field .form-select option { background: #1a2744; color: #fff; }

/* ─── Buttons ─── */
.btn-navy { background: var(--blue); border: none; border-radius: 10px; color: #fff; font-weight: 600; }
.btn-navy:hover { background: #2563eb; color: #fff; }
.btn-ghost-sm {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    color: var(--text-sub);
    font-weight: 600;
    font-size: 13px;
}
.btn-ghost-sm:hover { background: rgba(255,255,255,0.05); color: #fff; }

/* ─── Active filter badges ─── */
.filter-badge {
    background: rgba(59,130,246,0.12);
    border: 1px solid rgba(59,130,246,0.3);
    color: #93c5fd;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.filter-badge .rm { color: #f87171; text-decoration: none; font-size: 14px; line-height: 1; }
.filter-badge .rm:hover { color: var(--red); }
.filter-badge-reset {
    background: rgba(220,38,38,0.1);
    border-color: rgba(220,38,38,0.3);
    color: #fca5a5;
}

/* ─── Action cards (import / export / delete) ─── */
.action-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 20px; }
.action-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-left: 3px solid var(--blue);
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.action-card.danger { border-left-color: var(--red); }
.action-card h5 { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-sub); margin: 0; }
.action-card .form-control {
    background: #0c1629;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 12px;
}
.action-card small { color: var(--text-muted); font-size: 11px; }
.action-card-footer { display: flex; gap: 6px; margin-top: auto; }

/* ─── Admin recap ─── */
.admin-chip {
    background: rgba(30,58,95,0.6);
    border: 1px solid rgba(59,130,246,0.2);
    border-radius: 12px;
    padding: 8px 14px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.admin-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 12px; flex-shrink: 0;
}
.admin-name { color: #fff; font-weight: 600; font-size: 13px; }
.admin-sub  { color: var(--text-sub); font-size: 11px; }
.admin-nominal {
    color: #60a5fa; font-weight: 700; font-size: 12px;
    border-left: 1px solid rgba(255,255,255,0.1);
    padding-left: 10px; margin-left: 4px;
}

/* ─── Table ─── */
.table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.table thead th {
    background: #162040;
    color: var(--text-sub);
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px;
    border: none; padding: 12px 14px;
}
.table tbody td { padding: 12px 14px; font-size: 13px; color: #cbd5e1; border-color: var(--border); }
.table-hover tbody tr:hover td { background: rgba(59,130,246,0.04); }

/* ─── Badges ─── */
.badge-lunas     { background: rgba(16,185,129,.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,.25); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; }
.badge-cash      { background: rgba(245,158,11,.15); color: #fcd34d; border: 1px solid rgba(245,158,11,.25); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; }
.badge-online    { background: rgba(59,130,246,.15); color: #93c5fd; border: 1px solid rgba(59,130,246,.25); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; }
.badge-jurnal    { background: rgba(16,185,129,.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,.25); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; }
.badge-pending   { background: rgba(107,114,128,.15); color: #9ca3af; border: 1px solid rgba(107,114,128,.25); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; }

/* ─── Alerts ─── */
.alert-ok  { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.25); color: #86efac; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; }
.alert-err { background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.25); color: #fca5a5; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; }
</style>

{{-- HEADER --}}
<div class="row mb-4 align-items-center">
    <div class="col">
        <h3 class="m-0 fw-bold text-white">Sinkron Data Billing</h3>
    </div>
</div>

{{-- NOTIFIKASI --}}
@if(session('success'))
    <div class="alert-ok"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-err"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
@endif

{{-- SUMMARY --}}
<div class="stat-grid">
    <div class="surface-card">
        <div class="stat-label">Total Transaksi</div>
        <div class="stat-val">{{ number_format($totalTransaksi) }}</div>
    </div>
    <div class="surface-card">
        <div class="stat-label">Total Nominal</div>
        <div class="stat-val" style="font-size:20px">Rp {{ number_format($totalNominal,0,',','.') }}</div>
    </div>
    <div class="surface-card">
        <div class="stat-label">Sudah Dijurnalkan</div>
        <div class="stat-val green">{{ \App\Models\SinkronTransaksi::where('is_journalized', true)->count() }}</div>
    </div>
    <div class="surface-card">
        <div class="stat-label">Belum Dijurnalkan</div>
        <div class="stat-val amber">{{ \App\Models\SinkronTransaksi::where('is_journalized', false)->count() }}</div>
    </div>
</div>

{{-- REKAP PER ADMIN --}}
@if(isset($perAdmin) && $perAdmin->count() > 0)
<div class="mb-4">
    <p class="text-white fw-semibold mb-2" style="font-size:13px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-sub) !important">Rekap Per Admin</p>
    <div class="d-flex flex-wrap gap-2">
        @foreach($perAdmin as $admin)
            <div class="admin-chip">
                <div class="admin-avatar">{{ strtoupper(substr($admin->dibayar_oleh ?? 'U', 0, 1)) }}</div>
                <div>
                    <div class="admin-name">{{ $admin->dibayar_oleh ?? 'Unknown' }}</div>
                    <div class="admin-sub">{{ $admin->jumlah_transaksi }} transaksi</div>
                </div>
                <span class="admin-nominal">Rp {{ number_format($admin->total_nominal, 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- FILTER --}}
<div class="surface-card mb-3">
    <form method="GET" action="{{ route('sinkron.index') }}">
        <div class="filter-row">
            <div class="filter-field">
                <label>Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama / kode pelanggan...">
            </div>
            <div class="filter-field">
                <label>Bulan Tagihan</label>
                <input type="month" name="bulan_filter" value="{{ request('bulan_filter') }}" class="form-control">
            </div>
            <div class="filter-field">
                <label>Area</label>
                <select name="area" class="form-select">
                    <option value="">Semua Area</option>
                    @foreach($areaList as $area)
                        <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label>Metode</label>
                <select name="metode" class="form-select">
                    <option value="">Semua Metode</option>
                    @foreach($metodeList as $metode)
                        <option value="{{ $metode }}" {{ request('metode') == $metode ? 'selected' : '' }}>{{ strtoupper($metode) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label>Admin</label>
                <select name="dibayar_oleh" class="form-select">
                    <option value="">Semua Admin</option>
                    @foreach($adminList as $adm)
                        <option value="{{ $adm }}" {{ request('dibayar_oleh') == $adm ? 'selected' : '' }}>{{ $adm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2 align-items-end pb-1">
                <button type="submit" class="btn btn-navy px-3">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('sinkron.index') }}" class="btn btn-ghost-sm px-3">Reset</a>
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
        $filterLabels = ['search'=>'Cari','bulan_filter'=>'Bulan','area'=>'Area','metode'=>'Metode','dibayar_oleh'=>'Admin'];
    @endphp
    @if(count($activeFilters) > 0)
    <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
        <small class="text-muted">Filter aktif:</small>
        @foreach($activeFilters as $key => $val)
            <span class="filter-badge">
                {{ $filterLabels[$key] }}: <b>{{ strtoupper($val) }}</b>
                <a href="{{ request()->fullUrlWithQuery([$key => null]) }}" class="rm" title="Hapus filter">&times;</a>
            </span>
        @endforeach
        <a href="{{ route('sinkron.index') }}" class="filter-badge filter-badge-reset">
            <i class="fas fa-times"></i> Reset Semua
        </a>
    </div>
    @endif
</div>

{{-- ACTION CARDS: Import / Export / Delete --}}
<div class="action-grid">

    {{-- Import --}}
    <div class="action-card">
        <h5><i class="fas fa-file-import me-1"></i> Import Data</h5>
        <form method="POST" action="{{ route('sinkron.import') }}">
            @csrf
            <input type="month" name="bulan" value="{{ now()->format('Y-m') }}" class="form-control">
            <small>Import data dari sistem billing</small>
            <div class="action-card-footer">
                <button type="submit" class="btn btn-navy btn-sm w-100">Import Sekarang</button>
            </div>
        </form>
    </div>

    {{-- Export — BUG FIX: teruskan semua query params aktif ke export --}}
    <div class="action-card">
        <h5><i class="fas fa-file-export me-1"></i> Export Data</h5>
        <small>Export transaksi ke Excel</small>
        <div class="action-card-footer">
            {{-- 
                BUG FIX: gunakan request()->query() agar semua filter aktif
                (search, bulan_filter, area, metode, dibayar_oleh) ikut dikirim
            --}}
            <a href="{{ route('sinkron.export', request()->query()) }}"
               class="btn btn-sm btn-outline-success w-100"
               title="Export dengan filter yang aktif saat ini">
                <i class="fas fa-filter me-1"></i> Filter Aktif
            </a>
            <a href="{{ route('sinkron.export', ['all' => 1]) }}"
               class="btn btn-sm btn-outline-success w-100"
               title="Export semua data tanpa filter">
                <i class="fas fa-list me-1"></i> Semua Data
            </a>
        </div>
    </div>

    {{-- Delete --}}
    <div class="action-card danger">
        <h5><i class="fas fa-trash me-1"></i> Hapus Data</h5>
        <form method="POST" action="{{ route('sinkron.deleteTransaksi') }}"
              onsubmit="return confirm('Hapus semua data bulan ini?\n\nData yang sudah dijurnal TIDAK akan dihapus.')">
            @csrf
            @method('DELETE')
            <input type="month" name="bulan" value="{{ now()->format('Y-m') }}" class="form-control" required>
            <small>Yang sudah dijurnal akan dilindungi otomatis</small>
            <div class="action-card-footer">
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Bulan Ini
                </button>
            </div>
        </form>
    </div>

</div>

{{-- TABLE --}}
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-dark">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Area</th>
                    <th>Paket</th>
                    <th class="text-end">Jumlah</th>
                    <th>Metode</th>
                    <th>Admin</th>
                    <th>Bulan</th>
                    <th>Tgl Bayar</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Jurnal</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $trx)
                <tr>
                    <td class="fw-semibold text-white">{{ $trx->nama_pelanggan }}</td>
                    <td>{{ $trx->area ?? '-' }}</td>
                    <td>{{ $trx->paket ?? '-' }}</td>
                    <td class="text-end fw-bold text-white font-monospace">
                        Rp {{ number_format($trx->jumlah, 0, ',', '.') }}
                    </td>
                    <td>
                        @if(strtolower($trx->metode) === 'cash')
                            <span class="badge-cash">CASH</span>
                        @else
                            <span class="badge-online">{{ strtoupper($trx->metode ?? '-') }}</span>
                        @endif
                    </td>
                    <td>{{ $trx->dibayar_oleh ?? '-' }}</td>
                    <td>{{ $trx->bulan_tagihan ? \Carbon\Carbon::parse($trx->bulan_tagihan)->translatedFormat('M Y') : '-' }}</td>
                    <td>{{ $trx->tanggal_bayar ? \Carbon\Carbon::parse($trx->tanggal_bayar)->format('d M Y H:i') : '-' }}</td>
                    <td class="text-center"><span class="badge-lunas">LUNAS</span></td>
                    <td class="text-center">
                        @if($trx->is_journalized)
                            <span class="badge-jurnal" title="Dijurnalkan: {{ $trx->journalized_at?->format('d M Y H:i') ?? 'N/A' }}">
                                ✓ Jurnal
                            </span>
                        @else
                            <span class="badge-pending">⏳ Pending</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($trx->is_journalized)
                            <button class="btn btn-sm btn-secondary" disabled title="Terlindungi — sudah dijurnal">
                                <i class="fas fa-lock"></i>
                            </button>
                        @else
                            <form method="POST" action="{{ route('sinkron.deleteTransaksiById', $trx->id) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus transaksi {{ $trx->nama_pelanggan }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-white-50">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Belum ada data. Silakan import atau ubah filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transaksi->hasPages())
    <div class="px-3 py-2" style="background:#162040;border-top:1px solid var(--border)">
        {{ $transaksi->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection