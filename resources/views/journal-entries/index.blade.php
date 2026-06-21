@extends('layouts-main.app')
@section('title', __('Journal Entries'))
@section('page-title', __('Journal Entries'))
@section('content')
<link rel="stylesheet" href="{{ asset('assets/journal-entries.css') }}">
<div class="container-fluid">

    <!-- Success Alert -->
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
        <h3 class="m-0 font-weight-bold text-white">{{ __('Journal Entries') }}</h3>
        </div>
        <div class="col-md-6 text-end">
    <a href="#"
       class="btn btn-navy export-btn"
       onclick="startExport(event)">
        <i class="fas fa-download me-1"></i> Export
    </a>
</div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Total Entries -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Entries') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">
                                {{ number_format($stats['total_entries'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Bulan Ini') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">
                                {{ number_format($stats['this_month'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Debit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Total Debit') }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-white">
                                Rp {{ number_format($stats['total_debit'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Credit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Total Credit') }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($stats['total_credit'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-toolbar mb-4">

<form action="{{ route('journal-entries.index') }}" method="GET">

<div class="navy-filter d-flex flex-wrap align-items-center gap-3 p-3 rounded-4">

    <!-- Search -->
    <div class="flex-grow-1" style="min-width:240px;">
        <div class="input-group">
            <span class="input-group-text bg-dark border-0">
                <i class="fas fa-search text-primary-white"></i>
            </span>
            <input type="text"
                   name="search"
                   class="form-control border-0 text-primary-white"
                   placeholder="Search description / reference..."
                   value="{{ request('search') }}">
        </div>
    </div>

    <!-- Source -->
    <select name="source_type" class="form-select border-0" style="width:160px;">
        <option value="">Source</option>
        @foreach($sourceTypes as $type)
            <option value="{{ $type }}" @selected(request('source_type') == $type)>
                {{ $type }}
            </option>
        @endforeach
    </select>

    <!-- Date Range -->
    <input type="date"
           name="date_from"
           class="form-control border-0"
           style="width:150px;"
           value="{{ request('date_from') }}">

    <input type="date"
           name="date_to"
           class="form-control border-0"
           style="width:150px;"
           value="{{ request('date_to') }}">

    <!-- Month -->
    <select name="month" class="form-select border-0" style="width:120px;">
        <option value="">Bulan</option>
        @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" @selected(request('month') == $m)>
                {{ \Carbon\Carbon::createFromFormat('n', $m)->format('M') }}
            </option>
        @endfor
    </select>

    <!-- Year -->
    <select name="year" class="form-select border-0" style="width:110px;">
        <option value="">Tahun</option>
        @for($y = now()->year; $y >= 2020; $y--)
            <option value="{{ $y }}" @selected(request('year') == $y)>
                {{ $y }}
            </option>
        @endfor
    </select>

    <!-- Buttons -->
    <div class="d-flex align-items-center gap-2">

        <button type="submit" class="btn btn-navy px-4">
            <i class="fas fa-filter me-1"></i> Filter
        </button>

        <a href="{{ route('journal-entries.index') }}" class="btn btn-grey text-warning">
            <b>RESET</b>
        </a>

    </div>

</div>

</form>

</div>
 
    <!-- Journal Entries Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary-white">{{ __('Journal Entries') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive rounded-3 overflow-hidden">
                <table class="table table-hover table-striped table-dark mb-0">
                    <thead >
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Source') }}</th>
                            <th class="text-center">{{ __('Debit') }}</th>
                            <th class="text-center">{{ __('Credit') }}</th>
                            <th class="text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td><strong>{{ $entry->journal_date->format('Y-m-d') }}</strong></td>
                                <td>{{ $entry->description }}</td>
                                <td>{{ $entry->reference_no ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $entry->source_type }}</span>
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($entry->total_debit ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($entry->total_credit ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('journal-entries.show', $entry->id) }}" 
                                       class="btn btn-sm btn-info" title="{{ __('View Details') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3 d-block"></i>
                                    {{ __('No journal entries found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    {{ __('Showing') }} <strong>{{ $entries->firstItem() ?? 0 }}</strong> {{ __('to') }} <strong>{{ $entries->lastItem() ?? 0 }}</strong> {{ __('of') }} <strong>{{ $entries->total() }}</strong> {{ __('entries') }}
                </div>
            </div>

            <!-- Pagination -->
            <nav class="mt-4">
                {{ $entries->links('pagination::bootstrap-4') }}
            </nav>
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
</div>
            <div id="progressText">Menyiapkan export...</div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        window.location.href = "{{ route('journal-entries.export') }}";

        setTimeout(()=>{
            modal.hide();
        },1200);

    },2000);

}

</script>
@endsection