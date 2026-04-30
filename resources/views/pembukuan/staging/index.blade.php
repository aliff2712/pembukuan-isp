@extends('layouts-main.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/staging.css') }}">
@endpush

@section('title', 'Payment Staging Review')
@section('page-title', 'Payment Import Review')

@section('content')
<div class="sw">

    {{-- FLASH --}}
    @foreach(['success' => 'al-s', 'warning' => 'al-w', 'error' => 'al-e'] as $type => $cls)
        @if(session($type))
            <div class="al {{ $cls }}">
                <i class="fas fa-info-circle me-2"></i>{{ session($type) }}
            </div>
        @endif
    @endforeach

    {{-- SUMMARY --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['c-cyan',   'Pending',   $totalPending,   'pending'],
            ['c-green',  'Approved',  $totalApproved,  'approved'],
            ['c-yellow', 'Flagged',   $totalFlagged,   'flagged'],
            ['c-purple', 'Duplicate', $totalDuplicate, 'duplicate'],
            ['c-red',    'Rejected',  $totalRejected,  'rejected'],
        ] as [$cls, $label, $val, $status])
            {{-- col-6 col-lg-2 supaya 5 card tidak pecah di mobile --}}
            <div class="col-6 col-lg-2">
                <a href="{{ route('pembukuan.staging.index', ['status' => $status]) }}">
                    <div class="sc {{ $cls }} {{ $statusFilter === $status ? 'sc-active' : '' }}">
                        <div class="sc-label">{{ $label }}</div>
                        <div class="sc-val">{{ $val }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- FILTER --}}
    <div class="filter-bar mb-3">
        <form method="GET" class="row g-2" autocomplete="off">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                    placeholder="Cari pelanggan / kode..."
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-3">
                <input type="text" name="bulan" class="form-control"
                    placeholder="YYYY-MM"
                    value="{{ request('bulan') }}"
                    pattern="\d{4}-(0[1-9]|1[0-2])"
                    title="Format: YYYY-MM">
            </div>

            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn-f">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('pembukuan.staging.index', ['status' => $statusFilter]) }}"
                   class="btn-reset">Reset</a>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="tcard">
        <div class="tcard-head">
            <h6>Data {{ ucfirst($statusFilter) }}</h6>
            <span class="badge-count">{{ $stagedData->total() }}</span>
        </div>

        @if($stagedData->count())

        <form id="bulkForm" method="POST">
            @csrf

            <div class="table-responsive">
                <table class="stbl">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Jumlah</th>
                            {{-- Kolom info muncul sesuai status aktif --}}
                            @if($statusFilter === 'flagged')
                                <th>Alasan Flag</th>
                            @elseif($statusFilter === 'duplicate')
                                <th>Duplikat Dari</th>
                            @endif
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                            @foreach($stagedData as $d)
                @php
                    $isFlagged   = $d->status_approval === 'flagged';
                    $isDuplicate = $d->status_approval === 'duplicate';
                    $isActionable = $d->isActionable();

                    $rowClass = match(true) {
                        $isFlagged   => 'table-danger-row',
                        $isDuplicate => 'table-duplicate-row',
                        default      => '',
                    };
                @endphp

                <tr class="{{ $rowClass }}">
                    <td>
                        {{-- Checkbox hanya untuk data yang masih bisa diaksi --}}
                        @if($isActionable)
                            <input type="checkbox" class="chk" name="ids[]" value="{{ $d->id }}">
                        @endif
                    </td>

                    <td>{{ optional($d->tanggal_bayar)->format('d M Y') ?? '-' }}</td>

                    <td>
                        <strong>{{ $d->nama_pelanggan }}</strong><br>
                        <small>{{ $d->kode_transaksi ?? '-' }}</small>
                    </td>

                    <td class="amt">Rp {{ number_format($d->jumlah, 0, ',', '.') }}</td>

                    @if($statusFilter === 'flagged')
                        <td><div class="flag-box">{{ $d->flag_reason ?? '-' }}</div></td>
                    @elseif($statusFilter === 'duplicate')
                        <td><div class="duplicate-box"><code>{{ $d->duplicate_of ?? '-' }}</code></div></td>
                    @endif

                    <td class="d-flex gap-1">
                        <a href="{{ route('pembukuan.staging.show', $d->id) }}"
                        class="ba v" title="Lihat detail">
                            <i class="fas fa-eye"></i>
                        </a>

                        @if($isActionable)
                            <a href="{{ route('pembukuan.staging.edit', $d->id) }}"
                            class="ba e" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form method="POST"
                                action="{{ route('pembukuan.staging.approve', $d->id) }}">
                                @csrf
                                <button type="submit" class="ba a" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>

                            <form method="POST"
                                action="{{ route('pembukuan.staging.reject', $d->id) }}">
                                @csrf
                                <button type="submit" class="ba r" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>

                        @elseif($d->is_locked)
                            <span class="locked-b">
                                <i class="fas fa-lock"></i> Locked
                            </span>

                        @else
                            {{-- Sudah approved/rejected tapi belum locked --}}
                            <span class="done-b">
                                <i class="fas fa-check-circle"></i> {{ ucfirst($d->status_approval) }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
                    </tbody>
                </table>
            </div>

            {{-- BULK ACTION --}}
            <div class="bulk-f">
                <button type="button" id="btnApprove" class="btn-bulk bg" disabled>
                    <i class="fas fa-check me-1"></i> Approve
                </button>
                <button type="button" id="btnReject" class="btn-bulk br" disabled>
                    <i class="fas fa-times me-1"></i> Reject
                </button>
                <span id="selectedCount" class="sel-count"></span>
            </div>

        </form>

        {{ $stagedData->links() }}

        @else
            <div class="empty">
                <i class="fas fa-inbox mb-2 d-block" style="font-size:1.5rem;"></i>
                Tidak ada data {{ $statusFilter }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const form       = document.getElementById('bulkForm');
    const selectAll  = document.getElementById('selectAll');
    const btnApprove = document.getElementById('btnApprove');
    const btnReject  = document.getElementById('btnReject');
    const counter    = document.getElementById('selectedCount');

    // ── Helper ──────────────────────────────────────────────
    const getChecked = () => [...document.querySelectorAll('.chk:checked')];

    function updateState() {
        const count = getChecked().length;
        btnApprove.disabled = count === 0;
        btnReject.disabled  = count === 0;
        counter.textContent = count ? `${count} dipilih` : '';
    }

    // ── Select All ──────────────────────────────────────────
    selectAll?.addEventListener('change', e => {
        document.querySelectorAll('.chk').forEach(cb => cb.checked = e.target.checked);
        updateState();
    });

    document.querySelectorAll('.chk').forEach(cb =>
        cb.addEventListener('change', updateState)
    );

    // ── Bulk Approve ────────────────────────────────────────
    btnApprove.addEventListener('click', () => {
        Swal.fire({
            title: 'Approve data terpilih?',
            text: `${getChecked().length} data akan dijurnalkan.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Approve',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
        }).then(result => {
            if (result.isConfirmed) {
                form.action = '{{ route("pembukuan.staging.bulk-approve") }}';
                form.submit();
            }
        });
    });

    // ── Bulk Reject ─────────────────────────────────────────
    btnReject.addEventListener('click', () => {
        Swal.fire({
            title: 'Reject data terpilih?',
            text: `${getChecked().length} data akan ditolak.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reject',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
        }).then(result => {
            if (result.isConfirmed) {
                form.action = '{{ route("pembukuan.staging.bulk-reject") }}';
                form.submit();
            }
        });
    });

});
</script>
@endpush

@endsection