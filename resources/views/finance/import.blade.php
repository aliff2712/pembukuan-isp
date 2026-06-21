@extends('layouts-main.app')

@section('title', 'Import Transaksi')
@section('page-title', 'Import Data Transaksi DHS Dipanet Hotspot Solution')

@section('content')

<style>
/* ===============================
   NAVY BACKGROUND
=================================*/
body {
    background: #0f172a;
}

/* ===============================
   IMPORT CARD
=================================*/
.import-card {
    background: #1e293b;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow:
        0 15px 35px rgba(0,0,0,0.4),
        inset 0 1px 0 rgba(255,255,255,0.04);
    transition: all .25s ease;
}

.import-card:hover {
    transform: translateY(-3px);
}

/* HEADER */
.import-header {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 20px 25px;
}

.import-header h5 {
    color: #ffffff;
    font-weight: 600;
}

/* BODY */
.import-body {
    padding: 30px;
}

/* LABEL */
.import-body label {
    color: #cbd5e1;
    font-weight: 500;
    font-size: 14px;
}

/* FILE INPUT STYLE */
.file-upload-wrapper {
    background: #0f172a;
    border: 2px dashed rgba(255,255,255,0.15);
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all .25s ease;
}

.file-upload-wrapper:hover {
    border-color: #3b82f6;
    background: rgba(59,130,246,0.08);
}

.file-upload-wrapper i {
    font-size: 40px;
    color: #3b82f6;
    margin-bottom: 10px;
}

.file-upload-wrapper p {
    color: #94a3b8;
    margin-bottom: 0;
}

.file-upload-wrapper strong {
    color: #ffffff;
}

/* HIDDEN INPUT */
.file-upload-wrapper input {
    display: none;
}

/* BUTTON */
.btn-navy {
    background: #2563eb;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    padding: 10px 24px;
}

.btn-navy:hover {
    background: #1d4ed8;
}

.btn-outline-light {
    border-radius: 12px;
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

<div class="container-fluid">

    <!-- PAGE TITLE -->
    <div class="mb-4">
        <h1 class="h3 text-white fw-bold">Import Transaksi</h1>
        <p class="text-secondary mb-0">
            Upload file Excel untuk menambahkan data transaksi secara otomatis.
        </p>
    </div>

    <!-- SUCCESS -->
    @if(session('success'))
        <div class="alert alert-success mb-4">
            <strong>Berhasil!</strong> {{ session('success') }}
        </div>
    @endif

    <!-- ERROR -->
    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <strong>Terjadi Kesalahan:</strong>
            <ul class="mt-2 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- IMPORT CARD -->
    <div class="import-card">

        <div class="import-header">
            <h5>Upload File Excel</h5>
        </div>

        <div class="import-body">

            <form action="{{ route('finance.transaksi.import') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <!-- FILE UPLOAD AREA -->
                <label class="file-upload-wrapper">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>
                        <strong id="file-name">Klik untuk memilih file</strong><br>
                        <small>Format: .xlsx, .xls, .csv</small>
                    </p>

                    <input type="file"
                           name="file"
                           id="fileInput"
                           accept=".xlsx,.xls,.csv"
                           required>
                </label>

                <!-- BUTTONS -->
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-navy">
                        <i class="fas fa-file-import me-1"></i>
                        Import Transaksi
                    </button>

                    <a href="{{ route('finance.transaksi.index') }}"
   class="btn btn-outline-light btn-cancel">
    Batal
</a>
                </div>

            </form>

        </div>
    </div>

</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const fileInput = document.getElementById("fileInput");
    const fileName = document.getElementById("file-name");
    const cancelBtn = document.querySelector('.btn-cancel');

    // Preview nama file
    if (fileInput) {
        fileInput.addEventListener("change", function () {
            if (this.files.length > 0) {
                fileName.innerText = this.files[0].name;
            }
        });
    }

    // Konfirmasi batal
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


@endsection