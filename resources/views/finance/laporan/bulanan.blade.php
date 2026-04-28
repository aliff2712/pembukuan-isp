@extends('layouts-main.app')

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan ' . $label)

@section('content')
<div class="container-fluid">

    {{-- SUMMARY --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <small>Sinkron Transaksi</small>
                    <h5 class="fw-bold">
                        Rp {{ number_format($summary['sinkronTransaksiTotal'],0,',','.') }}
                    </h5>
                    <small>{{ $summary['sinkronTransaksiCount'] }} transaksi</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <small>Voucher</small>
                    <h5 class="fw-bold">
                        Rp {{ number_format($summary['voucherTotal'],0,',','.') }}
                    </h5>
                    <small>{{ $summary['voucherTransaksi'] }} transaksi</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-secondary text-white shadow-sm">
                <div class="card-body">
                    <small>Pemasukan lain</small>
                    <h5 class="fw-bold">
                        Rp {{ number_format($summary['otherTotal'],0,',','.') }}
                    </h5>
                    <small>{{ $summary['otherCount'] }} transaksi</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <small>Total Pendapatan</small>
                    <h4 class="fw-bold">
                        Rp {{ number_format($summary['totalPendapatan'],0,',','.') }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- BARU: Pengeluaran --}}
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <small>Total Pengeluaran</small>
                    <h5 class="fw-bold">
                        Rp {{ number_format($summary['totalPengeluaran'],0,',','.') }}
                    </h5>
                    <small>{{ $summary['expenseCount'] }} transaksi</small>
                </div>
            </div>
        </div>


    <div class="mb-3 d-flex gap-2">

<a href="#"
   class="btn btn-outline-success btn-sm"
   onclick="startExport(event)">
    <i class="fas fa-file-excel me-1"></i>
    Export Excel
</a>

<a href="{{ route('finance.laporan.index') }}" class="btn btn-outline-secondary btn-modern">
    <i class="fas fa-arrow-left me-2"></i>
    Back
</a>

</div>

<div class="card shadow-sm border-0 rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>Tanggal Bayar</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Metode</th>
                    <th>Total</th>
                </tr>
            </thead>

                    @foreach($transaksis as $t)
                        <tr>
                            <td>{{ $t->tanggal_bayar->format('d M Y') }}</td>
                            <td>Sinkron Transaksi</td>
                            <td>{{ $t->nama_pelanggan }} - {{ $t->paket ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($t->metode ?? '-') }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($t->jumlah,0,',','.') }}</td>
                        </tr>
                    @endforeach

                    @foreach($vouchers as $v)
                        <tr>
                            <td>{{ $v->sale_date->format('d M Y') }}</td>
                            <td>Voucher</td>
                            <td>{{ $v->notes ?? '-' }}</td>
                            <td>-</td>
                            <td>Rp {{ number_format($v->total_amount,0,',','.') }}</td>
                        </tr>
                    @endforeach

                    @foreach($otherIncomes as $o)
                        <tr>
                            <td>{{ $o->income_date->format('d M Y') }}</td>
                            <td>Other Income</td>
                            <td>{{ $o->description ?? '-' }}</td>
                            <td>-</td>
                            <td>Rp {{ number_format($o->amount,0,',','.') }}</td>
                        </tr>
                    @endforeach

                    {{-- BARU: Expense rows --}}
                    @foreach($expenses as $e)
                        <tr class="table-danger">
                            <td>{{ \Carbon\Carbon::parse($e->expense_date)->format('d M Y') }}</td>
                            <td>Pengeluaran</td>
                            <td>
                                {{ $e->expenseAccount->account_name ?? '-' }}
                                @if($e->description)
                                    <br><small class="text-muted">{{ $e->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $e->cashAccount->account_name ?? '-' }}
                                </span>
                            </td>
                            <td class="text-danger fw-semibold">
                                - Rp {{ number_format($e->amount,0,',','.') }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

</div>
<!-- EXPORT MODAL -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">

            <h5 class="mb-3">
                <i class="fas fa-file-export me-2"></i> Exporting Data
            </h5>

            <div class="d-flex justify-content-center mb-3">
                <div class="spinner-border" role="status"></div>
            </div>

            <div class="progress mb-2" style="height:20px;">
    <div id="exportProgress"
         class="progress-bar progress-bar-striped progress-bar-animated"
         style="width:0%; background:#3b82f6;">
         0%
    </div>
</div>

            <div id="progressText">Menyiapkan export...</div>

        </div>
    </div>
</div>
<style>

/* MODAL */
.table {
    margin-bottom: 0;
}
#exportModal .modal-content{
    border-radius:14px;
    border:none;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

/* TITLE */
#exportModal h5{
    color:#0b2a4a;
    font-weight:600;
}

/* SPINNER */
.spinner-border{
    color:#0b2a4a;
}

/* PROGRESS CONTAINER */
.progress{
    height:22px;
    border-radius:30px;
    background:#e9edf2;
}

/* PROGRESS BAR CERAH */
.progress-bar{
    font-weight:600;
    font-size:13px;
}

/* progress warna cerah + stripes */
.progress-bar-striped{
    background-image: linear-gradient(
        45deg,
        rgba(255,255,255,.25) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255,255,255,.25) 50%,
        rgba(255,255,255,.25) 75%,
        transparent 75%,
        transparent
    ),
    linear-gradient(90deg,#38bdf8,#0ea5e9);

    background-size: 1rem 1rem, 100% 100%;
}

/* TEXT */
#progressText{
    font-size:14px;
    color:#0b2a4a;
}

</style>
<script>

function startExport(e){

e.preventDefault();

const modal = new bootstrap.Modal(document.getElementById('exportModal'));
modal.show();

let progress = 0;

const bar = document.getElementById("exportProgress");
const text = document.getElementById("progressText");

function animate(){

    if(progress < 90){

        progress += Math.random() * 7;

        bar.style.width = progress + "%";
        bar.innerText = Math.floor(progress) + "%";

        requestAnimationFrame(animate);

    }

}

animate();

setTimeout(function(){

    bar.style.width = "100%";
    bar.innerText = "100%";
    text.innerText = "Export selesai";

    // DOWNLOAD FILE
    window.location.href = "{{ route('finance.laporan.export.excel.bulanan') }}";

    setTimeout(()=>{
        modal.hide();
    },1200);

},2000);

}

</script>
@endsection