@extends('layouts-main.app')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Keuangan')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-white mb-1">Laporan Keuangan</h3>
        <p class="text-white-50 small mb-0">
            Pilih periode laporan yang ingin Anda lihat
        </p>
    </div>

    <div class="row g-4">

        <!-- LAPORAN BULANAN -->
        <div class="col-lg-6">

            <div class="card report-card shadow-sm border-0 h-100">

                <div class="card-header bg-navy border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="report-icon bg-primary-soft">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-white">Laporan Bulanan</h5>
                    </div>
                </div>

                <div class="card-body">

                    <form action="{{ route('finance.laporan.bulanan') }}" method="GET">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-navy">
                                    Bulan
                                </label>

                                <select name="bulan" class="form-select">
                                    @foreach(range(1,12) as $b)
                                        <option value="{{ $b }}">
                                            {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-navy">
                                    Tahun
                                </label>

                                <input type="number"
                                       name="tahun"
                                       class="form-control"
                                       value="{{ now()->year }}">
                            </div>

                            <div class="col-12 mt-2">
                                <button class="btn btn-primary w-100">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Lihat Laporan
                                </button>
                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        <!-- LAPORAN TAHUNAN -->
        <div class="col-lg-6">

            <div class="card report-card shadow-sm border-0 h-100">

                <div class="card-header bg-navy border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="report-icon bg-success-soft">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-white">Laporan Tahunan</h5>
                    </div>
                </div>

                <div class="card-body">

                    <form action="{{ route('finance.laporan.tahunan') }}" method="GET">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">
                                Tahun
                            </label>

                            <input type="number"
                                   name="tahun"
                                   class="form-control"
                                   value="{{ now()->year }}">
                        </div>

                        <button class="btn btn-success w-100">
                            <i class="fas fa-chart-line me-1"></i>
                            Lihat Laporan
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

<!-- BACK BUTTON BAWAH -->
<div class="mt-4 text-end">

<a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-modern">

<i class="fas fa-arrow-left me-2"></i>
Back

</a>

</div>
</div>


<style>

/* NAVY COLOR */
.text-navy{
    color:#0f2a44;
}

/* CARD STYLE */
.report-card{
    border-radius:14px;
    transition:all .2s ease;
}

.report-card:hover{
    transform:translateY(-3px);
}

/* ICON BOX */
.report-icon{
    width:40px;
    height:40px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* SOFT COLORS */
.bg-primary-soft{
    background:rgba(13,110,253,.1);
}

.bg-success-soft{
    background:rgba(25,135,84,.1);
}

/* LABEL */
.form-label{
    font-size:13px;
}

/* INPUT */
.form-control,
.form-select{
    height:42px;
}

</style>

@endsection