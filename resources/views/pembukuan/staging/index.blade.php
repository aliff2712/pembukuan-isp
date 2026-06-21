@extends('layouts-main.app')

@section('title', 'Payment Staging Review')
@section('page-title', 'Payment Import Review')

@push('styles')
<style>
:root {
    --bg-base: #0f1117; --bg-card: #161b27; --bg-alt: #1c2233; --bg-hover: #1e2640;
    --border: #2a3147; --border-l: #323d57;
    --text: #e2e8f0; --muted: #6b7a99; --dim: #4a5568;
    --blue: #3b82f6; --green: #10b981; --yellow: #f59e0b; --red: #ef4444; --cyan: #06b6d4;
}
.sw { background: var(--bg-base); min-height: 100vh; padding: 1.5rem; }

/* Cards */
.sc { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; position: relative; overflow: hidden; transition: .2s; }
.sc:hover { border-color: var(--border-l); transform: translateY(-2px); }
.sc::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; }
.sc.c-cyan::before{background:var(--cyan)} .sc.c-green::before{background:var(--green)}
.sc.c-yellow::before{background:var(--yellow)} .sc.c-red::before{background:var(--red)}
.sc-label { font-size:.68rem; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:.3rem; }
.sc-val { font-size:2rem; font-weight:700; line-height:1; margin-bottom:.2rem; }
.sc.c-cyan .sc-val{color:var(--cyan)} .sc.c-green .sc-val{color:var(--green)}
.sc.c-yellow .sc-val{color:var(--yellow)} .sc.c-red .sc-val{color:var(--red)}
.sc-desc { font-size:.72rem; color:var(--dim); }

/* Filter & Tabs */
.filter-bar { background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:1rem 1.25rem; }
.filter-bar .form-control { background:var(--bg-alt); border:1px solid var(--border); color:var(--text); border-radius:8px; font-size:.85rem; }
.filter-bar .form-control:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(59,130,246,.15); background:var(--bg-alt); color:var(--text); }
.filter-bar .form-control::placeholder { color:var(--dim); }
.btn-f { background:rgba(59,130,246,.12); border:1px solid rgba(59,130,246,.3); color:var(--blue); border-radius:7px; font-size:.8rem; padding:.4rem .85rem; cursor:pointer; transition:.15s; text-decoration:none; display:inline-flex; align-items:center; gap:.3rem; }
.btn-f:hover { background:rgba(59,130,246,.22); color:var(--blue); }
.btn-f.active { background:rgba(59,130,246,.25); border-color:rgba(59,130,246,.5); }
.btn-reset { background:var(--bg-alt); border:1px solid var(--border); color:var(--muted); border-radius:7px; font-size:.8rem; padding:.4rem .85rem; text-decoration:none; display:inline-flex; align-items:center; gap:.3rem; transition:.15s; }
.btn-reset:hover { border-color:var(--border-l); color:var(--text); }

/* Table */
.tcard { background:var(--bg-card); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.tcard-head { background:var(--bg-alt); border-bottom:1px solid var(--border); padding:.85rem 1.25rem; display:flex; align-items:center; gap:.5rem; }
.tcard-head h6 { color:var(--text); font-size:.85rem; font-weight:600; margin:0; }
.tcard-head .badge-count { background:rgba(245,158,11,.15); color:var(--yellow); border:1px solid rgba(245,158,11,.25); border-radius:20px; font-size:.7rem; padding:.15rem .6rem; margin-left:auto; }
.stbl { width:100%; border-collapse:collapse; }
.stbl thead tr { background:var(--bg-alt); border-bottom:1px solid var(--border); }
.stbl thead th { padding:.7rem 1rem; font-size:.68rem; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); font-weight:600; white-space:nowrap; }
.stbl tbody tr { border-bottom:1px solid var(--border); transition:.15s; }
.stbl tbody tr:last-child { border-bottom:none; }
.stbl tbody tr:hover { background:var(--bg-hover); }
.stbl tbody td { padding:.8rem 1rem; font-size:.82rem; color:var(--text); vertical-align:middle; }
.pname { font-weight:600; } .pcode { font-size:.72rem; color:var(--muted); margin-top:2px; }
.amt { font-weight:700; color:var(--green); } .dtext { color:var(--muted); font-size:.8rem; }
.flag-b { display:inline-block; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); color:#fca5a5; border-radius:6px; font-size:.72rem; padding:.2rem .55rem; line-height:1.3; }

