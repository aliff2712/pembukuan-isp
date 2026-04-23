{{--
    Component: x-dashboard.card
    ─────────────────────────────────────────────────────────────
    Props:
      title     (string)  — label di atas nilai
      value     (string)  — nilai utama yang ditampilkan
      icon      (string)  — class FontAwesome, cth: "fas fa-money-bill-wave"
      bg        (string)  — class CSS tema kartu, cth: "bg-success-grey"
      href      (string)  — opsional, jadikan card sebagai link
      subtitle  (string)  — opsional, teks kecil di bawah nilai
      iconClass (string)  — opsional, override warna icon, cth: "text-primary"
--}}

@props([
    'title'     => '',
    'value'     => '',
    'icon'      => 'fas fa-circle',
    'bg'        => 'bg-success-grey',
    'href'      => null,
    'subtitle'  => null,
    'iconClass' => 'text-white',
])

{{-- @once: CSS ini hanya di-inject sekali meskipun component dipakai berkali-kali --}}
@once
<style>
/* =============================================================
   x-dashboard.card — embedded styles
   ============================================================= */

/* ICON CIRCLE */
.dashboard-card .icon-circle {
    background: rgba(255, 255, 255, 0.15);
    width: 52px;
    height: 52px;
    border-radius: 50%;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Background Variants ── */

.bg-success-grey {
    background-color: #2f3542 !important;
}

.bg-success-dark {
    background: #14532d !important;
    transition: background 0.2s ease;
}
.bg-success-dark:hover { background: #166534 !important; }

.bg-dark-blue {
    background: #1e3a8a !important;
    transition: background 0.2s ease;
}
.bg-dark-blue:hover { background: #1e40af !important; }

.bg-orange-soft {
    background: linear-gradient(135deg, #7c2d12, #c2410c) !important;
}

.bg-darkred-soft {
    background: linear-gradient(135deg, #450a0a, #7f1d1d) !important;
}

/* Dark Gold */
.card-warning-dark {
    background-color: #3b2f0f !important;
}

/* Cream — override text-white karena background terang */
.card-cream {
    background-color: #f8f5ef !important;
    border-radius: 12px;
}
.card-cream .card-title-label,
.card-cream .card-value,
.card-cream .card-subtitle { color: #2c3e50 !important; }

/* ── Border Left Variants ── */
.border-left-orange  { border-left: 4px solid #c2410c !important; }
.border-left-darkred { border-left: 4px solid #7f1d1d !important; }
.border-left-warning { border-left: 5px solid #b8860b !important; }
.border-left-success { border-left: 5px solid #28a745 !important; }
.border-left-danger  { border-left: 5px solid #dc3545 !important; }

/* ── Icon Color Helpers ── */
.text-orange  { color: #c2410c !important; }
.icon-orange  { color: rgba(255, 255, 255, 0.5) !important; }
.icon-darkred { color: rgba(255, 255, 255, 0.5) !important; }
</style>
@endonce

@if($href)
    <a href="{{ $href }}" class="text-decoration-none d-block h-100">
@endif

<div class="card border-0 shadow-sm h-100 dashboard-card {{ $bg }} text-white">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">

            <div class="flex-grow-1 me-3">
                <div class="text-uppercase small fw-semibold mb-1 card-title-label">
                    {{ $title }}
                </div>
                <div class="fs-4 fw-bold card-value">
                    {{ $value }}
                </div>
                @if($subtitle)
                <div class="mt-1 small opacity-75 card-subtitle">
                    {{ $subtitle }}
                </div>
                @endif
            </div>

            <div class="icon-circle">
                <i class="{{ $icon }} {{ $iconClass }}"></i>
            </div>

        </div>
    </div>
</div>

@if($href)
    </a>
@endif