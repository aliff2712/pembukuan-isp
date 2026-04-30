<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - DHS FINANCE</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* SWEETALERT DARK THEME */
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

/* tombol */
.swal2-confirm {
    background: #2563eb !important;
    border-radius: 10px !important;
    padding: 8px 20px !important;
}

.swal2-cancel {
    background: #334155 !important;
    border-radius: 10px !important;
}
    :root {
        --primary: #3b82f6;
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --dark-bg: #0f172a;
        --dark-card: #1e293b;
        --dark-sidebar: #0b1120;
        --dark-border: #1f2937;
        --text-main: #e2e8f0;
        --text-muted: #94a3b8;
    }

    html, body { overflow-x: hidden; }

    body {
        font-family: 'Nunito', sans-serif;
        background: var(--dark-bg);
        color: var(--text-main);
    }

    #wrapper {
        display: flex;
    }

    #content-wrapper {
        width: 100%;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .sidebar {
        width: 240px;
        min-height: 100vh;
        background: linear-gradient(180deg, #0b1120, #111827);
        border-right: 1px solid var(--dark-border);
        flex-shrink: 0;
    }

    .sidebar .nav-link {
        color: var(--text-muted);
        padding: 0.8rem 1rem;
        display: flex;
        align-items: center;
        transition: 0.2s;
    }

    .sidebar .nav-link i { width: 20px; }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background: rgba(59,130,246,0.15);
        color: #fff;
        border-left: 3px solid var(--primary);
    }

    .card {
        background: var(--dark-card);
        border: 1px solid var(--dark-border);
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        color: var(--text-main);
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid var(--dark-border);
    }

    .footer {
        margin-top: auto;
        background: #111827;
        border-top: 1px solid var(--dark-border);
        color: var(--text-muted);
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 6px; }

    @media (max-width: 768px) {
        .sidebar { display: none; }
        .card { border-radius: 12px; }
        h1 { font-size: 1.4rem; }
        h2 { font-size: 1.2rem; }
        .btn { font-size: 0.85rem; padding: 0.4rem 0.75rem; }
    }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body id="page-top">

<div id="wrapper">

    {{-- 
        ✅ SIDEBAR DIRENDER SEKALI — clone ke mobile via JS
        Sebelumnya sidebar di-render 2x (desktop + offcanvas) = double memory
    --}}
    <div class="sidebar d-none d-md-block" id="sidebar-desktop">
        @include('layouts-main.sidebar')
    </div>

    <!-- Sidebar Mobile — diisi dari clone desktop via JS, bukan render ulang -->
    <div class="offcanvas offcanvas-start text-bg-dark"
         tabindex="-1"
         id="mobileSidebar">
        <div class="offcanvas   -header border-bottom border-secondary">
            <h5>DHS FINANCE</h5>
            <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0" id="sidebar-mobile">
            {{-- diisi JS di bawah --}}
        </div>
    </div>

    <!-- Content Wrapper -->
    <div id="content-wrapper">

        @include('layouts-main.navbar')

        <div class="container-fluid py-4">
            @yield('content')
        </div>

        <div class="footer py-3 text-center">
            @include('layouts-main.footer')
        </div>

    </div>
</div>

<!-- Scroll To Top -->
<a href="#page-top"
   class="btn btn-primary scroll-to-top position-fixed"
   style="right:1rem;bottom:1rem;border-radius:50%;width:40px;height:40px;display:none;">
    <i class="fas fa-arrow-up"></i>
</a>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Clone sidebar desktop ke mobile — tidak render ulang dari server
document.getElementById('mobileSidebar').addEventListener('show.bs.offcanvas', function () {
    const mobileTarget = document.getElementById('sidebar-mobile');
    if (mobileTarget && mobileTarget.children.length === 0) {
        const desktopSidebar = document.getElementById('sidebar-desktop');
        if (desktopSidebar) {
            mobileTarget.innerHTML = desktopSidebar.innerHTML;
        }
    }
});

// Scroll to top button
$(document).scroll(function () {
    if ($(this).scrollTop() > 100) {
        $('.scroll-to-top').fadeIn();
    } else {
        $('.scroll-to-top').fadeOut();
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function(){

    @if(session('success'))
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: "{{ session('success') }}",
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true
});

// LOVE FILTER EFFECT

@endif

@if(session('error'))
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: "{{ session('error') }}",
    showConfirmButton: false,
    timer: 2000
});
@endif

});

</script>

@stack('scripts')

</body>
</html>