/* Action btns */
.ba { width:30px; height:30px; border-radius:6px; border:1px solid; display:inline-flex; align-items:center; justify-content:center; font-size:.75rem; transition:.15s; cursor:pointer; text-decoration:none; }
.ba:hover { transform:translateY(-1px); }
.ba.v{background:rgba(6,182,212,.1);border-color:rgba(6,182,212,.3);color:var(--cyan)}
.ba.e{background:rgba(59,130,246,.1);border-color:rgba(59,130,246,.3);color:var(--blue)}
.ba.a{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:var(--green)}
.ba.r{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:var(--red)}
.ba.v:hover{background:rgba(6,182,212,.2);color:var(--cyan)}
.ba.e:hover{background:rgba(59,130,246,.2);color:var(--blue)}
.ba.a:hover{background:rgba(16,185,129,.2);color:var(--green)}
.ba.r:hover{background:rgba(239,68,68,.2);color:var(--red)}
.locked-b { background:rgba(107,114,153,.1); border:1px solid rgba(107,114,153,.25); color:var(--muted); border-radius:6px; font-size:.7rem; padding:.2rem .5rem; }

/* Bulk footer */
.bulk-f { background:var(--bg-alt); border-top:1px solid var(--border); padding:.85rem 1.25rem; display:flex; gap:.75rem; align-items:center; }
.btn-bulk { border-radius:7px; font-size:.78rem; padding:.4rem .9rem; font-weight:600; border:1px solid; cursor:pointer; transition:.15s; display:inline-flex; align-items:center; gap:.4rem; }
.btn-bulk:disabled { opacity:.35; cursor:not-allowed; }
.btn-bulk.bg{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.3);color:var(--green)}
.btn-bulk.bg:not(:disabled):hover{background:rgba(16,185,129,.22)}
.btn-bulk.br{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.3);color:var(--red)}
.btn-bulk.br:not(:disabled):hover{background:rgba(239,68,68,.22)}
.sel-count { font-size:.75rem; color:var(--muted); margin-left:auto; }

