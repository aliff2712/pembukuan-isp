@extends('layouts-main.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow border-0 rounded-4 overflow-hidden">

                <div class="bg-primary text-white p-4">
                    <h4 class="fw-bold mb-1">Status Import Pipeline</h4>
                    <small class="opacity-75">ID: {{ $log->id }}</small>
                </div>

                <div class="card-body p-4">

                    {{-- STATUS BADGE --}}
                    <div class="mb-4 text-center">
                        @if($log->isProcessing())
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <h5>Sedang diproses...</h5>
                            <p class="text-muted small">Halaman akan refresh otomatis</p>
                        @elseif($log->isDone())
                            <div class="text-success fs-1 mb-2">✅</div>
                            <h5 class="text-success">Pipeline Selesai!</h5>
                        @elseif($log->isFailed())
                            <div class="text-danger fs-1 mb-2">❌</div>
                            <h5 class="text-danger">Pipeline Gagal</h5>
                        @endif
                    </div>

                    {{-- LOG OUTPUT --}}
                    @if($log->log)
                        <div class="bg-dark rounded-4 p-3 mb-4 font-monospace small text-success">
                            <div class="text-secondary mb-2">// pipeline.log</div>
                            @foreach(explode("\n", $log->log) as $line)
                                <div>> {{ $line }}</div>
                            @endforeach
                        </div>
                    @endif

                    {{-- BUTTONS --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('voucher-sales.import') }}"
                           class="btn btn-outline-secondary rounded-pill px-4">
                            Import Lagi
                        </a>
                        @if($log->isDone())
                            <a href="{{ route('voucher-sales.index') }}"
                               class="btn btn-success rounded-pill px-4">
                                Lihat Hasil
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($log->isProcessing())
<script>
    setTimeout(() => location.reload(), 3000);
</script>
@endif
@endpush