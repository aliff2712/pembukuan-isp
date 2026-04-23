@extends('layouts-main.app')

@section('content')
<style>
    .bg-navy{
        background-color: #0f172a;
    }
    /* ALERT NAVY STYLE */
.alert-success {
    background: rgba(22,163,74,.15);
    color: #4ade80;
    border: none;
}

.alert-danger {
    background: rgba(220,38,38,.15);
    color: #f87171;
    border: none;
}
/* ===============================
   SWEETALERT NAVY THEME
=================================*/

.swal2-popup {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-radius: 16px !important;
    border: 1px solid rgba(255,255,255,0.05);
}

.swal2-title {
    color: #ffffff !important;
    font-weight: 600;
}

.swal2-html-container {
    color: #cbd5e1 !important;
}

/* BUTTON CONFIRM */
.swal2-confirm {
    background: #2563eb !important;
    border-radius: 10px !important;
    padding: 8px 20px !important;
}

/* BUTTON CANCEL */
.swal2-cancel {
    background: #334155 !important;
    border-radius: 10px !important;
}

/* MOBILE SIZE */
@media (max-width: 576px) {
    .swal2-popup {
        width: 85% !important;
        font-size: 14px;
    }
}

</style>
<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card shadow border-0 rounded-4 overflow-hidden">

                {{-- NAVY HEADER --}}
                <div class="bg-primary text-white p-4">

                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:60px;height:60px;">
                            <i class="bi bi-cloud-arrow-up fs-4 text-white"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-1">Import Mikhmon CSV</h4>
                            <small class="opacity-75">
                                Import → Transform → Aggregate → Journalize
                            </small>
                        </div>
                    </div>

                </div>

                {{-- BODY --}}
                <div class="card-body p-4 bg-navy">

                    {{-- ALERTS --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- PIPELINE LOG --}}
                    @if(session('log'))
                        <div class="bg-dark rounded-4 p-3 mb-4 font-monospace small text-success shadow-sm">
                            <div class="text-secondary mb-2">// pipeline.log</div>
                            @foreach(session('log') as $line)
                                <div>> {{ $line }}</div>
                            @endforeach
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form action="{{ route('voucher-sales.import.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- UPLOAD BOX --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">
                                Upload File CSV
                            </label>

                            <div class="border rounded-4 p-5 text-center position-relative bg-light text-dark">

<input type="file"
       id="csv_file"
       class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0 @error('csv_file') is-invalid @enderror"
       name="csv_file"
       accept=".csv,.txt">

<i class="bi bi-file-earmark-spreadsheet fs-1 text-primary mb-3 d-block"></i>

<div class="fw-semibold">
    Klik atau tarik file ke sini
</div>

<div class="text-muted small">
    Format .CSV | Maks 10MB
</div>

<div id="file-name" class="mt-2 small fw-semibold text-dark"></div>

</div>
                            @error('csv_file')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">

                        <a href="{{ route('voucher-sales.index') }}"
   id="cancelBtn"
   class="btn btn-outline-secondary rounded-pill px-4 btn-cancel">
    <i class="fas fa-arrow-left me-1"></i>
    Kembali
</a>
                            <button type="submit"
                                    class="btn btn-primary rounded-pill px-4"
                                    id="btn-submit">
                                <i class="bi bi-rocket-takeoff me-2"></i>
                                Jalankan Pipeline
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const cancelBtn = document.getElementById("cancelBtn");
    const fileInput = document.getElementById('csv_file');
    const fileNameDisplay = document.getElementById('file-name');
    const form = document.querySelector('form');

    // loading submit
    if(form){
        form.addEventListener('submit', function () {
            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        });
    }

    // preview file name
    if(fileInput){
        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = "File dipilih: " + this.files[0].name;
            } else {
                fileNameDisplay.textContent = "";
            }
        });
    }

    // konfirmasi cancel
    if (cancelBtn) {

        cancelBtn.addEventListener('click', function(e){

            e.preventDefault();

            const targetUrl = this.href;

            Swal.fire({
                title: "Batalkan proses import?",
                text: "File yang sudah dipilih tidak akan tersimpan.",
                icon: "warning",
                width: window.innerWidth < 576 ? '85%' : '420px',
                showCancelButton: true,
                confirmButtonText: "Ya, Batalkan",
                cancelButtonText: "Tetap di Halaman",
                confirmButtonColor: "#2563eb",
                cancelButtonColor: "#475569",
                background: "#1e293b",
                color: "#e2e8f0"
            }).then((result) => {

                if (result.isConfirmed) {
                    window.location.href = targetUrl;
                }

            });

        });

    }

});

</script>
@endpush