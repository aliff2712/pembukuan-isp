@extends('layouts-main.app')
@section('title', 'Sinkron Belum Bayar')

@section('content')

<style>
body { background: #0f172a; }

/* === REUSE STYLE KAMU === */
.summary-card {
    background: #1e293b;
    border-radius: 18px;
    padding: 24px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 10px 25px rgba(0,0,0,0.35);
    transition: all .25s ease;
}
.summary-card:hover { transform: translateY(-4px); }
.summary-card small { color: #94a3b8; }
.summary-number { font-size: 30px; font-weight: 700; color: #fff; }

.filter-card {
    background: #1e293b;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.06);
}

.filter-card .form-control,
.filter-card .form-select {
    background: #0f172a;
    border: 1px solid #0f172a;
    color: #fff;
    border-radius: 12px;
}

.btn-navy {
    background: #2563eb;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
}

.table thead th {
    background: #1e3a5f;
    color: #fff;
}

.table tbody td {
    color: #e2e8f0;
}

/* === KHUSUS BELUM BAYAR === */
.badge-unpaid {
    background: #ef4444;
    color: #fff;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #f59e0b;
    color: #fff;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
}

.alert-success-navy {
    background: rgba(34,197,94,.15);
    border: 1px solid rgba(34,197,94,.3);
    color: #86efac;
    border-radius: 12px;
    padding: 12px 16px;
}

.alert-error-navy {
    background: rgba(220,38,38,.15);
    border: 1px solid rgba(220,38,38,.3);
    color: #fca5a5;
    border-radius: 12px;
    padding: 12px 16px;
}
</style>

{{-- HEADER --}}
<div class="mb-4">
    <h3 class="text-white fw-bold">Data Belum Bayar</h3>
</div>

{{-- NOTIF --}}
@if(session('success'))
<div class="alert-success-navy mb-3">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert-error-navy mb-3">
    {{ session('error') }}
</div>
@endif

{{-- SUMMARY --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="summary-card text-center">
            <small>Total Data</small>
            <div class="summary-number">{{ $totalData }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="summary-card text-center">
            <small>Total Piutang</small>
            <div class="summary-number">
                Rp {{ number_format($totalTagihan,0,',','.') }}
            </div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="GET">

            <div class="row g-3">

                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control" placeholder="Cari pelanggan...">
                </div>

                <div class="col-md-2">
                    <input type="month" name="bulan" value="{{ request('bulan') }}"
                        class="form-control">
                </div>

                <div class="col-md-2">
                    <select name="area" class="form-control">
                        <option value="">Semua Area</option>
                        @foreach($data->pluck('area')->unique() as $area)
                            <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>
                                {{ $area }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="jatuh_tempo">Jatuh Tempo</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-navy w-100">Filter</button>
                </div>

            </div>

        </form>
    </div>
</div>

{{-- IMPORT --}}
<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('sinkron.belum-bayar.import') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="month" name="bulan" value="{{ now()->format('Y-m') }}"
                        class="form-control">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-navy">
                        Import Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- DELETE --}}
<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('sinkron.deleteBelumBayar') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data belum bayar untuk bulan ini?')">
            @csrf
            @method('DELETE')
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="month" name="bulan" value="{{ now()->format('Y-m') }}"
                        class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Hapus Data Bulan Ini
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Area</th>
                    <th>Paket</th>
                    <th class="text-end">Tagihan</th>
                    <th>Bulan</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="fw-bold text-white">{{ $row->nama_pelanggan }}</td>
                    <td>{{ $row->area }}</td>
                    <td>{{ $row->paket }}</td>

                    <td class="text-end fw-bold text-white">
                        Rp {{ number_format($row->total_tagihan,0,',','.') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($row->bulan)->translatedFormat('F Y') }}
                    </td>

                    <td>
                        @if($row->status == 'jatuh_tempo')
                            <span class="badge badge-warning">Jatuh Tempo</span>
                        @else
                            <span class="badge badge-unpaid">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <form method="POST" action="{{ route('sinkron.deleteBelumBayarById', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus data {{ $row->nama_pelanggan }} bulan {{ $row->bulan }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus record ini">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        Tidak ada data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($data->hasPages())
    <div class="p-3">
        {{ $data->links() }}
    </div>
    @endif
</div>

@endsection