/* Alerts */
.al { border-radius:8px; padding:.75rem 1rem; font-size:.82rem; margin-bottom:1rem; }
.al-s{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#6ee7b7}
.al-w{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#fcd34d}
.al-e{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}

/* Checkbox */
.form-check-input{background-color:var(--bg-alt);border-color:var(--border-l);cursor:pointer}
.form-check-input:checked{background-color:var(--blue);border-color:var(--blue)}

/* Pagination */
.pagination .page-link{background:var(--bg-alt);border-color:var(--border);color:var(--muted);font-size:.8rem}
.pagination .page-item.active .page-link{background:var(--blue);border-color:var(--blue);color:#fff}
.pagination .page-link:hover{background:var(--bg-hover);color:var(--text);border-color:var(--border-l)}

/* Empty */
.empty { padding:4rem 2rem; text-align:center; }
.empty .ico { width:56px; height:56px; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.2); border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-size:1.4rem; color:var(--green); margin-bottom:1rem; }
.empty p { color:var(--muted); font-size:.85rem; margin:0; }
</style>
@endpush

@section('content')
<div class="sw">

    {{-- Flash --}}
    @foreach(['success' => 'al-s', 'warning' => 'al-w', 'error' => 'al-e'] as $type => $cls)
        @if(session($type))
            <div class="al {{ $cls }}"><i class="fas fa-info-circle me-2"></i>{{ session($type) }}</div>
        @endif
    @endforeach

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['c-cyan',   'Pending',  $totalPending,  'Menunggu validasi', 'pending'],
            ['c-green',  'Approved', $totalApproved, 'Sudah dijurnal',    'approved'],
            ['c-yellow', 'Flagged',  $totalFlagged,  'Perlu review',      'flagged'],
            ['c-red',    'Rejected', $totalRejected, 'Di-reject',         'rejected'],
        ] as [$cls, $label, $val, $desc, $s])
            <div class="col-6 col-md-3">
                <a href="{{ route('pembukuan.staging.index', ['status' => $s]) }}" style="text-decoration:none">
                    <div class="sc {{ $cls }} {{ $statusFilter === $s ? 'border-light' : '' }}">
                        <div class="sc-label">{{ $label }}</div>
                        <div class="sc-val">{{ $val }}</div>
                        <div class="sc-desc">{{ $desc }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="filter-bar mb-3">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari pelanggan / kode..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="bulan" class="form-control" placeholder="Bulan (YYYY-MM)" value="{{ request('bulan') }}">
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn-f"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('pembukuan.staging.index', ['status' => $statusFilter]) }}" class="btn-reset"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="tcard">
        <div class="tcard-head">
            <i class="fas fa-table" style="color:var(--yellow)"></i>
            <h6>Data {{ ucfirst($statusFilter) }}</h6>
            <span class="badge-count">{{ $stagedData->total() }} data</span>
        </div>

        @if($stagedData->count() > 0)
            <form id="bForm" method="POST" action="{{ route('pembukuan.staging.bulk-approve') }}">
                @csrf
                <div class="table-responsive">
                    <table class="stbl">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selAll" class="form-check-input"></th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Jumlah</th>
                                @if($statusFilter === 'flagged') <th>Alasan Flag</th> @endif
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stagedData as $d)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $d->id }}" class="form-check-input chk"></td>
                                <td><span class="dtext">{{ $d->tanggal_bayar?->format('d M Y') ?? '-' }}</span></td>
                                <td>
                                    <div class="pname">{{ $d->nama_pelanggan }}</div>
                                    <div class="pcode">{{ $d->kode_transaksi ?? '-' }}</div>
                                </td>
                                <td><span class="amt">Rp {{ number_format($d->jumlah, 0, ',', '.') }}</span></td>
                                @if($statusFilter === 'flagged')
                                    <td><span class="flag-b">{{ $d->flag_reason ?? '-' }}</span></td>
                                @endif
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('pembukuan.staging.show', $d->id) }}" class="ba v" title="Detail"><i class="fas fa-eye"></i></a>
                                        @if(!$d->is_locked)
                                            <a href="{{ route('pembukuan.staging.edit', $d->id) }}" class="ba e" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form method="POST" action="{{ route('pembukuan.staging.approve', $d->id) }}" style="display:inline">
                                                @csrf <button class="ba a" title="Approve"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('pembukuan.staging.reject', $d->id) }}" style="display:inline" onsubmit="return confirm('Reject data ini?')">
                                                @csrf <button class="ba r" title="Reject"><i class="fas fa-times"></i></button>
                                            </form>
                                        @else
                                            <span class="locked-b"><i class="fas fa-lock me-1"></i>Locked</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="bulk-f">
                    <button type="button" class="btn-bulk bg" id="btnA" disabled><i class="fas fa-check-circle"></i> Approve</button>
                    <button type="button" class="btn-bulk br" id="btnR" disabled><i class="fas fa-times-circle"></i> Reject</button>
                    <span class="sel-count" id="selCount"></span>
                </div>
            </form>
            <div style="padding:.85rem 1.25rem;border-top:1px solid var(--border)">{{ $stagedData->render() }}</div>
        @else
            <div class="empty">
                <div class="ico"><i class="fas fa-check-circle"></i></div>
                <p>Tidak ada data {{ $statusFilter }}.</p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('bForm');
    const selAll = document.getElementById('selAll');
    const chks = document.querySelectorAll('.chk');
    const btnA = document.getElementById('btnA');
    const btnR = document.getElementById('btnR');
    const selCount = document.getElementById('selCount');

    const update = () => {
        const n = document.querySelectorAll('.chk:checked').length;
        if(btnA) btnA.disabled = n === 0;
        if(btnR) btnR.disabled = n === 0;
        selCount.textContent = n > 0 ? n + ' dipilih' : '';
    };

    selAll?.addEventListener('change', e => { chks.forEach(c => c.checked = e.target.checked); update(); });
    chks.forEach(c => c.addEventListener('change', update));

    btnA?.addEventListener('click', () => {
        if(confirm('Approve data yang dipilih?')) { form.action = '{{ route("pembukuan.staging.bulk-approve") }}'; form.submit(); }
    });
    btnR?.addEventListener('click', () => {
        if(confirm('Reject data yang dipilih?')) { form.action = '{{ route("pembukuan.staging.bulk-reject") }}'; form.submit(); }
    });
});
</script>
@endsection